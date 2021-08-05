<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractNetworkData.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Neo\Models\Traits\HasCompositePrimaryKey;


/**
 * @property int $contract_id
 * @property string $network
 * @property boolean $has_guaranteed_reservations
 * @property int $guaranteed_impressions
 * @property int $guaranteed_media_value
 * @property int $guaranteed_net_investment
 * @property boolean $has_bonus_reservations
 * @property int $bonus_impressions
 * @property int $bonus_media_value
 * @property boolean $has_bua_reservations
 * @property int $bua_impressions
 * @property int $bua_media_value
 */
class ContractNetworkData extends Model {
    use HasCompositePrimaryKey;

    protected $table = "contracts_networks_data";

    public $incrementing = false;

    protected $primaryKey = ["contract_id", "network"];

    public $timestamps = false;

    protected $fillable = [
        "contract_id",
        "network"
    ];

    protected $casts = [
        "has_guaranteed_reservations" => "boolean",
        "has_bonus_reservations" => "boolean",
        "has_bua_reservations" => "boolean",
    ];
}