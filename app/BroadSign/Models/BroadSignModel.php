<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignModel.php
 */

namespace Neo\BroadSign\Models;

use BadMethodCallException;
use Facade\FlareClient\Http\Exceptions\BadResponse;
use GuzzleHttp\Psr7\MultipartStream;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use JsonSerializable;
use Neo\BroadSign\Endpoint;

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

    final public function __construct(array $attributes = []) {
        $this->attributes = $attributes;
    }

    /**
     * @param array $responseBody
     *
     * @return static
     */
    protected static function asSelf(array $responseBody): self {
        return new static($responseBody[0]);
    }

    /**
     * @param array $responseBody
     *
     * @return Collection
     */
    protected static function asMultipleSelf(array $responseBody): Collection {
        $elements = [];

        foreach ($responseBody as $rawEl) {
            $elements[] = new static($rawEl);
        }

        return collect($elements);
    }

    protected static function asID(array $responseBody): int {
        return $responseBody[0]["id"];
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
     * @param array  $body
     *
     * @return array|mixed
     * @return array|mixed
     * @throws BadResponse|JsonException
     */
    public function callAction(string $action, array $body) {
        return static::__callStatic($action, [$body]);
    }

    /**
     * @param string $actionName
     * @param        $args
     *
     * @return mixed
     * @throws BadResponse
     * @throws JsonException
     */
    public static function __callStatic(string $actionName, $args) {
        if (!array_key_exists($actionName, static::actions())) {
            $className = static::class;
            throw new BadMethodCallException("Static method {$actionName} does not exist on model {$className}.");
        }

        /** @var Endpoint $endpoint */
        $endpoint = static::actions()[$actionName];
        $params   = [];
        $headers  = [];
//        $headers = [ "Authorization" => "Bearer " . config('broadsign.api.key') ]; -- Broadsign HTTP Token Auth
        $path = $endpoint->path;

        if (count($args) >= 1) {
            $params = $args[0];

            // A get query has no body, replace in URL
            if (($endpoint->method === 'get') && is_numeric($args[0])) {
                $path   = preg_replace("/{id}/", $args[0], $path);
                $params = [];
            }
        }

        if (count($args) >= 2) {
            $headers = $args[1];
        }

        if ($endpoint->includeDomainID) {
            $params["domain_id"] = config("broadsign.domain-id");
        }

        if ($endpoint->cache === 0) {
            return static::executeCallAndGetResponse($endpoint, $path, $headers, $params);
        }

        // Get the unique slug of the request
        $slug = $endpoint->method . "@" . $path . "#" . json_encode($params, JSON_THROW_ON_ERROR);

        // Check if the slug is in our database
        return Cache::remember($slug, $endpoint->cache, fn() => static::executeCallAndGetResponse($endpoint, $path, $headers, $params));
    }

    /**
     * @param Endpoint $endpoint
     * @param string   $path
     * @param array    $headers
     * @param mixed    $payload // Either URL params for HEAD/GET requests or request body for POST, PUT, etc.
     * @return integer|array|BroadSignModel
     * @throws BadResponse
     * @throws JsonException
     */
    protected static function executeCallAndGetResponse(Endpoint $endpoint, string $path, array $headers, $payload) {

        /** @var Response $response */
        $request = Http::withoutVerifying()
                        ->withOptions(["cert" => storage_path('broadsign.pem')])
                        ->withHeaders($headers);

        if($endpoint->format === "multipart") {
            $boundary = "__X__BROADSIGN_REQUEST__";
            $request->contentType("multipart/mixed; boundary=$boundary");
            $payload = new MultipartStream($payload, $boundary);
        }

         $response = $request->{$endpoint->method}(config('broadsign.api.url') . $path, $payload);

        // In case the resource wasn't found (404), return null
        if ($response->status() === 404) {
            return null;
        }

        if (!$response->successful()) {
            // Request was not successful, log th exchange
            Log::channel("broadsign")->debug("request:{$endpoint->method} [{$path}] " . json_encode($payload, JSON_THROW_ON_ERROR));
            Log::channel("broadsign")
               ->log($response->status() === 200 ? "debug" : "error", "response:{$response->status()} [{$path}] " . $response->body());

            throw new BadResponse("", $response->status());
        }

        $responseBody = $response->json();

        // Execute post-request transformation if needed
        if (!is_null($endpoint->transformMethod)) {
            // Unwrap response content if needed
            if (static::$unwrapKey !== null) {
                $responseBody = $responseBody[static::$unwrapKey];
            }

            $responseBody = static::{$endpoint->transformMethod}($responseBody);
        }

        return $responseBody;
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
