<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Client.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Client
 *
 * @package Neo\Models
 *
 * @property integer    $id
 * @property string     $odoo_id
 * @property string     $name
 * @property Date       $created_at
 * @property Date       $updated_at
 *
 * @property Collection $contracts
 */
class Client extends Model {
    protected $table = "clients";

    protected $fillable = [
        "name",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function contracts(): HasMany {
        return $this->hasMany(Contract::class, "client_id", "id")->orderBy("contract_id");
    }
}
