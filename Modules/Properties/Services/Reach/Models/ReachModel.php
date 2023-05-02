<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReachModel.php
 */

namespace Neo\Modules\Properties\Services\Reach\Models;

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use League\Uri\Components\Query;
use League\Uri\Uri;
use Neo\Modules\Properties\Services\Reach\API\ModelsCollectionResponse;
use Neo\Modules\Properties\Services\Reach\ReachClient;
use Neo\Services\API\Endpoint;
use Neo\Services\API\Traits\HasAttributes;
use ReflectionClass;
use Throwable;

abstract class ReachModel {
    use HasAttributes;

    public string $endpoint;

    public string $key;

    public function __construct(protected ReachClient $client, array $attributes = []) {
        $this->setAttributes($attributes);
    }

    /**
     * @return bool
     * @throws GuzzleException
     * @throws RequestException
     */
    public function create(): bool {
        $endpoint = new Endpoint("POST", $this->getEndpointName() . "/");

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
        $endpoint = new Endpoint("GET", $this->getEndpointName() . "/" . $this->getKey() . "/");

        $response = $this->client->call($endpoint, $this->toArray());

        $this->setAttributes($response->json());
        return $this;
    }

    /**
     * Loads the model using its key
     *
     * @param ReachClient     $client
     * @param                 $key
     * @return static
     */
    public static function find(ReachClient $client, $key): static {
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
     * @param ReachClient $client
     * @param Carbon|null $ifModifiedSince
     * @param int         $limit
     * @return Collection<static>
     */
    public static function all(ReachClient $client, Carbon|null $ifModifiedSince = null, int $limit = 50): Enumerable {
        $model = new static($client);

        return LazyCollection::make(function () use ($ifModifiedSince, $model, $limit, $client) {
            $endpoint   = new Endpoint("GET", $model->getEndpointName() . "/");
            $parameters = [
                "page_size" => $limit,
            ];
            $headers    = [
                "If-Modified-Since" => $ifModifiedSince?->format("D, d M Y H:i:s \G\M\T"),
            ];

            do {
                $response           = $client->call($endpoint, $parameters, $headers);
                $collectionResponse = ModelsCollectionResponse::from($response->json());

                foreach ($collectionResponse->results as $result) {
                    yield new static($client, $result);
                }

                if ($collectionResponse->next === null) {
                    $endpoint = null;
                } else {
                    $nextUri            = Uri::createFromString($collectionResponse->next);
                    $endpoint           = new Endpoint("GET", $nextUri->getPath());
                    $endpoint->base     = Uri::createFromComponents([
                                                                        "scheme" => $nextUri->getScheme(),
                                                                        "host"   => $nextUri->getHost(),
                                                                        "port"   => $nextUri->getPort(),
                                                                    ])->toString();
                    $query              = Query::createFromUri($nextUri);
                    $parameters["page"] = $query->params("page");
                }
            } while ($endpoint !== null);
        });
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

        $endpoint = new Endpoint("PUT", $this->getEndpointName() . "/" . $this->getKey() . "/");

        $response = $this->client->call($endpoint, $this->toArray());

        $this->setAttributes($response->json());
        return true;
    }

    /**
     * @throws RequestException
     * @throws GuzzleException
     */
    public function delete(): bool {
        $endpoint = new Endpoint("DELETE", $this->getEndpointName() . "/" . $this->getKey() . "/");

        $this->client->call($endpoint, $this->toArray());

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
