<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DocumentsGenerationController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\UnableToProcessCsv;
use Neo\Documents\Contract\PDFContract;
use Neo\Documents\Contract\XLSXProposal;
use Neo\Documents\Exceptions\UnknownGenerationException;
use Neo\Documents\MobileCampaign\MobileCampaign;
use Neo\Documents\PlannerExport\PlannerExport;
use Neo\Documents\Traffic\Traffic;
use Neo\Exceptions\UnknownDocumentException;
use Neo\Http\Requests\Documents\MakeDocumentRequest;

class DocumentsGenerationController extends Controller {

	/**
	 * @param MakeDocumentRequest $request
	 * @return Response
	 * @throws UnknownDocumentException
	 * @throws UnknownGenerationException
	 * @throws Exception
	 * @throws InvalidArgument
	 * @throws UnableToProcessCsv
	 */
	public function make(MakeDocumentRequest $request): Response {
		// Input can either be done using a file or a json object named data
		/** @var UploadedFile|null $file */
		$file = $request->file("file");
		$data = $request->input("data");

		if ($request->has("locale")) {
			Lang::setLocale($request->input("locale"));
		}

		switch ($request->route('document')) {
			case "contract":
				if ($file === null) {
					return new Response(["error" => "Missing file"], 400);
				}

				Storage::disk("public")->put("/tmp/{$file->getClientOriginalName()}", $file->getContent());

				$document = PDFContract::makeContract($file->getContent());
				break;
			case "proposal":
				if ($file === null) {
					return new Response(["error" => "Missing file"], 400);
				}

				// We need to do a first parse of the given file as to get the requested output format
				$reader = Reader::createFromString($file->getContent());
				$reader->setDelimiter(',');
				$reader->setHeaderOffset(0);

				// Get all records in the file
				$line   = $reader->fetchOne();
				$format = ($line["export_in_excel"] ?? null) === "True" ? 'xlsx' : 'pdf';
				unset($reader);

				if ($format === 'xlsx') {
					$document = XLSXProposal::make($file->getContent());
				} else {
					$document = PDFContract::makeProposal($file->getContent());
				}
				break;
			case "traffic":
				if ($data === null) {
					return new Response(["error" => "Missing data"], 400);
				}

				$document = Traffic::make($data);
				break;
			case "planner-export":
				if ($data === null) {
					return new Response(["error" => "Missing data"], 400);
				}

				$document = PlannerExport::make($data);
				break;
			case "mobile-campaign":
				if ($data === null) {
					return new Response(["error" => "Missing data"], 400);
				}

				$document = MobileCampaign::make($data);
				break;
			default:
				throw new UnknownDocumentException();
		}

		if (!$document->build()) {
			throw new UnknownDocumentException();
		}

		return new Response($document->output(), 200, [
			"Content-Type" => $document->format()->value,
		]);
	}
}
