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
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use Mpdf\HTMLParserMode;
use Mpdf\MpdfException;
use Neo\Documents\Contract\PDFComponents\ContractFirstPage;
use Neo\Documents\Contract\PDFComponents\DetailedOrdersCategory;
use Neo\Documents\Contract\PDFComponents\DetailedOrdersTable;
use Neo\Documents\Contract\PDFComponents\DetailedSummary;
use Neo\Documents\Contract\PDFComponents\GeneralConditions;
use Neo\Documents\Contract\PDFComponents\Totals;
use Neo\Documents\Exceptions\MissingColumnException;
use Neo\Documents\Exceptions\UnknownGenerationException;
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

	/**
	 * @throws UnknownGenerationException
	 */
	public static function makeContract($data): MPDFDocument {
		$document               = parent::make($data);
		$document->documentType = self::TYPE_CONTRACT;


		return $document;
	}

	/**
	 * @throws UnknownGenerationException
	 */
	public static function makeProposal($data): MPDFDocument {
		$document               = parent::make($data);
		$document->documentType = self::TYPE_PROPOSAL;

		return $document;
	}

	/**
	 * @throws InvalidArgument
	 * @throws MissingColumnException
	 * @throws Exception
	 */
	public function ingest($data): bool {
		[$this->customer, $this->order] = ContractImporter::parse($data);

		return true;
	}

	/**
	 * @throws MpdfException
	 */
	public function build(): bool {
		// Import the stylesheet
		$this->mpdf->WriteHTML(File::get(resource_path('documents/stylesheets/contract.css')), HTMLParserMode::HEADER_CSS);

		// Build the document
		$this->setHeader("");
		$this->setFooter();
		$this->addPage("legal", "main");

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

	public function setHeader(string $title): void {
		$this->registerHeader(view("documents.contract.header", [
			"title"    => $title,
			"order"    => $this->order,
			"customer" => $this->customer,
		])->render());
	}

	public function setFooter(): void {
		$this->registerFooter(view("documents.contract.footer")->render());
	}

	/**
	 * @throws MpdfException
	 */
	private function makeContractFirstPage(): void {
		$this->mpdf->WriteHTML((new ContractFirstPage($this->order, $this->customer))->render()->render());
	}

	/**
	 * @throws MpdfException
	 */
	private function makeGeneralConditions(): void {
		$this->addPage("legal", "main");

		$this->mpdf->WriteHTML((new GeneralConditions())->render()->render());
	}

	/**
	 * @throws MpdfException
	 */
	private function makeCampaignSummary(): void {
		$this->setHeader(__("contract.campaign-summary-title"));
		$this->addPage("legal", "main");

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

		// Bua summary
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

	/**
	 * @throws MpdfException
	 */
	private function makeCampaignDetails(): void {
		$this->setHeader(__("contract.campaign-details-title"));

		foreach (["purchase", "bonus", "bua"] as $orderType) {
			if ($orderType === 'bonus' && $this->order->getBonusOrders()->isNotEmpty()) {
				$this->addPage([355, 355], "main");
			} else if ($orderType === 'bua' && $this->order->getBuaOrders()->isNotEmpty()) {
				$this->addPage([355, 355], "main");
			} else if ($orderType === 'purchase' && $this->order->getGuaranteedOrders()->isNotEmpty()) {
				$this->addPage([355, 355], "main");
			}

			foreach (["shopping", "otg", "fitness"] as $network) {
				$this->mpdf->WriteHTML(new DetailedOrdersTable($orderType, $this->order, $network));
			}

			$orders = new DetailedOrdersCategory($orderType, $this->order, $this->order->orderLines);
			$this->mpdf->WriteHTML($orders);
		}

		$this->renderDetailedSummary(true);
	}

	/**
	 * @throws MpdfException
	 */
	private function renderProductionDocument(): void {
		$this->setHeader(__("contract.production-details"));

		$this->mpdf->SetMargins(15, 15, 40);
		$this->renderDetailedSummary(false);
	}

	/**
	 * @throws MpdfException
	 */
	private function renderDetailedSummary(bool $renderDisclaimers): void {
		$this->addPage([355, 355], "main");
		$campaignDetailedSummary = new DetailedSummary($this->order, $this->order->orderLines, $this->order->productionLines, $renderDisclaimers);
		$this->mpdf->WriteHTML($campaignDetailedSummary->render()->render());
	}
}
