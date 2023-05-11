<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Endpoint.php
 */

namespace Neo\Services\API;

use Neo\Services\API\Parsers\ResponseParser;

/**
 * Represent an API endpoint and its specificities. By default, the body of the response is returned as-is, but can be
 * transformed automatically by defining a parser.
 *
 * @package Neo\BroadSign
 *
 * @method static static get(string $path)
 * @method static static post(string $path)
 * @method static static put(string $path)
 * @method static static delete(string $path)
 * @see     ResponseParser
 *
 */
class Endpoint {

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    /**
     *
     * @var string Base url for the endpoint.
     */
    public string $base = "";

    /**
     * @var string The HTTP method used for by the endpoint (get, post, put, etc.)
     */
    public string $method;
    public string $path;

    /**
     * @var array Options to pass along when querying the endpoint, a defined by GuzzleHTTP
     */
    public array $options = [
        "verify"      => false,
        "http_errors" => false,
    ];

    protected array $urlParameters = [];

    public string $format = "json";

    /**
     * The parser to apply to the response body. The parser is not triggered by the APIClient::call method and must be called
     * manually. The property is attached to the endpoint as a convenience
     *
     * @var ResponseParser|Callable|null
     */
    public mixed $parse = null;

    /**
     * Tell if the value should be cached, and for how long.
     * A value of zero means no caching, other values will be used as the cache entry duration, units in seconds.
     * Routes other than GET are never cached.
     *
     * @var int
     */
    public int $cache = 0;

    /*
    |--------------------------------------------------------------------------
    | *** Magic ***
    |--------------------------------------------------------------------------
    */

    public function __construct(string $method, string $path) {
        $this->method = $method;
        $this->path   = $path;
    }

    public static function __callStatic($verb, $args): static {
        return new static($verb, ...$args);
    }

    public function __toString(): string {
        return "$this->method@{$this->getUrl()}";
    }

    /*
    |--------------------------------------------------------------------------
    | Fluent Modifiers
    |--------------------------------------------------------------------------
    */

    public function multipart(): static {
        $this->format = "multipart";
        return $this;
    }

    /**
     * Specified the parser for the endpoint responses
     *
     * @param $parser
     * @return $this
     */
    public function parser($parser): static {
        $this->parse = $parser;
        return $this;
    }

    /**
     * Enable caching by setting the cache duration in seconds
     *
     * @param int $duration
     * @return static
     */
    public function cache(int $duration): static {
        $this->cache = $duration;
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Additional setters
    |--------------------------------------------------------------------------
    */

    public function getParamsList(): array {
        preg_match_all("/{([_a-zA-Z]+)}/", $this->path, $matches);
        return $matches[1];
    }

    public function setParam(string $parameter, $value): void {
        $this->urlParameters[$parameter] = $value;
    }

    /**
     * Fill in the endpoint uri parameter using a given payload
     * If the URI has a `id` parameter and the payload is a int or string, it will be replaced with it
     * If the payload is an array, an associative match is done
     *
     * @param int|string|array|null $payload
     * @return void
     */
    public function setParams(mixed $payload): void {
        $uriParams = $this->getParamsList();
        if (count($uriParams) > 0) {
            if ($uriParams[0] === "id" && (is_numeric($payload) || is_string($payload))) {
                $this->setParam("id", $payload);
            } else if (is_array($payload)) {
                foreach ($uriParams as $param) {
                    $this->setParam($param, $payload[$param]);
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Getters
    |--------------------------------------------------------------------------
    */

    protected function fillInParameters(string $str): string {
        $parameters = array_map(static fn($key) => '/{' . $key . '}/', array_keys($this->urlParameters));
        $values     = array_map("rawurlencode", array_values($this->urlParameters));
        return preg_replace($parameters, $values, $str);
    }

    public function getPath(): string {
        return $this->fillInParameters($this->path);
    }

    public function getBase() {
        return $this->fillInParameters($this->base);
    }

    public function getUrl(): string {
        $base = $this->getBase();
        $base = str_ends_with($base, "/") ? $base : $base . "/";

        $path = $this->getPath();
        $path = str_starts_with($this->getPath(), "/") ? substr($path, 1) : $path;

        return $base . $path;
    }

}
