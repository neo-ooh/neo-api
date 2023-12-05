<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PlayRecord.php
 */

namespace Neo\Modules\Dynamics\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property int         $player_id
 * @property Carbon      $loaded_at
 * @property Carbon|null $played_at
 * @property Carbon|null $ended_at
 * @property Carbon      $received_at
 * @property string      $dynamic
 * @property string      $version
 * @property array       $params
 * @property array       $logs
 */
class PlayRecord extends Model {
	protected $table = "dynamics_play_records";

	public $timestamps = false;

	protected $casts = [
		"loaded_at"   => "datetime",
		"played_at"   => "datetime",
		"ended_at"    => "datetime",
		"received_at" => "datetime",
		"params"      => "array",
		"logs"        => "array",
	];
}
