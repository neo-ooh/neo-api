<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PlaceExchangeModel.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Neo\Modules\Properties\Services\PlaceExchange\PlaceExchangeClient;
use Neo\Services\API\APIAuthenticationError;
use Neo\Services\API\Endpoint;
use Neo\Services\API\Traits\HasAttributes;
use ReflectionClass;
use Throwable;

abstract class PlaceExchangeModel {
    use HasAttributes;

    public string $endpoint;

    public string $slug;

    public string $key;

    public function __construct(protected PlaceExchangeClient $client, array $attributes = []) {
        $this->setAttributes($attributes);
    }

    /**
     * @return bool
     * @throws GuzzleException
     * @throws RequestException
     */
    public function create(): bool {
        $endpoint = new Endpoint("POST", $this->getEndpointName());

        $response = $this->client->call($endpoint, $this->toArray(), [
            "Content-Type" => "application/json",
        ]);

        $this->setAttributes($response->json());
        return true;
    }

    /**
     * Reload the model from the API
     *
     * @return $this
     * @throws GuzzleException
     * @throws RequestException
     */
    public function refresh(): static {
        $endpoint = new Endpoint("GET", $this->getEndpointName() . "/" . $this->getKey());

        $response = $this->client->call($endpoint, []);

        $this->setAttributes($response->json());
        return $this;
    }

    /**
     * Loads the model using its key
     *
     * @param PlaceExchangeClient $client
     * @param                     $key
     * @return static
     */
    public static function find(PlaceExchangeClient $client, $key): static {
        $model = new static($client);
        $model->setAttribute($model->key, $key);
        try {
            $model->refresh();
        } catch (Throwable $e) {
            clock($e);
        } finally {
            return $model;
        }
    }

    /**
     * @param PlaceExchangeClient $client
     * @param int                 $pageSize
     * @return Collection<static>
     */
    public static function all(PlaceExchangeClient $client, int $pageSize = 500): Enumerable {
        $model = new static($client);

        return LazyCollection::make(function () use ($pageSize, $client, $model) {
            $endpoint   = new Endpoint("GET", $model->getEndpointName());
            $parameters = [
                "page_size" => $pageSize,
                "page"      => 0,
            ];

            do {
                $response  = $client->call($endpoint, $parameters);
                $rawModels = $response->json();

                foreach ($rawModels as $rawModel) {
                    yield new static($client, $rawModel);
                }

                if (count($rawModels) < $pageSize) {
                    $endpoint = null;
                } else {
                    $parameters["page"]++;
                }
            } while ($endpoint !== null);
        });
    }

    /**
     * Because PlaceExchange uses the unit NAME as id in the URL, we need to allow passing a key to the save function in case we
     * are changing the name of the unit
     *
     * @throws RequestException
     * @throws GuzzleException|APIAuthenticationError
     */
    public function save(string|null $key = null): bool {
        // If the model key is missing or null, we want to create the model
        if (!isset($this->{$this->key}) || $this->getKey() === null) {
            return $this->create();
        }

        $endpoint = new Endpoint("PATCH", $this->getEndpointName() . "/" . ($key === null ? $this->getKey() : $key));

        $response = $this->client->call($endpoint, $this->toArray());

        $this->setAttributes($response->json());
        return true;
    }

    /**
     * @return bool
     * @throws APIAuthenticationError
     * @throws GuzzleException
     * @throws RequestException
     */
    public function delete(): bool {
        $endpoint = new Endpoint("DELETE", $this->getEndpointName() . "/" . $this->getKey());

        $this->client->call($endpoint, []);

        return true;
    }

    public function setKey($value) {
        $this->setAttribute($this->key, $value);
        return $this;
    }


    public function getKey() {
        return $this->getAttribute($this->key);
    }

    protected function getEndpoint() {

    }

    protected function getEndpointName() {
        if (isset($this->endpoint)) {
            return $this->endpoint;
        }

        // Get the current class name without the namespace, and pluralize it
        $reflection = new ReflectionClass($this);
        return Str::plural(strtolower($reflection->getShortName()));
    }
}
