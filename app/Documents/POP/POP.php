<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POP.php
 */

namespace Neo\Documents\POP;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Mpdf\HTMLParserMode;
use Neo\Documents\PDFDocument;

class POP extends PDFDocument {
    protected POPData $data;

    protected string $header_view = "documents.pop.header";
    protected string $footer_view = "documents.pop.footer";

    public function __construct() {
        parent::__construct([
            "margin_bottom" => 25,
            "packTableData" => true,
            "use_kwt"       => true,
        ]);
    }

    protected function ingest($data): bool {
        $this->data = new POPData($data);

        return true;
    }

    public function build(): bool {
        App::setLocale($this->data->locale);

        // Import styling
        $this->mpdf->WriteHTML(File::get(resource_path('documents/stylesheets/pop.css')), HTMLParserMode::HEADER_CSS);

        // First, the preface
        // Build the cover page
        $this->mpdf->AddPageByArray([
            "sheet-size"   => "Legal-L",
            "pageselector" => "preface"
        ]);

        $this->mpdf->WriteHTML(view("documents.pop.coverpage", [
            "locale" => $this->data->locale
        ])->render());

        $reservations = $this->data->values->where("type", "!==", "bua");

        if (count($reservations) === 0) {
            $reservations = $this->data->values;
        }

        // Build the preface page
        $this->mpdf->WriteHTML(view("documents.pop.preface", [
            "data"       => $this->data,
            "start_date" => $reservations->min("start_date")->format("Y-m-d"),
            "end_date"   => $reservations->max("end_date")->format("Y-m-d"),
        ])->render());


        // Print the summary page
        $this->setLayout(__("pop.title"), "Legal-L", ["data" => $this->data], "BLANK");

        $this->mpdf->WriteHTML(view("documents.pop.summary-page", [
            "data"              => $this->data,
            "guaranteed_values" => $this->data->values->where("type", "=", "guaranteed")->first(),
            "bonus_values"      => $this->data->values->where("type", "=", "bonus")->first(),
            "bua_values"        => $this->data->values->where("type", "=", "bua")->first(),
        ])->render());

        // If we have screenshots, display them
        if (count($this->data->screenshots) > 0) {
            $this->mpdf->WriteHTML(view("documents.pop.screenshots", [
                "screenshots" => $this->data->screenshots
            ]));
        }

        $this->mpdf->Close();

        return true;
    }

    public function getName(): string {
        $name = __("pop.title") . " • " . __("pop.subtitle", ["contract" => $this->data->contract_name]);
        return $name . " • Neo-OOH";
    }
}
