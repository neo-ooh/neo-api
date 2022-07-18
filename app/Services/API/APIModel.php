<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - APIModel.php
 */

namespace Neo\Services\API;

use BadMethodCallException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Neo\Services\API\Traits\HasAttributes;

/**
 * Class APIModel
 *
 * @package Neo\BroadSign\Models
 */
abstract class APIModel implements JsonSerializable, Arrayable {
    use HasAttributes;

    protected static string $key;
    protected static string $unwrapKey;
    protected static array $updatable;

    protected APIClientInterface $api;

    public function getKey() {
        $key = static::$key;
        return $this->$key ?? null;
    }

    final public function __construct(APIClientInterface $client, array $attributes = []) {
        $this->api        = $client;
        $this->attributes = $attributes;
    }

    /**
     * @return void
     * @throws ClientException
     */
    public function create(): void {
        $this->{static::$key} = $this->callAction("create", $this->attributes);
        $this->dirty          = false;
    }

    /**
     * @param string $action
     * @param array  $payload
     * @param array  $headers
     * @return array|mixed
     * @throws ClientException
     */
    public function callAction(string $action, array $payload = [], array $headers = []): mixed {
        if (!array_key_exists($action, static::actions())) {
            $className = static::class;
            throw new BadMethodCallException("Static method $action does not exist on model $className.");
        }

        $endpoint = static::actions()[$action];

        return $this->api->call($endpoint, $payload, $headers);
    }

    public function __call(string $methodName, array $args) {
        return $this->callAction($methodName, ...$args);
    }

    /**
     * @param string $methodName
     * @param array  $args First argument passed to the method must be a valid BroadSignClient
     * @return mixed
     */
    public static function __callStatic(string $methodName, array $args) {
        $client = array_shift($args);
        return (new static($client))->callAction($methodName, ...$args);
    }

    abstract protected static function actions(): array;

    public function save(): self {
        $properties = array_filter($this->attributes,
            static fn($key) => in_array($key, static::$updatable, true),
            ARRAY_FILTER_USE_KEY);
        $this->callAction("update", $properties);

        $this->dirty = false;

        return $this;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->toArray();
    }
}
