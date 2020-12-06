<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - EditorTemplate.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;

class EditorTemplate extends Model {


    protected $table = "editor_templates";

    protected $casts = [
        "parameters" => 'array',
    ];

    protected $fillable = [
        "slug",
        "parameters",
    ];
}

