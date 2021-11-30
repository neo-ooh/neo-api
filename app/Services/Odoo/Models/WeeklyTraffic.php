<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Contract.php
 */

namespace Neo\Services\Odoo\Models;

use Illuminate\Support\Collection;
use Neo\Services\API\Odoo\Client;
use Neo\Services\API\Odoo\Model;

/**
 * @property int    $id
 * @property int    $week_number 0-indexed
 * @property int    $traffic     Daily traffic
 * @property array  $partner_id
 * @property string $display_name
 */
class WeeklyTraffic extends Model {
    public static string $slug = "weekly.traffic";

    protected static array $filters = [];

    /**
     * @param Client $client
     * @param int    $propertyId
     * @return Collection<static>
     */
    public static function forProperty(Client $client, int $propertyId): Collection {
        return static::findBy($client, "partner_id", $propertyId);
    }
}

