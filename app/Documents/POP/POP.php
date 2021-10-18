<?php

namespace Neo\Documents\POP;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Mpdf\HTMLParserMode;
use Neo\Documents\PDFDocument;
use Neo\Models\ContractScreenshot;

class POP extends PDFDocument {
    protected array $contract;

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
        $this->contract = $data;

        $this->contract["reservations"] = collect($this->contract["reservations"])
            ->values()
            ->filter(fn($reservation) => $reservation["show"])
            ->filter(fn($reservation) => $this->contract["show_" . $reservation["type"] . "_reservations"])
            ->map(function ($reservation) {
                $reservation["start_date"] = Date::make($reservation["start_date"]);
                $reservation["end_date"]   = Date::make($reservation["end_date"]);

                $reservation["received_impressions"] = round($reservation["received_impressions"] * $this->contract[$reservation["type"] . "_reservations_impressions_factor"]);

                return $reservation;
            });

        $this->contract["networks"] = collect($this->contract["networks"]);

        // Calculate contract properties
        $this->contract["guaranteed_impressions"] = $this->contract["networks"]->sum("guaranteed_impressions");
        $this->contract["guaranteed_media_value"] = $this->contract["networks"]->sum("guaranteed_media_value");
        $this->contract["net_investment"]         = $this->contract["networks"]->sum("guaranteed_net_investment");
        $this->contract["bonus_impressions"]      = $this->contract["networks"]->sum("bonus_impressions");
        $this->contract["bonus_media_value"]      = $this->contract["networks"]->sum("bonus_media_value");
        $this->contract["bua_impressions"]        = $this->contract["networks"]->sum("bua_impressions");
        $this->contract["bua_media_value"]        = $this->contract["networks"]->sum("bua_media_value");

        $this->contract["contracted_impressions"] = $this->contract["guaranteed_impressions"] + $this->contract["bonus_impressions"];
        $this->contract["contracted_media_value"] = $this->contract["guaranteed_media_value"] + $this->contract["bonus_media_value"];

        $this->contract["total_received_impressions"] = $this->contract["reservations"]->sum("received_impressions");

        $this->contract["contracted_cpm"]           = 0;
        $this->contract["current_cpm"]              = 0;
        $this->contract["current_guaranteed_value"] = 0;
        $this->contract["current_bonus_value"]      = 0;
        $this->contract["current_bua_value"]        = 0;
        $this->contract["current_value"]            = 0;

        // If we received any impression, we are able to deduce the current cpm
        if ($this->contract["total_received_impressions"] > 0) {
            $this->contract["current_cpm"] = $this->contract["net_investment"] / ($this->contract["total_received_impressions"] / 1000);
        }

        // Execute calculations for each type of buys if possible
        if ($this->contract["contracted_impressions"] > 0) {
            // Deduce contracted CPM
            $this->contract["contracted_cpm"] = $this->contract["net_investment"] / ($this->contract["contracted_impressions"] / 1000);

            // Deduce current media value for the type of buy
            $imprValue = $this->contract["contracted_media_value"] / $this->contract["contracted_impressions"];

            $this->contract["current_guaranteed_value"] = $imprValue * $this->contract["reservations"]->where("type", "guaranteed")->sum("received_impressions");
        }

        if($this->contract["bonus_impressions"] > 0) {
            // Deduce current media value for the type of buy
            $imprValue = $this->contract["bonus_media_value"] / $this->contract["bonus_impressions"];

            $this->contract["current_bonus_value"] = $imprValue * $this->contract["reservations"]->where("type", "bonus")
                                                                                                 ->sum("received_impressions");
        }

        if($this->contract["bua_impressions"] > 0) {
            // Deduce current media value for the type of buy
            $imprValue = $this->contract["bua_media_value"] / $this->contract["bua_impressions"];

            $this->contract["current_bua_value"] = $imprValue * $this->contract["reservations"]->where("type", "bua")
                                                                                               ->sum("received_impressions");
        }

        $this->contract["current_value"] = $this->contract["current_guaranteed_value"] + $this->contract["current_bonus_value"] + $this->contract["current_bua_value"];

        // Map the screenshots Ids to their model counterpart
        $this->contract["screenshots"] = ContractScreenshot::query()
                                                           ->whereIn("id", collect($this->contract["screenshots"]))
                                                           ->with(["burst", "burst.location"])
                                                           ->get();

        // Lock the screenshots
        $this->contract["screenshots"]->each(function(ContractScreenshot $screenshot) {
            $screenshot->is_locked = true;
            $screenshot->save();
            $screenshot->created_at->tz("America/Toronto");
        } );

        return true;
    }

    public function build(): bool {
        App::setLocale($this->contract["locale"]);

        // Import styling
        $this->mpdf->WriteHTML(File::get(resource_path('documents/stylesheets/pop.css')), HTMLParserMode::HEADER_CSS);

        // First, the preface
        // Build the cover page
        $this->mpdf->AddPageByArray([
            "sheet-size"   => "Legal-L",
            "pageselector" => "preface"
        ]);

        $this->mpdf->WriteHTML(view("documents.pop.coverpage", [
            "locale" => $this->contract["locale"]
        ])->render());

        $reservations = $this->contract["reservations"]->where("type", "!==", "bua");

        if(count($reservations) === 0) {
            $reservations = $this->contract["reservations"];
        }

        // Build the preface page
        $this->mpdf->WriteHTML(view("documents.pop.preface", [
            "contract"   => $this->contract,
            "start_date" => $reservations->min("start_date")->format("Y-m-d"),
            "end_date"   => $reservations->max("end_date")->format("Y-m-d"),
        ])->render());


        // Print the summary page
        $this->setLayout(__("pop.title"), "Legal-L", ["contract" => $this->contract], "BLANK");

        $this->mpdf->WriteHTML(view("documents.pop.summary-page", [
            "contract"             => $this->contract,
            "purchaseReservations" => $this->contract["reservations"]->where("type", "guaranteed"),
            "bonusReservations"    => $this->contract["reservations"]->where("type", "bonus"),
            "buaReservations"      => $this->contract["reservations"]->where("type", "bua")
        ])->render());

        // If we have screenshots, display them
        if (count($this->contract["screenshots"]) > 0) {
            $this->mpdf->WriteHTML(view("documents.pop.screenshots", [
                "screenshots" => $this->contract["screenshots"]
            ]));
        }

        $this->mpdf->Close();

        return true;
    }

    public function getName(): string {
        $name = __("pop.title") . " • " . __("pop.subtitle", ["contract" => $this->contract["contract_id"]]);
        return $name . " • Neo-OOH";
    }
}
