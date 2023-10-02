<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MockupContractScreenshot.php
 */

namespace Neo\Utils;

use Illuminate\Database\Eloquent\Builder;
use Imagick;
use ImagickException;
use Neo\Models\Screenshot;
use Neo\Modules\Broadcast\Models\DisplayTypeFrame;
use Neo\Modules\Broadcast\Models\FormatCropFrame;
use Neo\Modules\Properties\Models\Enums\InventoryPictureType;
use Neo\Modules\Properties\Models\Product;

class MockupContractScreenshot {
	public function __construct(protected Screenshot $screenshot) {
	}

	/**
	 * Generate and store a mockup version of the screenshot in a temporary file
	 *
	 * @return string|null the path to the mockup temp file, null if nothing was generated
	 * @throws ImagickException
	 */
	public function makeMockup(): null|string {
		// First, we need to find which format to mock up this screenshot to
		$location = $this->screenshot->location ?? $this->screenshot->player->location;
		$product  = $this->screenshot->product ?? Product::query()
		                                                 ->where("is_bonus", "=", false)
		                                                 ->whereHas("locations", function (Builder $query) use ($location) {
			                                                 $query->where("id", "=", $location->getKey());
		                                                 })->first();

		if (!$product) {
			return null;
		}

		$format = $product->format ?? $product->category->format;

		if (!$format) {
			return null;
		}

		// Now we load the appropriate crop frames for this format and the screenshot's location's display type
		$displayTypeFrames = DisplayTypeFrame::query()->where("display_type_id", "=", $location->display_type_id)->get();

		if ($displayTypeFrames->isEmpty()) {
			return null;
		}

		$cropFrames = $format->crop_frames->whereIn("display_type_frame_id", $displayTypeFrames->pluck("id"));

		if ($cropFrames->isEmpty()) {
			return null;
		}

		// Now find our mockup image
		$mockupPicture = $product->pictures()->where("type", "=", InventoryPictureType::Mockup)->inRandomOrder()->first();

		if (!$mockupPicture) {
			$mockupPicture = $product->category->pictures()
			                                   ->where("type", "=", InventoryPictureType::Mockup)
			                                   ->whereHas("product", function (Builder $query) use ($format) {
				                                   $query->where("format_id", "=", $format->getKey())
				                                         ->orWhereHas("category", function (Builder $query) use ($format) {
					                                         $query->where("format_id", "=", $format->getKey());
				                                         });
			                                   })
			                                   ->inRandomOrder()
			                                   ->first();

			if (!$mockupPicture) {
				// No mockup picture available, stop here
				return null;
			}
		}

		// Now, we load our mockup image, and then composite each crop frames according to its parameter on top of it
		$destinationImage  = $this->loadImageFromUrl($mockupPicture->url);
		$destinationWidth  = $destinationImage->getImageWidth();
		$destinationHeight = $destinationImage->getImageHeight();

		$sourceImage  = $this->loadImageFromUrl($this->screenshot->url);
		$sourceWidth  = $sourceImage->getImageWidth();
		$sourceHeight = $sourceImage->getImageHeight();

		/** @var FormatCropFrame $cropFrame */
		foreach ($cropFrames as $cropFrame) {
			$sourceFrame = $cropFrame->display_type_frame;

			// Extract frame
			$croppedFrame = $sourceImage->clone();
			$croppedFrame->trimImage(5);
			$croppedFrame->cropImage(
				width : $sourceFrame->width * $sourceWidth,
				height: $sourceFrame->height * $sourceHeight,
				x     : $sourceFrame->pos_x * $sourceWidth,
				y     : $sourceFrame->pos_y * $sourceHeight,
			);

			// Resize to final dimensions
			$croppedFrame->scaleImage(
				      ($cropFrame->scale / 10_000) * $destinationWidth,
				rows: 0,
			);

			if (method_exists($croppedFrame, 'setImageAlpha')) { // <-- does not exist before 7.x.x
				$croppedFrame->setImageAlpha(.9);
			} else {
				$croppedFrame->setImageOpacity(.9);
			}

			$destinationImage->compositeImage(
				           $croppedFrame,
				composite: Imagick::COMPOSITE_SCREEN,
				x        : ($cropFrame->pos_x / 10_000) * $destinationWidth,
				y        : ($cropFrame->pos_y / 10_000) * $destinationHeight,
			);
		}

		$outputTmp = tempnam(sys_get_temp_dir(), "screenshot_");
		rename($outputTmp, $outputTmp .= '.jpeg');

		$destinationImage->writeImage($outputTmp);

		return $outputTmp;
	}

	/**
	 * @throws ImagickException
	 */
	protected function loadImageFromUrl($url) {
		$tmp = tmpfile();
		fwrite($tmp, file_get_contents($url));
		fseek($tmp, 0);
		$path = stream_get_meta_data($tmp)['uri'];
		return new Imagick($path);
	}
}
