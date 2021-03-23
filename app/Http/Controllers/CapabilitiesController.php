<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CapabilitiesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\Capabilities\ListCapabilitiesRequest;
use Neo\Http\Requests\Capabilities\UpdateCapabilityRequest;
use Neo\Models\Capability;

class CapabilitiesController extends Controller {
    /**
     * List all capabilities scoped to the one accessible by the current user.
     * Passing `standalone` as a query parameter makes this route returns only standalone capabilities
     *
     * @param ListCapabilitiesRequest $request
     *
     * @return Response
     */
    public function index (ListCapabilitiesRequest $request): Response {
        // A user can only see the capabilities directly or indirectly associated with it
        $capabilities = Auth::user()->capabilities;

        if ($request->has("standalone")) {
            $capabilities = $capabilities->filter(fn ($c) => $c->standalone === true);
        }

        return new Response($capabilities->values());
    }

    /**
     * Update the standalone status of the specified capability.
     *
     * @param UpdateCapabilityRequest $request
     * @param Capability              $capability
     *
     * @return Response
     */
    public function update (UpdateCapabilityRequest $request, Capability $capability): Response {
        $capability->standalone = $request->validated()['standalone'];
        $capability->save();

        return new Response($capability);
    }
}
