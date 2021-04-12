<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreativesController.php
 */

namespace Neo\Http\Controllers;

use Exception;
use FFMpeg\FFProbe;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Neo\Exceptions\BaseException;
use Neo\Exceptions\CannotOverwriteCreativeException;
use Neo\Exceptions\IncompatibleFrameAndFormat;
use Neo\Exceptions\InvalidCreativeDimensions;
use Neo\Exceptions\InvalidCreativeDuration;
use Neo\Exceptions\InvalidCreativeFileFormat;
use Neo\Exceptions\InvalidCreativeFrameRate;
use Neo\Exceptions\InvalidCreativeSize;
use Neo\Exceptions\InvalidCreativeType;
use Neo\Exceptions\InvalidVideoCodec;
use Neo\Http\Requests\Creatives\DestroyCreativeRequest;
use Neo\Http\Requests\Creatives\StoreCreativeRequest;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\DynamicCreative;
use Neo\Models\Frame;
use Neo\Models\StaticCreative;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class CreativesController extends Controller {
    /**
     * @param StoreCreativeRequest $request
     * @param Content              $content
     *
     * @return ResponseFactory|Response
     * @throws IncompatibleFrameAndFormat
     */
    public function store(StoreCreativeRequest $request, Content $content) {
        // Start by checking the given frame matched the content layout
        /** @var Frame $frame */
        $frame = Frame::query()->find($request->get("frame_id"));
        if ($frame->layout->id !== $content->layout->id) {
            throw new IncompatibleFrameAndFormat();
        }

        // Check there is not already a creative for this content's frame
        if ($content->creatives()->where("frame_id", "=", $frame->id)->count() !== 0) {
            return (new CannotOverwriteCreativeException())->asResponse();
        }

        // Check the creative type
        $type = $request->input("type");

        if($type !== Creative::TYPE_STATIC && $type !== Creative::TYPE_DYNAMIC) {
            return (new InvalidCreativeType())->asResponse();
        }

        // Type is valid. Creative storage will be determined by the validity of the uploaded file or url
        // Prefill the creative
        $creative             = new Creative();
        $creative->owner_id   = Auth::id();
        $creative->content_id = $content->id;
        $creative->frame_id   = $frame->id;

        if($type === Creative::TYPE_STATIC) {
            $file = $request->file("file");

            return $this->handleStaticCreative($file, $creative, $frame, $content);
        }

        if($type === Creative::TYPE_DYNAMIC) {
            $name = $request->input("url");
            $url = $request->input("url");

            return $this->handleDynamicCreative($name, $url, $creative, $content);
        }

        return new Response("unreachable");
    }

    protected function handleStaticCreative($file, $creative, $frame, $content) {
        // Control the uploaded creative
        // This methods returns only if the creative is valid
        try {
            $this->validateStaticCreative($file, $frame, $content);
        } catch (BaseException $exc) {
            return $exc->asResponse();
        }

        // Finalize the creative
        $creative->original_name  = $file->getClientOriginalName();
        $creative->status     = "OK";
        $creative->duration   = $content->duration;
        $creative->save();

        // File its settings
        StaticCreative::query()->create([
            "creative_id" => $creative->id,
            "extension" => $file->extension(),
            "checksum" => hash_file('sha256', $file->path()),
        ]);

        $creative->refresh();

        // And store the creative
        $creative->store($file);

        // Properly rename the ad if applicable
        if($content->creatives_count === 1) {
            // This creative is the first, use its name
            $content->name = $file->getBasename();
            $content->save();
        }

        return new Response($content, 201);
    }

    /**
     * @param UploadedFile $file
     * @param Frame        $frame
     * @param Content      $content
     * @return boolean
     * @throws InvalidCreativeDimensions
     * @throws InvalidCreativeDuration
     * @throws InvalidCreativeFileFormat
     * @throws InvalidCreativeFrameRate
     * @throws InvalidCreativeSize
     * @throws InvalidVideoCodec
     */
    protected function validateStaticCreative(UploadedFile $file, Frame $frame, Content $content): bool {
        // Check the file is correctly uploaded
        if (!$file->isValid()) {
            throw new UploadException("An error occurred while uploading the creative");
        }

        // Execute additional media specific asserts
        $mime = $file->getMimeType();

        if ($mime === "image/jpeg" || $mime === "image/png") {
            // Static (Picture)
            // Dimensions
            [$width, $height] = getimagesize($file);
            if ($width !== $frame->width || $height !== $frame->height) {
                throw new InvalidCreativeDimensions();
            }

            // Weight
            if ($file->getSize() > 1.049e+7) { //10 Mib
                throw new InvalidCreativeSize();
            }

            return true;
        }

        if ($mime === "video/mp4") {
            // Dynamic (video)
            $ffprobe = FFProbe::create(config('ffmpeg'));

            $authorizedCodecs = ["h264"];

            $videoStream = $ffprobe->streams($file->path())->videos()->first(); //Select the video

            if (is_null($videoStream)) {
                throw new InvalidCreativeFileFormat();
            }

            $fileInformations = $ffprobe->format($file->path());

            //Check video codec
            if (!in_array($videoStream->get("codec_name"), $authorizedCodecs, true)) {
                throw new InvalidVideoCodec();
            }

            //Check video dimensions
            if ((int)$videoStream->get("width") !== $frame->width || (int)$videoStream->get("height") !== $frame->height) {
                throw new InvalidCreativeDimensions();
            }

            //Check framerate
            $framerate = $this->fracToFloat($videoStream->get("r_frame_rate"));
            if ($framerate < 23.9 || $framerate > 30) {
                throw new InvalidCreativeFrameRate();
            }

            // Check length
            if ((int)$content->duration !== 0) {
                $maxDuration = $content->duration; //Add 1 second offset

                if (abs($fileInformations->get("duration") - $maxDuration) > 1) {
                    throw new InvalidCreativeDuration();
                }
                // Duration OK
            } else {
                // Content has no applied duration, set the one of the video
                $content->duration = round($fileInformations->get("duration"));
                $content->save();
            }

            return true;
        }

        throw new InvalidCreativeFileFormat();
    }

    protected function handleDynamicCreative($name, $url, Creative $creative, Content $content): Response {
        // Nothing to check here really, just create the creative
        $creative->original_name  = $name;
        $creative->status     = "OK";
        $creative->duration   = $content->duration;
        $creative->save();

        // File its settings
        DynamicCreative::query()->create([
            "creative_id" => $creative->id,
            "url" => $url,
            "refresh_interval" => 60, // minutes
        ]);

        $creative->refresh();

        // Properly rename the ad if applicable
        if($content->creatives_count === 1) {
            // This creative is the first, use its name
            $content->name = $name;
            $content->save();
        }

        return new Response($content, 201);
    }

    private function fracToFloat($frac): float {
        $numbers = explode("/", $frac);
        return round($numbers[0] / $numbers[1], 6);
    }

    /**
     * @param DestroyCreativeRequest $request
     * @param Creative               $creative
     *
     * @return ResponseFactory|Response
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function destroy(DestroyCreativeRequest $request, Creative $creative) {
        if ($creative->content->schedules_count === 0) {
            $creative->forceDelete();
        } else {
            $creative->delete();
        }

        return new Response([]);
    }
}
