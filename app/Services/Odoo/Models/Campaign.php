<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Campaign.php
 */

namespace Neo\Services\Odoo\Models;

use Neo\Services\Odoo\OdooClient;
use Neo\Services\Odoo\OdooModel;

/**
 * @property int    $id
 * @property array  $order_id
 * @property string $state
 * @property string $date_start
 * @property string $date_end
 * @property int    $sequence
 * @property array  $display_name
 * @property array  $create_uid
 * @property string $create_date
 * @property array  $write_uid
 * @property array  $write_date
 */
class Campaign extends OdooModel {
    public static string $slug = "sale.campaign";

    protected static array $filters = [];

    public static function findByName(OdooClient $client, string $contractName): static {
        return static::findBy($client, "name", $contractName)->first();
    }
}

