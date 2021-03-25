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

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Neo\Documents\Contract\Contract;
use Neo\Documents\Exceptions\UnknownGenerationException;
use Neo\Exceptions\UnknownDocumentException;

class DocumentsGenerationController extends Controller {

    /**
     * @param Request $request
     * @return Response
     * @throws UnknownDocumentException
     * @throws FileNotFoundException
     * @throws UnknownGenerationException
     */
    public function make(Request $request) {
        App::setLocale('en');

        $file = $request->file("file");

        if($file === null) {
            return new Response(["error" => "Missing file"],  400);
        }

        $contract = null;

        switch ($request->route('document')) {
            case "proposal":
                $contract = Contract::make($file->getContent());
//                $contract = Contract::make(Storage::disk('local')->get('quotation.csv'));
                break;
            default:
                throw new UnknownDocumentException();
        }

        return new Response($contract->output(), 200, ["Content-Type" => "application/pdf"]);
    }
}
