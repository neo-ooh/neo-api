<?php

namespace Neo\Documents\Contract;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use League\Csv\Reader;
use Mpdf\HTMLParserMode;
use Neo\Documents\Document;
use Neo\Documents\Network;

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

    protected function build($data): bool {
        $this->ingest($data);

        // Import the stylesheet
        $this->mpdf->WriteHTML(File::get(resource_path('documents/stylesheets/contract.css')), HTMLParserMode::HEADER_CSS);

        // Build Campaign Details page
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

    private function makeCampaignDetails() {
        // Set the proper header
        $this->mpdf->SetHTMLHeader(view('documents.contract.header-campaign-summary', ["customer" => $this->customer, "order" => $this->order])->render());

        // Create a new 14" by 14" page
        $orientation = "P";
        $this->mpdf->_setPageSize([355, 355], $orientation);
        $this->mpdf->SetMargins(0, 0, 50);
        $this->mpdf->AddPage();

        // We want to show the guaranteed purchases for each network.
        foreach ([Network::NEO_SHOPPING, Network::NEO_OTG, Network::NEO_FITNESS] as $network) {
            // Filter orders
            $purchases = $this->orderLines
                ->filter(fn(/**@var OrderLine $order */ $order) =>
                    $order->isNetwork($network) &&
                    $order->total_tax != 0
            )
                                          ->sortBy(['market', 'property_name'])
                                          ->groupBy(['market', 'property_name']);

            // if no order for this network, skip
            if (count($purchases) === 0) {
                continue;
            }

            // Render the Guaranteed purchase for this network
            $this->mpdf->WriteHTML(view('documents.contract.detailed-network-orders', [
                "type"    => "purchase",
                "network" => $network,
                "purchasesByRegion" => $purchases
            ])->render());
        }
    }
}
