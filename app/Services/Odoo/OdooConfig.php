<?php

namespace Neo\Services\Odoo;

use Neo\Services\API\Odoo\Client;

class OdooConfig {
    public function __construct(protected string $serverUrl,
                                protected string $username,
                                protected string $password,
                                protected string $database) {
    }

    public static function fromConfig(): static {
        return new static(
            config('modules.odoo.server-url'),
            config('modules.odoo.username'),
            config('modules.odoo.password'),
            config('modules.odoo.database')
        );
    }

    public function getClient() {
        return new Client($this->serverUrl, $this->database, $this->username, $this->password);
    }
}