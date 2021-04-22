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
use Neo\Documents\POP\POP;
use Neo\Exceptions\UnknownDocumentException;
use Neo\Http\Requests\Documents\MakeDocumentRequest;

class DocumentsGenerationController extends Controller {

    /**
     * @param Request $request
     * @return Response
     * @throws UnknownDocumentException
     */
    public function make(MakeDocumentRequest $request): Response {
        // Input can either be done using a file or a json object named data
        $file = $request->file("file");
        $data = $request->input("data");

        switch ($request->route('document')) {
            case "contract":
                if ($file === null) {
                    return new Response(["error" => "Missing file"], 400);
                }

                $document = Contract::makeContract($file->getContent());
                break;
            case "proposal":
                if ($file === null) {
                    return new Response(["error" => "Missing file"], 400);
                }

                $document = Contract::makeProposal($file->getContent());
                break;
            case "pop":
                if ($data === null) {
                    return new Response(["error" => "Missing data"], 400);
                }

                $document = POP::make($data);
                break;
            default:
                throw new UnknownDocumentException();
        }

        if (!$document || !$document->build()) {
            throw new UnknownDocumentException();
        }

        return new Response($document->output(), 200, [
            "Content-Type" => "application/pdf",
            "access-control-allow-origin"=> "localhost:3000"
        ]);
    }
}
