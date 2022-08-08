<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OdooConfig.php
 */

namespace Neo\Services\Odoo;

class OdooConfig {
    public function __construct(protected string $serverUrl,
                                protected string $username,
                                protected string $password,
                                protected string $database) {
    }

    public static function fromConfig(): static {
        return new static(
            config('modules-legacy.odoo.server-url'),
            config('modules-legacy.odoo.username'),
            config('modules-legacy.odoo.password'),
            config('modules-legacy.odoo.database')
        );
    }

    public function getClient() {
        return new OdooClient($this->serverUrl, $this->database, $this->username, $this->password);
    }
}
