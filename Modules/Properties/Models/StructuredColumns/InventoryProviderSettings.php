<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryProviderSettings.php
 */

namespace Neo\Modules\Properties\Models\StructuredColumns;

use Neo\Models\Utils\JSONDBColumn;

class InventoryProviderSettings extends JSONDBColumn {
    public function __construct(
        /**
         * @var int|null Inventory provider API URL
         */
        public string|null $api_url = null,

        /**
         * @var string|null API Auth key
         */
        public string|null $api_key = null,

        /**
         * @var string|null Odoo API Auth username
         */
        public string|null $api_username = null,

        /**
         * @var string|null Odoo database
         */
        public string|null $database = null,
    ) {

    }

    public function excludeProperties(): array {
        return [
            "api_key" => true,
        ];
    }
}
