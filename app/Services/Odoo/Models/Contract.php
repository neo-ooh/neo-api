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

use Neo\Services\Odoo\OdooClient;
use Neo\Services\Odoo\OdooModel;

/**
 * @property int        $id
 * @property string     $name
 * @property string     $display_name
 * @property string     $date_order
 * @property string     $create_date
 * @property array      $user_id
 * @property array      $partner_id
 * @property array      $partner_invoice_id
 * @property array      $analytic_account_id
 * @property array      $order_line
 * @property array      $company_id
 * @property array<int> $campaign_ids
 * @property string     $access_url
 * @property string     $state
 */
class Contract extends OdooModel {
    public static string $slug = "sale.order";

    protected static array $filters = [];

    public static function findByName(OdooClient $client, string $contractName): static|null {
        return static::findBy($client, "name", $contractName)->first();
    }

    public function isDraft(): bool {
        return $this->state === 'draft';
    }

    public function isCancelled(): bool {
        return $this->state === 'cancel';
    }

    /**
     * Tell if the contract is confirmed, and its content should be taken into account for availabilities, etc.
     *
     * @return bool
     */
    public function isConfirmed(): bool {
        return !$this->isDraft() && !$this->isCancelled();
    }
}

