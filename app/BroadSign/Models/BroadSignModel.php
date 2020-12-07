<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - BroadSignModel.php
 */

namespace Neo\BroadSign\Models;

use BadMethodCallException;
use Facade\FlareClient\Http\Exceptions\BadResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use JsonSerializable;
use Neo\BroadSign\Endpoint;
use RuntimeException;

/**
 * Class BroadSignModel
 *
 * @package Neo\BroadSign\Models
 */
abstract class BroadSignModel implements JsonSerializable, Arrayable {
    protected static string $unwrapKey;
    protected static array  $updatable;
    protected array         $attributes;
    protected bool $dirty = false;

    final public function __construct (array $attributes = []) {
        $this->attributes = $attributes;
    }

    /**
     * @param array $responseBody
     *
     * @return static
     */
    protected static function asSelf (array $responseBody): self {
        return new static($responseBody[0]);
    }

    /**
     * @param array $responseBody
     *
     * @return Collection
     */
    protected static function asMultipleSelf (array $responseBody): Collection {
        $elements = [];

        foreach ($responseBody as $rawEl) {
            $elements[] = new static($rawEl);
        }

        return collect($elements);
    }

    protected static function asID (array $responseBody): int {
        return $responseBody[0]["id"];
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get (string $name) {
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
    public function __isset (string $name): bool {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @param        $value
     */
    public function __set (string $name, $value) {
        $this->attributes[$name] = $value;
        $this->dirty = true;
    }

    /**
     */
    public function __toString (): string {
        try {
            /** @var string $serialized */
            $serialized = json_encode($this->attributes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        } catch(JsonException $e) {
            return $e->getMessage();
        }

        return $serialized;
    }

    public function create (): void {
        $this->id = $this->callAction("create", $this->attributes);
        $this->dirty = false;
    }

    /**
     * @param string $action
     * @param array  $body
     *
     * @return array|mixed
     * @return array|mixed
     * @throws BadResponse
     */
    public function callAction (string $action, array $body): array {
        return static::__callStatic($action, [ $body ]);
    }

    /**
     * @param string $actionName
     * @param        $args
     *
     * @return mixed
     * @throws BadResponse
     */
    public static function __callStatic (string $actionName, $args) {
        if (!array_key_exists($actionName, static::actions())) {
            $className = static::class;
            throw new BadMethodCallException("Static method {$actionName} does not exist on model {$className}.");
        }

        /** @var Endpoint $action */
        $endpoint = static::actions()[$actionName];
        $params = [];
        $headers = [];
//        $headers = [ "Authorization" => "Bearer " . config('broadsign.api.key') ]; -- Broadsign HTTP Token Auth
        $path = $endpoint->path;

        if (count($args) >= 1) {
            $params = $args[0];

            // A get query has no body, replace in URL
            if (($endpoint->method === 'get') && is_numeric($args[0])) {
                $path = preg_replace("/{id}/", $args[0], $path);
                $params = [];
            }
        }

        if (count($args) >= 2) {
            $headers = $args[1];
        }

        if ($endpoint->includeDomainID) {
            $params["domain_id"] = config("broadsign.domain-id");
        }

        /** @var Response $response */
        Log::debug("Calling broadsign API: ({$endpoint->method}) {$path}", $params);

        $response = Http::withoutVerifying()
                        ->withOptions(["cert" => storage_path('broadsign.pem')])
                        ->withHeaders($headers)
                        ->{$endpoint->method}(config('broadsign.api.url') . $path, $params);

        if (!$response->successful()) {
            Log::error($response->json());
            throw new BadResponse("Received response with invalid status code", $response->status());
        }

        $responseBody = $response->json();

        // Execute post-request transformation if needed
        if (!is_null($endpoint->transformMethod)) {
            // Unwrap response content if needed
            if (static::$unwrapKey !== null) {
                $responseBody = $responseBody[static::$unwrapKey];
            }

            try {
                $responseBody = static::{$endpoint->transformMethod}($responseBody);
            } catch (RuntimeException $e) {
                Log::error("Could not parse Broadsign Response");
                Log::debug(print_r($response, true));
            }
        }

        return $responseBody;
    }

    abstract protected static function actions ();

    public function save (): self {
        $properties = array_filter($this->attributes,
            static fn ($key) => in_array($key, static::$updatable, true),
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
    public function jsonSerialize (): array {
        return $this->toArray();
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray (): array {
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
    public function toJson ($options = 0): string {
        $json = json_encode($this->attributes, JSON_THROW_ON_ERROR | $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }
}
