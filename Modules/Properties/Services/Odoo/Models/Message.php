<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Message.php
 */

namespace Neo\Modules\Properties\Services\Odoo\Models;

/**
 * @property int $id
 */
class Message extends OdooModel {
    public static string $slug = "mail.message";

    protected static array $filters = [];
}
