<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContentIsNotEditableException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class ContentIsNotEditableException extends BaseException {
    public function __construct() {
        parent::__construct("Content is locked and cannot be changed. This also applies to the content's creatives.", "contents.locked");
    }
}
