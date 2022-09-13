<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreativeValidator.php
 */

namespace Neo\Modules\Broadcast\Utils;

use FFMpeg\FFProbe;
use Neo\Exceptions\InvalidVideoCodec;
use Neo\Exceptions\UnreadableCreativeException;
use Neo\Modules\Broadcast\Enums\BroadcastParameters;
use Neo\Modules\Broadcast\Exceptions\InvalidCreativeDimensions;
use Neo\Modules\Broadcast\Exceptions\InvalidCreativeDuration;
use Neo\Modules\Broadcast\Exceptions\InvalidCreativeFrameRate;
use Neo\Modules\Broadcast\Exceptions\InvalidCreativeSize;
use Neo\Modules\Broadcast\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Frame;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

class CreativeValidator {
    protected bool $validationPerformed = false;

    protected int $creativeWidth;
    protected int $creativeHeight;
    protected float $creativeLength = 0;

    public function __construct(
        protected File    $file,
        protected Frame   $frame,
        protected Content $content,
    ) {
    }

    /**
     * @return bool
     * @throws InvalidCreativeDimensions
     * @throws InvalidCreativeDuration
     * @throws InvalidCreativeFrameRate
     * @throws InvalidCreativeSize
     * @throws InvalidVideoCodec
     * @throws UnreadableCreativeException
     * @throws UnsupportedFileFormatException
     */
    public function validate(): bool {
        // Execute additional media specific asserts
        $mime = $this->file->getMimeType();

        return match ($mime) {
            "image/jpeg", "image/png" => $this->validateImage(),
            "video/mp4"               => $this->validateVideo(),
            default                   => throw new UnsupportedFileFormatException(),
        };
    }

    /**
     * @return float
     */
    public function getCreativeLength(): float {
        if (!$this->validationPerformed) {
            throw new RuntimeException("You need to call `validate()` before getting the creative length");
        }

        return $this->creativeLength;
    }

    /**
     * @return bool
     * @throws InvalidCreativeDimensions
     * @throws InvalidCreativeSize
     */
    protected function validateImage(): bool {
        [$this->creativeWidth, $this->creativeHeight] = getimagesize($this->file);

        $this->validateAspectRatio();

        // Weight
        $maxSize = param(BroadcastParameters::CreativeImageMaxSizeMiB) * 1.049e+6; // MiB to Byte
        if ($this->file->getSize() > $maxSize) { //10 Mib
            throw new InvalidCreativeSize();
        }

        $this->validationPerformed = true;

        return true;
    }

    /**
     * @return bool
     * @throws InvalidCreativeDimensions
     * @throws InvalidCreativeDuration
     * @throws InvalidCreativeFrameRate
     * @throws InvalidVideoCodec
     * @throws UnreadableCreativeException
     * @throws InvalidCreativeSize
     */
    protected function validateVideo(): bool {
        /** @noinspection SpellCheckingInspection */
        $ffprobe = FFProbe::create(config('ffmpeg'));

        $authorizedCodecs = ["h264"];

        $videoStream = $ffprobe->streams($this->file->getRealPath())->videos()->first(); //Select the video

        if (is_null($videoStream)) {
            throw new UnreadableCreativeException();
        }

        $fileInformation = $ffprobe->format($this->file->getRealPath());

        //Check video codec
        $codec = $videoStream->get("codec_name");
        if (!in_array($codec, $authorizedCodecs, true)) {
            throw new InvalidVideoCodec($codec);
        }

        //Check video dimensions
        $this->creativeWidth  = (int)$videoStream->get("width");
        $this->creativeHeight = (int)$videoStream->get("height");
        $this->validateAspectRatio();

        //Check framerate
        $framerate = $this->fracToFloat($videoStream->get("r_frame_rate"));
        if ($framerate < 23.9 || $framerate > 30) {
            throw new InvalidCreativeFrameRate();
        }

        // Check length if content length is not zero
        $this->creativeLength = (double)$fileInformation->get("duration");
        // Allow up to 1 second of difference between content and creative
        if (($this->content->duration > 0) && abs($this->creativeLength - $this->content->duration) > 1) {
            throw new InvalidCreativeDuration(expectedLength: $this->content->duration, foundLength: $this->creativeLength);
        }

        // Weight
        $maxSize = param(BroadcastParameters::CreativeVideoMaxSizeMiB) * 1.049e+6;       // MiB to Byte
        if ($this->file->getSize() > $maxSize) {
            throw new InvalidCreativeSize();
        }

        $this->validationPerformed = true;
        return true;
    }

    protected function validateAspectRatio() {
        $creativeAspectRatio = aspect_ratio($this->creativeWidth / $this->creativeHeight);
        $frameAspectRatio    = aspect_ratio($this->frame->width / $this->frame->height);

        if ($creativeAspectRatio[0] !== $frameAspectRatio[0] || $creativeAspectRatio[1] !== $frameAspectRatio[1]) {
            throw new InvalidCreativeDimensions(
                expectedWidth: $this->frame->width,
                expectedHeight: $this->frame->height,
                foundWidth: $this->creativeWidth,
                foundHeight: $this->creativeHeight,
            );
        }
    }


    private function fracToFloat($frac): float {
        $numbers = explode("/", $frac);
        return round((double)$numbers[0] / (double)$numbers[1], 6);
    }
}
