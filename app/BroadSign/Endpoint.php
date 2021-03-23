<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Endpoint.php
 */

namespace Neo\BroadSign;

/**
 * Represent a broadsign API endpoint and its specificities. By default endpoints are expected to return a single
 * model. This behaviour can be customized by specifying if the endpoint returns multiple models or a simple ID.
 *
 * @see     Endpoint::multiple()
 * @see     Endpoint::id()
 *
 * @package Neo\BroadSign
 *
 * @method static Endpoint get(string $path)
 * @method static Endpoint post(string $path)
 * @method static Endpoint put(string $path)
 * @method static Endpoint delete(string $path)
 */
class Endpoint {

    /*
    |--------------------------------------------------------------------------
    | Properties
    |--------------------------------------------------------------------------
    */

    public string $method;
    public string $path;

    public bool $includeDomainID = true;

    public ?string $transformMethod = "asSelf";

    /**
     * Tell if the value should be cached, and for how long.
     * A value of zero means no caching, other values will be used as the cache entry duration, units in seconds.
     * Routes other that GET are never cached.
     * @var int
     */
    public int $cache = 0;

    /*
    |--------------------------------------------------------------------------
    | *** Magic ***
    |--------------------------------------------------------------------------
    */

    public function __construct (string $method, string $path) {
        $this->method = $method;
        $this->path = $path;
    }

    public static function __callStatic ($verb, $args): Endpoint {
        return new Endpoint($verb, ...$args);
    }

    public function __toString(): string {
        return "Endpoint:{$this->method}@{$this->path}";
    }

    /*
    |--------------------------------------------------------------------------
    | Modifiers
    |--------------------------------------------------------------------------
    */

    /**
     * Specified if the parameter "domain_id" should be automatically added to the request or not
     *
     * @param bool $includeDomain
     *
     * @return $this
     */
    public function domain (bool $includeDomain): Endpoint {
        $this->includeDomainID = $includeDomain;
        return $this;
    }

    /**
     * Specifies this endpoint returns multiple instance of the model. The response will be converted to a collection
     * of the current model
     *
     * @return $this
     */
    public function multiple (): Endpoint {
        $this->transformMethod = "asMultipleSelf";
        return $this;
    }

    /**
     * Specifies this endpoint returns only an ID. The response will be converted to an integer.
     *
     * @return $this
     */
    public function id (): Endpoint {
        $this->transformMethod = "asID";
        return $this;
    }

    /**
     * Specified the returned value of this endpoint should be ignored
     *
     * @return $this
     */
    public function ignore (): self {
        $this->transformMethod = null;
        return $this;
    }

    /**
     * Specified the returned value of this endpoint should be ignored
     *
     * @param string|null $method
     *
     * @return $this
     */
    public function customTransform (?string $method): self {
        $this->transformMethod = $method;
        return $this;
    }

    /**
     * Enable caching by setting the cache duration in seconds
     *
     * @param int $duration
     * @return Endpoint
     */
    public function cache(int $duration) {
        $this->cache = $duration;
        return $this;
    }
}
