<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
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
    public function ignore (): Endpoint {
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
    public function customTransform (?string $method): Endpoint {
        $this->transformMethod = $method;
        return $this;
    }
}
