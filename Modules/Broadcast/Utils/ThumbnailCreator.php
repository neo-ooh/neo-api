<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ThumbnailCreator.php
 */

namespace Neo\Modules\Broadcast\Utils;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Media\Video;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\GD\Driver;
use Intervention\Image\ImageManager;
use Neo\Modules\Broadcast\Exceptions\UnsupportedFileFormatException;
use Symfony\Component\HttpFoundation\File\File;

class ThumbnailCreator {
	protected string|null $tempFile;

	public function __construct(protected File $file, protected int $maxWidth = 1280, protected int $minWidth = 1280) {
	}

	/**
	 * @return resource|null
	 * @throws UnsupportedFileFormatException
	 */
	public function getThumbnailAsStream(string $format = "jpg") {
		$extension = strtolower($this->file->guessExtension());
		return match ($extension) {
			"jpg", "jpeg", "png" => $this->makeThumbnailForImage($this->file, $format),
			"mp4"                => $this->makeThumbnailForVideo($this->file),
			default              => throw new UnsupportedFileFormatException($this->file->getMimeType()),
		};
	}

	/**
	 * @param File $file
	 * @return resource|null
	 */
	protected function makeThumbnailForImage(File $file, string $format) {
        $manager = new ImageManager(new Driver());
		$img = $manager->read($file);
        $img->scaleDown(width: 1280, height: 1280);

		return $img->toPng()->toFilePointer();
	}

	/**
	 * @param File $file
	 * @return resource|null
	 */
	protected function makeThumbnailForVideo(File $file) {
		$ffmpeg  = FFMpeg::create(config('ffmpeg'));
		$ffprobe = FFProbe::create(config('ffmpeg'));

		$tempName       = uniqid("thumb_", true);
		$this->tempFile = Storage::disk('local')->path($tempName);


		//thumbnail
		/** @var TimeCode $duration */
		$duration = $ffprobe->format($file->getRealPath())->get("duration");

		/** @var Video $video */
		$video = $ffmpeg->open($file->getRealPath());
		$frame = $video->frame(TimeCode::fromSeconds((double)$duration / 5));
		$frame->save($this->tempFile);

		return Storage::disk("local")->readStream($tempName);
	}

	public function __destruct() {
		if (isset($this->tempFile)) {
			// Clean temporary file
			Storage::disk('local')->delete($this->tempFile);
		}
	}

}
