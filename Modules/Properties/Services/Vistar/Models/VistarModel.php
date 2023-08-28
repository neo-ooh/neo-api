<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - VistarModel.php
 */

namespace Neo\Modules\Properties\Services\Vistar\Models;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Str;
use Neo\Modules\Properties\Services\Vistar\VistarClient;
use Neo\Services\API\APIAuthenticationError;
use Neo\Services\API\Endpoint;
use Neo\Services\API\Traits\HasAttributes;
use ReflectionClass;
use Throwable;

abstract class
VistarModel {
	use HasAttributes;

	public string $endpoint;

	public string $slug;

	public string $key;

	public function __construct(protected VistarClient $client, array $attributes = []) {
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
		$endpoint = new Endpoint("GET", $this->getEndpointName() . "/" . $this->getKey());

		$response = $this->client->call($endpoint, []);

		$this->setAttributes($response->json());
		return $this;
	}

	/**
	 * Loads the model using its key
	 *
	 * @param VistarClient    $client
	 * @param                 $key
	 * @return static
	 */
	public static function find(VistarClient $client, $key): static {
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
	 * @param VistarClient $client
	 * @return Collection<static>
	 * @throws GuzzleException
	 * @throws RequestException
	 * @throws APIAuthenticationError
	 */
	public static function all(VistarClient $client): Enumerable {
		$model = new static($client);

		$endpoint = new Endpoint("GET", $model->getEndpointName() . "/");

		$response = $client->call($endpoint, []);

		/** @var array<array<string, mixed>> $rawModels */
		$rawModels = $response->json()[$model->slug];

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

		$response = $this->client->call($endpoint, $this->toArray());

		$this->setAttributes($response->json());
		return true;
	}

	/**
	 * @return bool
	 * @throws APIAuthenticationError
	 * @throws GuzzleException
	 * @throws \Neo\Modules\Properties\Services\Exceptions\RequestException
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
