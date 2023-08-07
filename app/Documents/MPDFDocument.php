<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MPDFDocument.php
 */

namespace Neo\Documents;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;

abstract class MPDFDocument extends Document {
	protected array $settings;

	protected Mpdf $mpdf;

	protected function __construct(array $mpdfConfiguration) {
		// before doing anything, update the max execution time to prevent timeout
		set_time_limit(240);

		$this->mpdf = new Mpdf(array_merge(
			                         [
				                         "fontDir"      => [resource_path('fonts/')],
				                         "fontdata"     => [
					                         "poppins-bold"        => [
						                         "R"  => "Poppins-SemiBold.ttf",
						                         "I"  => "Poppins-SemiBoldItalic.ttf",
						                         "B"  => "Poppins-Bold.ttf",
						                         "BI" => "Poppins-BoldItalic.ttf",
					                         ],
					                         "poppins-regular"     => [
						                         "R"  => "Poppins-Regular.ttf",
						                         "I"  => "Poppins-Italic.ttf",
						                         "B"  => "Poppins-Medium.ttf",
						                         "BI" => "Poppins-MediumItalic.ttf",
					                         ],
					                         "poppins-light"       => [
						                         "R" => "Poppins-Light.ttf",
						                         "I" => "Poppins-Italic.ttf",
					                         ],
					                         "poppins-extra-light" => [
						                         "R" => "Poppins-ExtraLight.ttf",
						                         "I" => "Poppins-ExtraItalic.ttf",
					                         ],
				                         ],
				                         "default_font" => "poppins-regular",
			                         ]
			                       , $mpdfConfiguration
		                       ));

		// Some very large contracts exceeds the PCRE backtrack limit. So we increase it to prevent crash
		ini_set("pcre.backtrack_limit", "5000000");
	}


	/*
	|--------------------------------------------------------------------------
	| Document building
	|--------------------------------------------------------------------------
	*/

	protected string $header_view = "";
	protected string $footer_view = "";

	protected function registerHeader(string $html): void {
		$this->mpdf->DefHTMLHeaderByName("default_header", $html);
	}

	protected function registerFooter(string $html): void {
		$this->mpdf->DefHTMLFooterByName("default_footer", $html);
	}

	/**
	 * Adds a new page to the document
	 *
	 * @param string|number[] $dimensions A page type or page dimensions in mm ([w, h])
	 * @param string          $pageName   Name of the @page selector
	 * @return void
	 */
	public function addPage(mixed $dimensions, string $pageName = ""): void {
		$this->mpdf->AddPageByArray([
			                            "orientation"  => "P",
			                            "sheet-size"   => $dimensions,
			                            "pageselector" => $pageName,
		                            ]);
	}

	/**
	 * Define a new layout for future pages, and add a new page
	 *
	 * @param string          $title
	 * @param string|number[] $dimensions   A page type or page dimensions in mm ([w, h])
	 * @param array           $context      Values to pass to the header and footer when rendering
	 * @param string          $pageselector Name of the @page selector
	 * @return void
	 */
	protected function setLayout(string $title, mixed $dimensions, array $context = [], string $pageselector = ""): void {
		$this->registerHeader(view($this->header_view, array_merge(["title" => $title], $context))->render());

		$this->addPage($dimensions, $pageselector);

		$this->registerFooter(view($this->footer_view, array_merge(["width" => is_array($dimensions) ? $dimensions[0] - 5 : 210], $context))->render());
	}

	/**
	 * Takes a path to a css file and loads it. HTML appended after wil be able to use the classes defined in the file.
	 *
	 * @param string $filePath
	 * @return void
	 * @throws MpdfException
	 */
	public function loadCSSFile(string $filePath) {
		$this->mpdf->WriteHTML(File::get($filePath), HTMLParserMode::HEADER_CSS);
	}

	public function appendHTML(string $html) {
		$this->mpdf->WriteHTML($html);
	}


	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	public function format(): DocumentFormat {
		return DocumentFormat::PDF;
	}

	public function output(): null|string {
		return $this->mpdf->Output($this->getName(), Destination::STRING_RETURN);
	}

	/**
	 * @param int $code
	 * @return Response
	 */
	public function asResponse(int $code = 200): Response {
		return new Response($this->output(), $code, [
			"Content-Type" => $this->format()->value,
		]);
	}
}
