<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HivestackModel.php
 */

namespace Neo\Modules\Properties\Services\Hivestack\Models;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Neo\Modules\Properties\Services\Hivestack\API\HivestackClient;
use Neo\Services\API\Endpoint;
use Neo\Services\API\Traits\HasAttributes;
use ReflectionClass;

abstract class HivestackModel {
    use HasAttributes;

    public string $endpoint;

    public string $key;

    public function __construct(protected HivestackClient $client, array $attributes = []) {
        $this->setAttributes($attributes);
    }

    /**
     * @return bool
     * @throws GuzzleException
     * @throws RequestException
     */
    public function create(): bool {
        $endpoint = new Endpoint("POST", $this->getEndpointName());

        $response = $this->client->call($endpoint, $this->getAttributes());

        $this->setAttributes($response->json()[0]);
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

        $response = $this->client->call($endpoint, $this->getAttributes());

        $this->setAttributes($response->json());
        return $this;
    }

    /**
     * Loads the model using its key
     *
     * @param HivestackClient $client
     * @param                 $key
     * @return static
     * @throws GuzzleException
     * @throws RequestException
     */
    public static function find(HivestackClient $client, $key) {
        $model = new static($client);
        $model->setAttribute($model->key, $key);
        return $model->refresh();
    }

    /**
     * @param HivestackClient $client
     * @param int             $limit
     * @param int             $offset
     * @return Collection<static>
     * @throws GuzzleException
     * @throws RequestException
     */
    public static function all(HivestackClient $client, int $limit = 100, int $offset = 0): Collection {
        $model    = new static($client);
        $endpoint = new Endpoint("GET", $model->getEndpointName());

        $response = $client->call($endpoint, [
            '$top'  => $limit,
            '$skip' => $offset,
        ]);

        /** @var array<array<string, mixed>> $rawModels */
        $rawModels = $response->json();

        return collect($rawModels)->map(fn(array $attributes) => new static($client, $attributes));
    }

    /**
     * @throws RequestException
     * @throws GuzzleException
     */
    public function save(): bool {
        // If the model key is missing or null, we want to create the model
        if (!isset($this->{$this->key}) || $this->getKey() === null) {
            return $this->create();
        }

        $endpoint = new Endpoint("PUT", $this->getEndpointName() . "/" . $this->getKey());

        $response = $this->client->call($endpoint, $this->getAttributes());

        $this->setAttributes($response->json());
        return true;
    }

    /**
     * @throws RequestException
     * @throws GuzzleException
     */
    public function delete(): bool {
        $endpoint = new Endpoint("DELETE", $this->getEndpointName() . "/" . $this->getKey());

        $this->client->call($endpoint, $this->getAttributes());

        return true;
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
