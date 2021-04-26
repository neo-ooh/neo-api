<?php

namespace Neo\Documents\POP;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Mpdf\HTMLParserMode;
use Neo\Documents\Document;

class POP extends Document {
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
            ->map(function($reservation) {
                $reservation["start_date"] = Date::make($reservation["start_date"]);
                $reservation["end_date"] = Date::make($reservation["end_date"]);
                return $reservation;
            });

        $this->contract["networks"] = collect($this->contract["networks"]);

        // Calculate contract properties
        $this->contract["guaranteed_impressions"] = $this->contract["networks"]->sum("guaranteed_impressions");
        $this->contract["guaranteed_media_value"] = $this->contract["networks"]->sum("guaranteed_media_value");
        $this->contract["net_investment"] = $this->contract["networks"]->sum("guaranteed_net_investment");
        $this->contract["bonus_impressions"] = $this->contract["networks"]->sum("bonus_impressions");
        $this->contract["bonus_media_value"] = $this->contract["networks"]->sum("bonus_media_value");

        $this->contract["contracted_impressions"] = $this->contract["guaranteed_impressions"] + $this->contract["bonus_impressions"];
        $this->contract["contracted_media_value"] = $this->contract["guaranteed_media_value"] + $this->contract["bonus_media_value"];

        $this->contract["total_received_impressions"] = $this->contract["reservations"]->sum("received_impressions");

        $this->contract["contracted_cpm"] = 0;
        $this->contract["current_cpm"] = 0;

        // calculate cpm if possible
        if($this->contract["contracted_impressions"] !== 0) {
            $this->contract["contracted_cpm"] = $this->contract["net_investment"] / $this->contract["contracted_impressions"] * 1000;
        }

        if($this->contract["total_received_impressions"] !== 0) {
            $this->contract["current_cpm"] = $this->contract["net_investment"] / $this->contract["total_received_impressions"] * 1000;

            // Calculate current media value per network
            $this->contract["current_guaranteed_value"] = ($this->contract["reservations"]->where("type", "guaranteed")->sum("received_impressions") / 1000) * $this->contract["current_cpm"];
            $this->contract["current_bonus_value"] = ($this->contract["reservations"]->where("network", "bonus")->sum("received_impressions") / 1000) * $this->contract["current_cpm"];
            $this->contract["current_bua_value"] = ($this->contract["reservations"]->where("network", "otg")->sum("received_impressions") / 1000) * $this->contract["current_cpm"];
            $this->contract["current_value"] = $this->contract["current_guaranteed_value"] + $this->contract["current_bonus_value"] + $this->contract["current_bua_value"];
        }

        return true;
    }

    public function build(): bool {
        // Import styling
        $this->mpdf->WriteHTML(File::get(resource_path('documents/stylesheets/pop.css')), HTMLParserMode::HEADER_CSS);

        // Set the layout
        $this->setLayout("", "legal");

        // Print the summary page
        $this->mpdf->WriteHTML(view("documents.pop.summary-page", [
            "contract" => $this->contract,
            "purchaseReservations" => $this->contract["reservations"]->where("type", "guaranteed"),
            "bonusReservations" => $this->contract["reservations"]->where("type", "bonus"),
            "buaReservations" => $this->contract["reservations"]->where("type", "bua")
        ])->render());

        $this->mpdf->Close();

        return true;
    }
}
