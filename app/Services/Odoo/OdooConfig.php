<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OdooConfig.php
 */

namespace Neo\Services\Odoo;

use Edujugon\Laradoo\Exceptions\OdooException;

class OdooConfig {
    public function __construct(
        protected string $serverUrl,
        protected string $username,
        protected string $password,
        protected string $database
    ) {
    }

    public static function fromConfig(): static {
        return new static(
            config('modules-legacy.odoo.server-url'),
            config('modules-legacy.odoo.username'),
            config('modules-legacy.odoo.password'),
            config('modules-legacy.odoo.database')
        );
    }

    /**
     * @throws OdooException
     */
    public function getClient(): OdooClient {
        return new OdooClient($this->serverUrl, $this->database, $this->username, $this->password);
    }
}
