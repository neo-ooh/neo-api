<?php

namespace Neo\Documents\Contract;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use League\Csv\Reader;
use Mpdf\HTMLParserMode;
use Neo\Documents\Contract\Components\DetailedOrders;
use Neo\Documents\Contract\Components\DetailedOrdersCategory;
use Neo\Documents\Contract\Components\DetailedSummary;
use Neo\Documents\Contract\Components\Totals;
use Neo\Documents\Document;
use Neo\Documents\Network;

class Contract extends Document {
    protected Customer $customer;
    protected Order $order;
    protected Collection $production;

    /**
     * @var Collection
     */
    protected Collection $orderLines;

    public function __construct() {
        parent::__construct([
            "margin_bottom" => 25,
        ]);

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
            $this->setHeader("Production Details");
            $this->setFooter();

            $orientation = "P";
            $this->mpdf->_setPageSize([355, 355], $orientation);
            $this->mpdf->SetMargins(0, 0, 50);
            $this->mpdf->AddPage();

            $this->renderDetailedSummary(false);

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

            if ($orderLine->is_production) {
                $this->production->push($orderLine);
                return;
            }

            if((int)$orderLine->unit_price === 0 && $orderLine->isNetwork(Network::NEO_OTG)) {
                // -Dans le On the Go, nous avons lié les produits In Screen et Full Screen dans une même propriété. Pourquoi? Parce qu'ils ont le même inventaire. Exemple: il y a 15 spot de dispo. Si un client achète un Digital Full Screen, il reste donc 14 dispos. Il va donc aussi rester 14 dispo autant pour In Screen que pour le Full Screen. Ce sont deux produits differents dans le même écran.
                //Donc, dans Odoo, lorsque j'ajoute, un Full screen (ou vice versa), ca l'ajoute aussi un in screen qui toutefois se n'a aucune valeurs dans cette propositions. Ainsi, dans le cas que ca arrive, il ne faut pas affiher le In screen. En plus, il ne doit pas faire partie des calculs sur la ligne de total.
                //Maintenant, quel champ utilisé. Je crois que le meilleur champs serait: Order Lines/Unit Price. Lorsqu'il est à 0, on affiche pas. Note que ceci est exclusif à On the Go.
                continue;
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

        foreach(["purchase", "bonus", "bua"] as $orderType) {
            $orders = new DetailedOrdersCategory($orderType, $this->order, $this->orderLines);
            $this->mpdf->WriteHTML($orders);
        }

        $this->mpdf->AddPage();

        $this->renderDetailedSummary(true);
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

    private function renderDetailedSummary(bool $renderDisclaimers) {
        $campaignDetailedSummary = new DetailedSummary($this->orderLines, $this->production, $renderDisclaimers);
        $this->mpdf->WriteHTML($campaignDetailedSummary->render()->render());
    }
}
