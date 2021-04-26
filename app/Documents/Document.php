<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Document.php
 */

namespace Neo\Documents;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Neo\Documents\Exceptions\UnknownGenerationException;

abstract class Document {
    /**
     * @param mixed $data
     * @return Document
     * @throws UnknownGenerationException
     */
    public static function make($data): Document {
        $document = new static();

        if (!$document->ingest($data)) {
            throw new UnknownGenerationException();
        }

        return $document;
    }

    /*
     * Internal methods
     */

    protected array $settings;

    protected Mpdf $mpdf;

    protected function __construct(array $mpdfConfiguration) {
        // before doing anything, update the max execution time to prevent timeout
        set_time_limit(60);

        $this->mpdf = new Mpdf(array_merge([
            "fontDir"      => [resource_path('fonts/')],
            "fontdata"     => [
                "poppins-bold"        => [
                    "R"  => "Poppins-SemiBold.ttf",
                    "I"  => "Poppins-SemiBoldItalic.ttf",
                    "B"  => "Poppins-Bold.ttf",
                    "BI" => "Poppins-BoldItalic.ttf",
                ],
                "poppins-regular"     => [
                    "R"  => "Poppins-Regular.ttf",
                    "I"  => "Poppins-Italic.ttf",
                    "B"  => "Poppins-Medium.ttf",
                    "BI" => "Poppins-MediumItalic.ttf",
                ],
                "poppins-light"       => [
                    "R" => "Poppins-Light.ttf",
                    "I" => "Poppins-Italic.ttf",
                ],
                "poppins-extra-light" => [
                    "R" => "Poppins-ExtraLight.ttf",
                    "I" => "Poppins-ExtraItalic.ttf",
                ]
            ],
            'default_font' => 'poppins-regular',
        ], $mpdfConfiguration
        ));
    }

    /**
     * Holds the data ingestion logic.
     *
     * @param $data
     * @return bool A boolean value indicating if the data was correctly ingested or not
     */
    abstract protected function ingest($data): bool;

    /**
     * Holds the entire document generation logic. Each document has to define one.
     *
     * @return bool A boolean value indicating if the document generation was successful or not
     */
    abstract public function build(): bool;

    public function output() {
        return $this->mpdf->Output("", Destination::STRING_RETURN);
    }


    /*
     * Useful methods for document building
     */

    protected string $header_view = "";
    protected string $footer_view = "";

    protected function setLayout(string $title, $dimensions, $context = []): void {
        $this->mpdf->SetHTMLHeader(view($this->header_view, [
            "title"    => $title,
            ...$context
        ])->render());

        // Create a new 14" by 14" page
        $orientation = "P";
        $this->mpdf->_setPageSize($dimensions, $orientation);
        $this->mpdf->SetMargins(0, 0, 45);
        $this->mpdf->AddPage($orientation, "", 1);

        $this->mpdf->SetHTMLFooter(view($this->footer_view, [
            "width" => is_array($dimensions) ? $dimensions[0]-5 : 210,
            ...$context
        ])->render());
    }
}
