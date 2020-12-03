<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Exceptions;

use Exception;
use Illuminate\Http\Response;

abstract class BaseException extends Exception
{
    protected int $status = 422;

    public function asResponse(): ?Response
    {
        return new Response([
            "code" => $this->code,
            "message" => $this->message,
        ], $this->status);
    }
}
