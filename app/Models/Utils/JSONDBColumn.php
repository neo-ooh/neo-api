<?php

namespace Neo\Models\Utils;

use Illuminate\Contracts\Database\Eloquent\Castable;
use JsonException;
use Spatie\DataTransferObject\DataTransferObject;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class JSONDBColumn extends DataTransferObject implements Castable {
    public static function castUsing(array $arguments): JSONDBColumnCast {
        return new JSONDBColumnCast(static::class);
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * @throws UnknownProperties
     * @throws JsonException
     */
    public static function fromJson(string $json): static {
        return new static(json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }
}