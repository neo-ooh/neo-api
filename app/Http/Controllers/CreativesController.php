<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - CreativesController.php
 */

namespace Neo\Http\Controllers;

use Exception;
use FFMpeg\FFProbe;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Neo\Exceptions\BaseException;
use Neo\Exceptions\CannotOverwriteCreativeException;
use Neo\Exceptions\IncompatibleFrameAndFormat;
use Neo\Exceptions\InvalidCreativeDimensions;
use Neo\Exceptions\InvalidCreativeDuration;
use Neo\Exceptions\InvalidCreativeFileFormat;
use Neo\Exceptions\InvalidCreativeFrameRate;
use Neo\Exceptions\InvalidCreativeSize;
use Neo\Exceptions\InvalidVideoCodec;
use Neo\Http\Requests\Creatives\DestroyCreativeRequest;
use Neo\Http\Requests\Creatives\StoreCreativeRequest;
use Neo\BroadSign\Jobs\DisableBroadSignCreative;
use Neo\BroadSign\Jobs\ImportCreativeInBroadSign;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\Frame;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class CreativesController extends Controller
{
    /**
     * @param StoreCreativeRequest $request
     * @param Content              $content
     *
     * @return ResponseFactory|Response
     * @throws IncompatibleFrameAndFormat
     * @throws InvalidCreativeFileFormat
     */
    public function store(StoreCreativeRequest $request, Content $content)
    {
        // Start by checking the given frame matched the content format
        /** @var Frame $frame */
        $frame = Frame::query()->find($request->get("frame_id"));
        if ($frame->format->id !== $content->format->id) {
            throw new IncompatibleFrameAndFormat();
        }

        // Check there is not already a creative for this content's frame
        if ($content->creatives()->where("frame_id", "=", $frame->id)->count() !== 0) {
            return (new CannotOverwriteCreativeException())->asResponse();
        }

        // Check the file is correctly uploaded
        $file = $request->file("file");
        if (!$file->isValid()) {
            throw new UploadException("An error occurred while uploading the creative");
        }

        // Control the uploaded creative
        // This methods returns only if the creative is valid
        Log::debug($file->getMimeType());
        try {
            $validCreative = $this->validateCreative($file, $frame, $content);
        } catch (BaseException $exc) {
            return $exc->asResponse();
        }

        if (!$validCreative) {
            return (new InvalidCreativeFileFormat())->asResponse();
        }

        $content->refresh();

        // File is good, store it
        $creative             = new Creative();
        $creative->owner_id   = Auth::id();
        $creative->content_id = $content->id;
        $creative->frame_id   = $frame->id;
        $creative->extension  = $file->extension();
        $creative->status     = "OK";
        $creative->checksum   = hash_file('sha256', $file->path());
        $creative->duration   = $content->duration;
        $creative->save();
        $creative->refresh();
        $creative->store($file);


        // Import the creative in BroadSign
        ImportCreativeInBroadSign::dispatch($creative->id);

        return new Response($creative, 201);
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
    protected function validateCreative(UploadedFile $file, Frame $frame, Content $content): bool {
        // Execute additional media specific asserts
        if ($file->getMimeType() === "image/jpeg") {
            // Static (Picture)

            // Dimension
            [$width, $height] = getimagesize($file);
            if ($width !== $frame->width || $height !== $frame->height) {
                throw new InvalidCreativeDimensions();
            }

            // Weight
            if ($file->getSize() > 1.049e+7) { //10 Mib
                throw new InvalidCreativeSize();
            }
        }

        if ($file->getMimeType() === "video/mp4") {
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

        return false;
    }

    private function fracToFloat($frac): float
    {
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
    public function destroy(DestroyCreativeRequest $request, Creative $creative)
    {
        if ($creative->content->schedules_count === 0) {
            $creative->forceDelete();
        } else {
            $creative->delete();
        }

        DisableBroadSignCreative::dispatch($creative->id);

        return new Response([]);
    }


}
