<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Attachment.php
 */

namespace Neo\Modules\Properties\Services\Odoo\Models;

/**
 * @property string  $name
 * @property string  $description
 * @property string  $res_model Slug of the model the attachment is associated with
 * @property integer $res_id    Id of the model the attachment is associated with
 * @property string  $type      Upload type 'URL' or 'binary'
 * @property string  $url       File url, if type is 'url'
 * @property string  $datas     File content, if type is 'binary'
 */
class Attachment extends OdooModel {
    public static string $slug = "ir.attachment";

    protected static array $filters = [];
}
