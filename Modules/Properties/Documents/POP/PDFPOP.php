<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PDFPOP.php
 */

namespace Neo\Modules\Properties\Documents\POP;

use Illuminate\Support\Facades\App;
use Mpdf\MpdfException;
use Neo\Documents\MPDFDocument;
use Neo\Modules\Properties\Documents\POP\components\POPBuyTypeSummary;
use Neo\Modules\Properties\Documents\POP\components\POPCoverPage;
use Neo\Modules\Properties\Documents\POP\components\POPFlightDetails;
use Neo\Modules\Properties\Documents\POP\components\POPFlightSummary;
use Neo\Modules\Properties\Documents\POP\components\POPFooter;
use Neo\Modules\Properties\Documents\POP\components\POPHeader;
use Neo\Modules\Properties\Documents\POP\components\POPSummaryTotals;

class PDFPOP extends MPDFDocument {
	protected POPRequest $request;

	public function __construct() {
		parent::__construct([
			                    "bleed_margin"     => 0,
			                    "packTableData"    => false,
			                    "use_kwt"          => true,
			                    "setAutoTopMargin" => false,
			                    "simpleTables"     => false,
			                    "useSubstitutions" => false,
		                    ]);
	}

	protected function ingest($data): bool {
		$this->request = $data;

		return true;
	}

	/**
	 * @throws MpdfException
	 */
	public function build(): bool {
		App::setLocale($this->request->locale);

		// Start by loading our css and header/footer
		$this->loadCSSFile(module_path('Properties', 'Documents/POP/pop.css'));
		$this->registerHeader((new POPHeader(__("pop.title"), $this->request))->render());
		$this->registerFooter((new POPFooter())->render());

		$this->addPage("legal", "cover-page");

		// Add the cover page
		$this->appendHTML((new POPCoverPage())->render());

		// Print the summary
		$this->addPage("legal", "main");

		if ($this->request->summary_breakdown === 'flights') {
			// Print the summary of each flight
			foreach ($this->request->flights as $flight) {
				$this->appendHTML((new POPFlightSummary($flight))->render());
			}
		} else {
			$flightsByType = $this->request->flights->toCollection()->groupBy("flight_type.value");

			foreach ($flightsByType as $flights) {
				$this->appendHTML((new POPBuyTypeSummary($flights))->render());
			}
		}

		// Print the summary totals
		$this->appendHTML((new POPSummaryTotals($this->request))->render());

		// Print the details of each flight
		foreach ($this->request->flights as $flight) {
			(new POPFlightDetails($flight, $this))->render();
		}

		return true;
	}

	public function getName(): string {
		return $this->request->contract_number . " - POP.pdf";
	}
}
