<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FormatsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\Formats\CloneFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\DestroyFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\ListFormatsByIdsRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\ListFormatsRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\ShowFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\StoreFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\UpdateFormatRequest;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Layout;
use Neo\Modules\Broadcast\Models\LoopConfiguration;

class FormatsController extends Controller {
	/**
	 * Depending on the user capabilities, we may return all formats or a subset of the available formats
	 *
	 * @param ListFormatsRequest $request
	 *
	 * @return Response
	 */
	public function index(ListFormatsRequest $request): Response {
		return new Response(Format::query()->orderBy("name")->get()->loadPublicRelations());
	}

	public function byIds(ListFormatsByIdsRequest $request): Response {
		$formats = Format::query()->findMany($request->input("ids"));
		return new Response($formats->loadPublicRelations());
	}

	public function store(StoreFormatRequest $request): Response {
		$format                 = new Format();
		$format->network_id     = $request->input("network_id");
		$format->name           = $request->input("name");
		$format->content_length = $request->input("content_length");
		$format->save();

		$format->broadcast_tags()->sync($request->input("tags"));

		return new Response($format, 201);
	}

	public function clone(CloneFormatRequest $request, Format $format): Response {
		$clone                 = new Format();
		$clone->network_id     = $request->input("network_id");
		$clone->name           = $request->input("name");
		$clone->content_length = $format->content_length;
		$clone->save();

		$clone->display_types()->sync($format->display_types);
		$clone->layouts()
		      ->sync($format->layouts->mapWithKeys(fn(Layout $layout) => [$layout->getKey() => ["is_fullscreen" => $layout->settings->is_fullscreen]]));
		$clone->broadcast_tags()->sync($format->broadcast_tags);

		foreach ($format->loop_configurations as $loop_configuration) {
			$clonedLoop = new LoopConfiguration($loop_configuration->getAttributes());
			unset($clonedLoop->id);
			$clonedLoop->save();

			$clone->loop_configurations()->attach($clonedLoop);
		}

		return new Response($clone);
	}

	/**
	 * @param ShowFormatRequest $request
	 * @param Format            $format
	 *
	 * @return Response
	 */
	public function show(ShowFormatRequest $request, Format $format): Response {
		return new Response($format->loadPublicRelations());
	}

	/**
	 * @param UpdateFormatRequest $request
	 * @param Format              $format
	 *
	 * @return Response
	 */
	public function update(UpdateFormatRequest $request, Format $format): Response {
		$format->name           = $request->input("name");
		$format->slug           = $request->input("slug", "");
		$format->content_length = $request->input("content_length");

		// Validate the given main layout is actually attached to the format
		$mainLayoutId = $request->input("main_layout_id");
		if ($mainLayoutId !== null) {
			$mainLayoutId = $format->layouts()->where("id", "=", $mainLayoutId)->exists() ? $mainLayoutId : null;
		}

		$format->main_layout_id = $mainLayoutId;
		$format->save();

		$format->broadcast_tags()->sync($request->input("tags"));

		// Detach loop configuration that don't match the format anymore if the content length changed
		$format->loop_configurations()->detach($format->loop_configurations()
		                                              ->where("spot_length_ms", "<>", $format->content_length * 1000)
		                                              ->pluck("id"));

		return new Response($format->loadPublicRelations());
	}

	/**
	 * @param DestroyFormatRequest $request
	 * @param Format               $format
	 *
	 * @return Response
	 */
	public function destroy(DestroyFormatRequest $request, Format $format): Response {
		$format->delete();

		return new Response($format);
	}
}
