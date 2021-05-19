<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignModel.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use BadMethodCallException;
use Facade\FlareClient\Http\Exceptions\BadResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\JsonEncodingException;
use JsonException;
use JsonSerializable;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;

/**
 * Class BroadSignModel
 *
 * @package Neo\BroadSign\Models
 */
abstract class BroadSignModel implements JsonSerializable, Arrayable {
    protected static string $unwrapKey;
    protected static array $updatable;
    protected array $attributes;
    protected bool $dirty = false;

    protected BroadsignClient $api;

    final public function __construct(BroadSignClient $client, array $attributes = []) {
        $this->api        = $client;
        $this->attributes = $attributes;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name) {
        // Check if a method with the specified name exists
        if (method_exists($this, $name)) {
            // Yes call it and return
            return $this->{$name}();
        }

        // Return the attribute with the provided name
        return $this->attributes[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @param        $value
     */
    public function __set(string $name, $value) {
        $this->attributes[$name] = $value;
        $this->dirty             = true;
    }

    /**
     */
    public function __toString(): string {
        try {
            /** @var string $serialized */
            $serialized = json_encode($this->attributes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        } catch (JsonException $e) {
            return $e->getMessage();
        }

        return $serialized;
    }

    public function create(): void {
        $this->id    = $this->callAction("create", $this->attributes);
        $this->dirty = false;
    }

    /**
     * @param string $action
     * @param array  $payload
     * @param array  $headers
     * @return array|mixed
     * @throws BadResponse
     */
    public function callAction(string $action, $payload = [], $headers = []) {
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

    abstract protected static function actions();

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

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array {
        return $this->attributes;
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     *
     * @return string
     *
     * @throws JsonException
     */
    public function toJson($options = 0): string {
        $json = json_encode($this->attributes, JSON_THROW_ON_ERROR | $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }
}
