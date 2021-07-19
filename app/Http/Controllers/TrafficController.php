<?php

namespace Neo\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Neo\Documents\NetworkTraffic\NetworkTraffic;
use Neo\Exceptions\UnknownDocumentException;
use Neo\Http\Requests\Traffic\ExportTrafficRequest;
use Neo\Models\DisplayTypePrintsFactors;
use Neo\Models\Network;
use Neo\Models\Property;
use Storage;

class TrafficController extends Controller
{
    public function export(ExportTrafficRequest $request) {
        $year = $request->input("year");

        $networks = Network::query()->whereHas("printsFactors")->with(["printsFactors"])->orderBy("name")->get()->append("properties");

        $periods = DisplayTypePrintsFactors::query()
                                           ->with("displayTypes")
                                           ->get();

        $file = new NetworkTraffic($year, $networks, $periods);

        if (!$file || !$file->build()) {
            throw new UnknownDocumentException();
        }

        return new Response($file->output(), 200, [
            $file->format()
        ]);
    }
}
