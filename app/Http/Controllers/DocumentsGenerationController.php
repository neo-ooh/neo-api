<?php

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

        $contract = null;

        switch ($request->route('document')) {
            case "contract":
                $contract = Contract::make(Storage::disk('local')->get('sale.order.11.csv'));
                break;
            default:
                throw new UnknownDocumentException();
        }

        return new Response($contract->output(), 200, ["Content-Type" => "application/pdf"]);
    }
}
