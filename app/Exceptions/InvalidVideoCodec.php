<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidVideoCodec.php
 */

namespace Neo\Exceptions;

class InvalidVideoCodec extends BaseException {
    public function __construct(string $codec) {
        parent::__construct("Unsupported video codec `$codec`", "creative.bad-video-codec");
    }
}
