<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Property.php
 */

namespace Neo\Services\Odoo\Models;

use Neo\Services\Odoo\OdooModel;

/**
 * @property int        $id
 * @property string     $name
 * @property string     $display_name
 * @property bool       $date
 * @property bool       $title
 * @property bool       $parent_id
 * @property bool       $parent_name
 * @property string     $lang
 * @property string     $street
 * @property string     $street2
 * @property string     $zip
 * @property array      $city
 * @property array      $state_id
 * @property string     $country_id
 * @property double     $partner_latitude
 * @property double     $partner_longitude
 * @property string     $email
 * @property string     $phone
 *
 * @property array<int> $rental_product_ids
 *
 * @property Province   $province
 */
class Property extends OdooModel {
    public static string $slug = "res.partner";

    protected static array $filters = [
        ["is_company", "=", true],
        ["center_type", "<>", false],
        ["center_type", "<>", "group"],
    ];

    public function province(): Province {
        return Province::get($this->client, $this->state_id[0]);
    }
}
