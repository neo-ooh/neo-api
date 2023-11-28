<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PlayersController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\Players\ListPlayersRequest;
use Neo\Modules\Broadcast\Http\Requests\Players\ShowPlayerRequest;
use Neo\Modules\Broadcast\Http\Requests\Players\UpdatePlayerRequest;
use Neo\Modules\Broadcast\Models\Player;

class PlayersController extends Controller {
	public function index(ListPlayersRequest $request) {
		$players = Player::query()
		                 ->when($request->has("location_id"), function (Builder $query) use ($request) {
			                 $query->where("location_id", "=", $request->input("location_id"));
		                 })
		                 ->orderBy("name")
		                 ->get();

		return new Response($players);
	}

	public function show(ShowPlayerRequest $request, Player $player) {
		return new Response($player);
	}

	public function update(UpdatePlayerRequest $request, Player $player) {
		$player->dynamics_debug = $request->input("dynamics_debug");
		$player->save();

		return new Response($player);
	}
}
