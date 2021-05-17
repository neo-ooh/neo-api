<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Contract.php
 */

namespace Neo\Documents\Contract;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use League\Csv\Reader;
use Mpdf\HTMLParserMode;
use Neo\Documents\Contract\Components\ContractFirstPage;
use Neo\Documents\Contract\Components\DetailedOrdersCategory;
use Neo\Documents\Contract\Components\DetailedOrdersTable;
use Neo\Documents\Contract\Components\DetailedSummary;
use Neo\Documents\Contract\Components\GeneralConditions;
use Neo\Documents\Contract\Components\Totals;
use Neo\Documents\Document;
use Neo\Documents\Network;

class Contract extends Document {
    public const TYPE_PROPOSAL = 'proposal';
    public const TYPE_CONTRACT = 'contract';

    protected string $documentType;

    protected Customer $customer;
    protected Order $order;

    protected string $header_view = "documents.contract.header";
    protected string $footer_view = "documents.contract.footer";

    public function __construct() {
        parent::__construct([
            "margin_bottom"     => 25,
            "packTableData"     => true,
            "use_kwt"           => true,
            "setAutoTopMargin"  => "pad",
        ]);

        // Register our components
        Blade::componentNamespace("Neo\\Documents\\Contract\\Components", "contract");

        // Some very large contracts exceeds the PCRE backtrack limit. So we increase it to prevent crash
        ini_set("pcre.backtrack_limit", "5000000");
    }

    public static function makeContract($data): Document {
        $document               = parent::make($data);
        $document->documentType = self::TYPE_CONTRACT;

        return $document;
    }

    public static function makeProposal($data): Document {
        $document               = parent::make($data);
        $document->documentType = self::TYPE_PROPOSAL;

        return $document;
    }

    public function ingest($data): bool {
        // Data is expected to be a CSV file
        // Read the csv file
        $reader = Reader::createFromString($data);
        $reader->setDelimiter(',');
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
                $this->order->productionLines->push($orderLine);
                continue;
            }

            if ((int)$orderLine->unit_price === 0 && $orderLine->isNetwork(Network::NEO_OTG)) {
                // -Dans le On the Go, nous avons lié les produits In Screen et Full Screen dans une même propriété. Pourquoi? Parce qu'ils ont le même inventaire. Exemple: il y a 15 spot de dispo. Si un client achète un Digital Full Screen, il reste donc 14 dispos. Il va donc aussi rester 14 dispo autant pour In Screen que pour le Full Screen. Ce sont deux produits differents dans le même écran.
                //Donc, dans Odoo, lorsque j'ajoute, un Full screen (ou vice versa), ca l'ajoute aussi un in screen qui toutefois se n'a aucune valeurs dans cette propositions. Ainsi, dans le cas que ca arrive, il ne faut pas affiher le In screen. En plus, il ne doit pas faire partie des calculs sur la ligne de total.
                //Maintenant, quel champ utilisé. Je crois que le meilleur champs serait: Order Lines/Unit Price. Lorsqu'il est à 0, on affiche pas. Note que ceci est exclusif à On the Go.
                continue;
            }

            // Each line holds one Order Line
            $this->order->orderLines->push($orderLine);
        }

        // All data has been correctly parsed and imported, let's make some calculations right now
        $this->order->computeValues();

        return true;
    }

    public function build(): bool {
        App::setLocale(substr($this->order->locale, 0, 2));

        // Import the stylesheet
        $this->mpdf->WriteHTML(File::get(resource_path('documents/stylesheets/contract.css')), HTMLParserMode::HEADER_CSS);

        // Build the document

        // Contracts have an additional firsst page
        if ($this->documentType === self::TYPE_CONTRACT) {
            $this->makeContractFirstPage();
        }

        // If there is no order lines, we assume its a production export, and only show the detailed summary
        if ($this->order->orderLines->count() === 0) {
            $this->renderProductionDocument();
        } else {
            $this->makeCampaignSummary();
            $this->makeCampaignDetails();
        }

        if ($this->documentType === self::TYPE_CONTRACT) {
            $this->makeGeneralConditions();
        }

        return true;
    }

    public function getName(): string {
        $name = $this->documentType === static::TYPE_CONTRACT
            ? __("contract.contract", ["contract" => $this->order->reference])
            : __("contract.proposal", ["contract" => $this->order->reference]);

        return $name . " • " . $this->order->company_name;
    }

    private function makeContractFirstPage(): void {
        $this->setLayout("", "legal", [
            "customer" => $this->customer,
            "order"    => $this->order,
        ]);

        $this->mpdf->WriteHTML((new ContractFirstPage($this->order, $this->customer))->render()->render());
    }

    private function makeGeneralConditions(): void {
        $this->setLayout("", "legal", [
            "customer" => $this->customer,
            "order"    => $this->order
        ]);

        $this->mpdf->WriteHTML((new GeneralConditions())->render()->render());
    }

    private function makeCampaignSummary(): void {
        $this->setLayout(__("contract.campaign-summary-title"), "legal", [
            "customer" => $this->customer,
            "order"    => $this->order
        ]);

        // Purchase summary
        $purchaseOrders = $this->order->getPurchasedOrders();

        if ($purchaseOrders->isNotEmpty()) {
            $this->mpdf->WriteHTML(view("documents.contract.campaign-summary.orders-category", [
                "category" => "purchase",
                "orders"   => $purchaseOrders,
                "order"    => $this->order,
            ])->render());
        }

        // Bonus summary
        $bonusOrders = $this->order->getBonusOrders();

        if ($bonusOrders->isNotEmpty()) {
            $this->mpdf->WriteHTML(view("documents.contract.campaign-summary.orders-category", [
                "category" => "bonus",
                "orders"   => $bonusOrders,
                "order"    => $this->order,
            ])->render());
        }

        // Bonus summary
        $buaOrders = $this->order->getBonusOrders();

        if ($buaOrders->isNotEmpty()) {
            $this->mpdf->WriteHTML(view("documents.contract.campaign-summary.orders-category", [
                "category" => "bua",
                "orders"   => $buaOrders,
                "order"    => $this->order,
            ])->render());
        }

        $this->mpdf->WriteHTML((new Totals($this->order, $this->order->orderLines, "full", $this->order->productionLines))->render());
    }

    private function makeCampaignDetails(): void {
        $this->setLayout(__("contract.campaign-details-title"), [355, 355], [
            "customer" => $this->customer,
            "order"    => $this->order
        ]);

        foreach (["purchase", "bonus", "bua"] as $orderType) {
            if ($orderType === 'bonus' && $this->order->getBonusOrders()->isNotEmpty()) {
                $this->mpdf->AddPage();
            } else if ($orderType === 'bua' && $this->order->getBuaOrders()->isNotEmpty()) {
                $this->mpdf->AddPage();
            }

            foreach (["shopping", "otg", "fitness"] as $network) {
                $this->mpdf->WriteHTML(new DetailedOrdersTable($orderType, $this->order, $network));
            }

            $orders = new DetailedOrdersCategory($orderType, $this->order, $this->order->orderLines);
            $this->mpdf->WriteHTML($orders);
        }

        $this->mpdf->AddPage();

        $this->renderDetailedSummary(true);
    }

    private function renderProductionDocument() {
        $this->setLayout(__("contract.production-details"), [355, 355], [
            "customer" => $this->customer,
            "order"    => $this->order
        ]);

        $this->mpdf->SetMargins(15, 15, 40);
        $this->renderDetailedSummary(false);
    }

    private function renderDetailedSummary(bool $renderDisclaimers) {
        $campaignDetailedSummary = new DetailedSummary($this->order, $this->order->orderLines, $this->order->productionLines, $renderDisclaimers);
        $this->mpdf->WriteHTML($campaignDetailedSummary->render()->render());
    }
}
