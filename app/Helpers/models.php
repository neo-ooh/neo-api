<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - models.php
 */

use Illuminate\Database\Eloquent\Model;

if (!function_exists("model_table")) {
    /**
     * Return the table name of the passed model
     *
     * @param class-string<Model> $model
     * @return string
     */
    function model_table(string $model): string {
        return (new $model())->getTable();
    }
}
