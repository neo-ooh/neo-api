<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DocumentsGenerationController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Neo\Documents\Contract\Contract;
use Neo\Exceptions\UnknownDocumentException;
use Neo\Http\Requests\Documents\MakeDocumentRequest;

class DocumentsGenerationController extends Controller {

    /**
     * @param Request $request
     * @return Response
     * @throws UnknownDocumentException
     */
    public function make(MakeDocumentRequest $request): Response {
        App::setLocale('en');

        $file = $request->file("file");

        if ($file === null) {
            return new Response(["error" => "Missing file"], 400);
        }

        $document = null;

        switch ($request->route('document')) {
            case "contract":
                $document = Contract::makeContract($file->getContent());
                break;
            case "proposal":
                $document = Contract::makeProposal($file->getContent());
                break;
            default:
                throw new UnknownDocumentException();
        }

        if (!$document->build()) {
            throw new UnknownDocumentException();
        }

        return new Response($document->output(), 200, ["Content-Type" => "application/pdf"]);
    }
}
