<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrackUserActivityMiddleware.php
 */

namespace Neo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Neo\Models\Actor;

class TrackUserActivityMiddleware {
	public function handle(Request $request, Closure $next) {
		/** @var Actor|null $user */
		if ($user = Auth::user()) {
			$t                = $user->timestamps;
			$user->timestamps = false;
			$user->touchQuietly("last_activity_at");
			$user->timestamps = $t;
		}
		return $next($request);
	}
}
