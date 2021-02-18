<?php

namespace Neo\Documents\Contract;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use League\Csv\Reader;
use Mpdf\HTMLParserMode;
use Neo\Documents\Contract\Components\DetailedOrders;
use Neo\Documents\Contract\Components\DetailedSummary;
use Neo\Documents\Contract\Components\Totals;
use Neo\Documents\Document;

class Contract extends Document {
    protected Customer $customer;
    protected Order $order;
    protected Collection $production;

    protected array $regions = [
        "Greater Montreal",
        "Eastern Townships",
        "Center of Quebec",
        "Hull - Gatineau",
        "Quebec City & Region",
        "Northwest of Quebec",
        "Northeast of Quebec",
        "Greater Toronto",
        "North-Western Ontario",
        "South-Western Ontario",
        "Kingston / Belleville",
        "Ottawa",
        "Greater Vancouver Area",
        "Winnipeg & Region",
        "Regina & Region",
        "Edmonton & Region",
        "Calgary & Region",
        "Halifax & Region",
        "New Brunswick"
    ];

    /**
     * @var Collection
     */
    protected Collection $orderLines;

    public function __construct() {
        parent::__construct();

        // Register our components
        Blade::componentNamespace("Neo\\Documents\\Contract\\Components", "contract");

        $this->production = new Collection();
    }

    protected function build($data): bool {
        $this->ingest($data);

        // Import the stylesheet
        $this->mpdf->WriteHTML(File::get(resource_path('documents/stylesheets/contract.css')), HTMLParserMode::HEADER_CSS);

        if ($this->orderLines->count() === 0) {
            // Production Contract
            $this->setHeader("Production Costs");
            $this->setFooter();

            $orientation = "P";
            $this->mpdf->_setPageSize([355, 355], $orientation);
            $this->mpdf->SetMargins(0, 0, 50);
            $this->mpdf->AddPage();

            $this->renderDetailedSummary();

            return true;
        }

        // Build each section
        $this->makeCampaignSummary();
        $this->makeCampaignDetails();
        return true;
    }

    public function output() {
        return $this->mpdf->Output();
    }

    private function ingest($data): void {
        // Data is expected to be a CSV file
        // Read the csv file
        $reader = Reader::createFromString($data);
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);

        // Get all records in the file
        $records = $reader->getRecords();

        $this->orderLines = new Collection();

        // Parse all records
        foreach ($records as $offset => $record) {
            // The first record holds additional informations such as customer and order info
            if ($offset === 1) {
                $this->customer = new Customer($record);
                $this->order    = new Order($record);
            }

            $orderLine = new OrderLine($record);

            if ($orderLine->is_production === 'VRAI') {
                $this->production->push($orderLine);
                return;
            }

            // Each line holds one Order Line
            $this->orderLines->push($orderLine);
        }
    }

    private function makeCampaignSummary(): void {
        // Update the header
        $this->setHeader("Campaign Summary");
        $this->setFooter();

        // Create a new letter page
        $orientation = "P";
        $this->mpdf->_setPageSize("legal", $orientation);
        $this->mpdf->SetMargins(0, 0, 50);
        $this->mpdf->AddPage($orientation, "", 1);

        $campaignSummaryOrders = view('documents.contract.campaign-summary.orders', [
            "purchaseOrders" => $this->orderLines->filter(fn($order) => $order->isGuaranteedPurchase()),
            "bonusOrders"    => $this->orderLines->filter(fn($order) => $order->isGuaranteedBonus()),
            "buaOrders"      => $this->orderLines->filter(fn($order) => $order->isBonusUponAvailability()),
            "order" => $this->order
        ])->render();

        $this->mpdf->WriteHTML($campaignSummaryOrders);

        $this->mpdf->WriteHTML((new Totals($this->orderLines, "full", $this->production))->render()->render());
    }

    private function makeCampaignDetails(): void {
        // Update the header
        $this->setHeader("Campaign Details");

        // Create a new 14" by 14" page
        $orientation = "P";
        $this->mpdf->_setPageSize([355, 355], $orientation);
        $this->mpdf->SetMargins(0, 0, 50);
        $this->mpdf->AddPage($orientation, "", 1);

        $this->setFooter();

        $campaignDetails = new DetailedOrders($this->order, $this->orderLines);

        $this->mpdf->WriteHTML($campaignDetails->render()->render());

        $this->renderDetailedSummary();
    }

    private function setHeader($title) {
        $this->mpdf->SetHTMLHeader(view('documents.contract.header', [
            "title"    => $title,
            "customer" => $this->customer,
            "order"    => $this->order
        ])->render());
    }

    private function setFooter() {
        $this->mpdf->SetHTMLFooter(view('documents.contract.footer', [
            "width" => 345
        ])->render());
    }

    private function renderDetailedSummary() {
        $campaignDetailedSummary = new DetailedSummary($this->orderLines, $this->production);
        $this->mpdf->WriteHTML($campaignDetailedSummary->render()->render());
    }
}
