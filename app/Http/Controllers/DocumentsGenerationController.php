<?php

namespace Neo\Http\Controllers;

use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Neo\Documents\Contract\Contract;

class DocumentsGenerationController extends Controller {
    public function make(Request $request) {
        App::setLocale('en');

        switch ($request->route('document')) {
            case "contract":
                $contract = Contract::make(Storage::disk('local')->get('contract-dump.csv'));
                break;
        }

        return new Response($contract->output(), 200, ["Content-Type" => "application/pdf"]);
    }
}
