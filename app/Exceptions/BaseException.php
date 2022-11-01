<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BaseException.php
 */

namespace Neo\Exceptions;

use Exception;
use Illuminate\Http\Response;
use JsonException;
use Throwable;

class BaseException extends Exception {
    public function __construct(string $message = "", protected string $errorCode = "error.unknown", protected int $status = 422, ?Throwable $previous = null) {
        parent::__construct($message, $this->status, $previous);
    }

    public function toArray(): array {
        return [
            "code"    => $this->errorCode,
            "message" => $this->message,
        ];
    }

    public function asResponse(): ?Response {
        return new Response($this->toArray(), $this->status);
    }

    /**
     * @return int
     */
    public function getStatus(): int {
        return $this->status;
    }

    /**
     * @throws JsonException
     */
    public function __toString(): string {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
