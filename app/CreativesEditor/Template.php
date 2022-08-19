<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Template.php
 */

namespace Neo\CreativesEditor;

use Arr;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use JsonException;
use function Fuse\Helpers\get;

class Template implements CastsAttributes {
    // Title
    public string $titleText;
    public string $titleFont;
    public string $titleColor;
    public string $titleBgColor;

    // Body
    public string $bodyText;
    public string $bodyFont;
    public string $bodyColor;

    // Background
    public const BACKGROUND_PLAIN = 'plain';
    public const BACKGROUND_IMAGE = 'image';

    public string $backgroundType;
    public string $backgroundColor;

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function get($model, string $key, $value, array $attributes) {
        return json_encode([
            "title"      => [
                "text"    => $this->titleText,
                "font"    => $this->titleFont,
                "color"   => $this->titleColor,
                "bgcolor" => $this->titleBgColor,
            ],
            "body"       => [
                "text"  => $this->bodyText,
                "font"  => $this->bodyFont,
                "color" => $this->bodyColor,
            ],
            "background" => [
                "type"  => $this->backgroundType,
                "color" => $this->backgroundColor,
            ]
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes) {
        $templateData = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        $this->titleText    = Arr::get($templateData, "title.text");
        $this->titleFont    = Arr::get($templateData, "title.font");
        $this->titleColor   = Arr::get($templateData, "title.color");
        $this->titleBgColor = Arr::get($templateData, "title.bgcolor");

        $this->bodyText  = Arr::get($templateData, "body.text");
        $this->bodyFont  = Arr::get($templateData, "body.font");
        $this->bodyColor = Arr::get($templateData, "body.color");

        $this->backgroundType  = Arr::get($templateData, "background.type");
        $this->backgroundColor = Arr::get($templateData, "background.color");
    }
}
