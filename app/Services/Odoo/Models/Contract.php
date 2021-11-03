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

use Neo\Services\API\Odoo\Client;
use Neo\Services\API\Odoo\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $date_order
 * @property string $create_date
 * @property array $user_id
 * @property array $partner_id
 * @property array $partner_invoice_id
 * @property array $analytic_account_id
 * @property array $order_line
 * @property array $company_id
 * @property array<int> $campaign_ids
 * @property string $access_url
 * @property string $state
 */
class Contract extends Model {
    public static string $slug = "sale.order";

    protected static array $filters = [];

    public static function findByName(Client $client, string $contractName): static {
        return static::findBy($client, "name", $contractName)->first();
    }
}

