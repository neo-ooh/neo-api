<?php /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */ /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ActorsRolesController.php
 */ /** @noinspection PhpUnusedParameterInspection */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ActorsRoles\ListActorRolesRequest;
use Neo\Http\Requests\ActorsRoles\SyncActorRolesRequest;
use Neo\Models\Actor;

class ActorsRolesController extends Controller {
    public function index (ListActorRolesRequest $request, Actor $actor): Response {
        return new Response($actor->roles);
    }

    public function sync (SyncActorRolesRequest $request, Actor $actor): Response {
        $actor->syncRoles($request->validated()['roles']);

        return new Response(["roles" => $actor->roles, "own_roles" => $actor->own_roles]);
    }
}
