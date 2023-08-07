<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PDFContract.php
 */

namespace Neo\Documents\Contract;

use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Mpdf\HTMLParserMode;
use Neo\Documents\Contract\PDFComponents\ContractFirstPage;
use Neo\Documents\Contract\PDFComponents\DetailedOrdersCategory;
use Neo\Documents\Contract\PDFComponents\DetailedOrdersTable;
use Neo\Documents\Contract\PDFComponents\DetailedSummary;
use Neo\Documents\Contract\PDFComponents\GeneralConditions;
use Neo\Documents\Contract\PDFComponents\Totals;
use Neo\Documents\MPDFDocument;

class PDFContract extends MPDFDocument {
    public const TYPE_PROPOSAL = 'proposal';
    public const TYPE_CONTRACT = 'contract';

    protected string $documentType;

    protected Customer $customer;
    protected Order $order;

    protected string $header_view = "documents.contract.header";
    protected string $footer_view = "documents.contract.footer";

    public function __construct() {
        parent::__construct([
                                "margin_bottom"    => 15,
                                "packTableData"    => true,
                                "use_kwt"          => true,
                                "setAutoTopMargin" => "pad",
                            ]);

        CarbonInterval::setCascadeFactors([
                                              'minute' => [60, 'seconds'],
                                              'hour'   => [60, 'minutes'],
                                              'day'    => [8, 'hours'],
                                              'week'   => [5, 'days'],
                                              'month'  => [999999, 'weeks'],
                                          ]);

        // Register our components

        Blade::componentNamespace("Neo\\Documents\\Contract\\PDFComponents", "contract");
        // Some very large contracts exceeds the PCRE backtrack limit. So we increase it to prevent crash
        ini_set("pcre.backtrack_limit", "5000000");
    }

    public static function makeContract($data): MPDFDocument {
        $document               = parent::make($data);
        $document->documentType = self::TYPE_CONTRACT;


        return $document;
    }

    public static function makeProposal($data): MPDFDocument {
        $document               = parent::make($data);
        $document->documentType = self::TYPE_PROPOSAL;

        return $document;
    }

    public function ingest($data): bool {
        [$this->customer, $this->order] = ContractImporter::parse($data);

        return true;
    }

    public function build(): bool {
        // Import the stylesheet
        $this->mpdf->WriteHTML(File::get(resource_path('documents/stylesheets/contract.css')), HTMLParserMode::HEADER_CSS);

        // Build the document

        // Contracts have an additional first page
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
            "order"    => $this->order,
        ]);

        $this->mpdf->WriteHTML((new GeneralConditions())->render()->render());
    }

    private function makeCampaignSummary(): void {
        $this->setLayout(__("contract.campaign-summary-title"), "legal", [
            "customer" => $this->customer,
            "order"    => $this->order,
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
        $buaOrders = $this->order->getBuaOrders();

        if ($buaOrders->isNotEmpty()) {
            $this->mpdf->WriteHTML(view("documents.contract.campaign-summary.orders-category", [
                "category" => "bua",
                "orders"   => $buaOrders,
                "order"    => $this->order,
            ])->render());
        }

        // Adserver products summary
        $adserverLines = $this->order->getAdServerLines();

        if ($adserverLines->isNotEmpty()) {
            $this->mpdf->WriteHTML(view("documents.contract.campaign-summary.adserver-products", [
                "lines" => $adserverLines,
                "order" => $this->order,
            ])->render());
        }

        // Audience extension strategy summary
        $audienceExtensionLines = $this->order->getAudienceExtensionLines();

        if ($audienceExtensionLines->isNotEmpty()) {
            $this->mpdf->WriteHTML(view("documents.contract.campaign-summary.audience-extension", [
                "lines" => $audienceExtensionLines,
                "order" => $this->order,
            ])->render());
        }

        $this->mpdf->WriteHTML((new Totals($this->order, $this->order->orderLines, "full", $this->order->productionLines))->render());
    }

    private function makeCampaignDetails(): void {
        $this->setLayout(__("contract.campaign-details-title"), [355, 355], [
            "customer" => $this->customer,
            "order"    => $this->order,
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
            "order"    => $this->order,
        ]);

        $this->mpdf->SetMargins(15, 15, 40);
        $this->renderDetailedSummary(false);
    }

    private function renderDetailedSummary(bool $renderDisclaimers) {
        $campaignDetailedSummary = new DetailedSummary($this->order, $this->order->orderLines, $this->order->productionLines, $renderDisclaimers);
        $this->mpdf->WriteHTML($campaignDetailedSummary->render()->render());
    }
}
