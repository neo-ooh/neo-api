<?php

namespace Neo\Documents\Contract;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use League\Csv\Reader;
use Mpdf\HTMLParserMode;
use Neo\Documents\Document;

class Contract extends Document {
    protected Customer $customer;
    protected Order $order;

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

//        $this->mpdf->simpleTables = true;
    }

    protected function build($data): bool {
        $this->ingest($data);

        // Import the stylesheet
        $this->mpdf->WriteHTML(File::get(resource_path('documents/stylesheets/contract.css')), HTMLParserMode::HEADER_CSS);

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

            // Each line holds one Order Line
            $this->orderLines->push(new OrderLine($record));
        }
    }

    private function makeCampaignSummary(): void {
        // Update the header
        $this->mpdf->SetHTMLHeader(view('documents.contract.header', [
            "title"    => "Campaign Summary",
            "customer" => $this->customer,
            "order"    => $this->order
        ])->render());

        $this->mpdf->SetHTMLFooter(view('documents.contract.footer', ["width" => 203])->render());

        // Create a new letter page
        $orientation = "P";
        $this->mpdf->_setPageSize("legal", $orientation);
        $this->mpdf->SetMargins(0, 0, 50);
        $this->mpdf->AddPage($orientation, "", 1);

        $campaignSummary = view('documents.contract.summary', [
            "orders" => $this->orderLines
        ])->render();

        $this->mpdf->WriteHTML($campaignSummary);
    }

    private function makeCampaignDetails(): void {
        // Update the header
        $this->mpdf->SetHTMLHeader(view('documents.contract.header', [
            "title"    => "Campaign Details",
            "customer" => $this->customer,
            "order"    => $this->order
        ])->render());

        // Create a new 14" by 14" page
        $orientation = "P";
        $this->mpdf->_setPageSize([355, 355], $orientation);
        $this->mpdf->SetMargins(0, 0, 50);
        $this->mpdf->AddPage($orientation, "", 1);

        $this->mpdf->SetHTMLFooter(view('documents.contract.footer', ["width" => 345])->render());

        $campaignDetails = view('documents.contract.details', [
            "orders" => $this->orderLines
        ])->render();

        $this->mpdf->WriteHTML($campaignDetails);
    }
}
