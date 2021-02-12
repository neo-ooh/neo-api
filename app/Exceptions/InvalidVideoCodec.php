<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - InvalidVideoCodec.php
 */

namespace Neo\Exceptions;

class InvalidVideoCodec extends BaseException {
    protected $code = "creative.bad-video-codec";
    protected $message = "Creative video codec is not supported";

//    protected int $status = 422;
}
