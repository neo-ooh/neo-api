<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OdooModel.php
 */

namespace Neo\Modules\Properties\Services\Odoo\Models;

use Cache;
use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonException;
use Neo\Modules\Properties\Services\Odoo\API\OdooClient;
use Neo\Services\API\Traits\HasAttributes;

abstract class OdooModel implements Arrayable {

    use HasAttributes;

    /**
     * @var string The model identifier (eg. res.partner)
     */
    public static string $slug;

    /**
     * @var string The model unique key
     */
    protected static string $key = "id";

    /**
     * @var array List of filters that will be applied to every get request for this model.
     */
    protected static array $filters;

    /**
     * @var array The fields that should be pulled when retrieving this model. An empty array means all fields.
     */
    protected static array $fields = [];

    protected static array $relations = [];

    protected OdooClient $client;

    /**
     * @param OdooClient       $client       Odoo client for future requests from this model
     * @param array|Collection $attributes   Attributes of the model
     * @param bool             $isIncomplete If true, the model will fetch itself when trying to access a missing property
     */
    final public function __construct(
        OdooClient       $client,
        array|Collection $attributes = [],
        protected bool   $isIncomplete = false
    ) {
        $this->client = $client;
        $this->setAttributes((array)$attributes);
    }

    protected function setAttributes(array $attributes): void {
        $this->attributes = $attributes;

        // DisplayType relations
        foreach (static::$relations as $relation => $modelType) {
            if (!isset($this->{$relation})) {
                continue;
            }

            $attr = [
                "id"   => $this->{$relation}[0],
                "name" => $this->{$relation}[1],
            ];

            // Set relation as incomplete model
            $this->{$relation} = new $modelType($this->client, $attr, true);
        }
    }

    public function getKey(): mixed {
        return $this->{static::$key};
    }

    protected static function makeCacheKey(int $id) {
        return "odoo-" . static::$slug . "-" . $id;
    }

    protected function getCacheKey(): string {
        return static::makeCacheKey($this->getKey());
    }

    public function invalidate() {
        Cache::tags(['odoo-data'])->delete($this->getCacheKey());
    }

    /**
     * Pull all records for the current model
     *
     * @param OdooClient $client
     * @param array      $filters
     * @param int|null   $limit
     * @param int        $offset
     * @return Collection
     * @throws JsonException
     * @throws OdooException
     */
    public static function all(OdooClient $client, array $filters = [], array $fields = [], int|null $limit = null, int $offset = 0): Collection {
        $rawModels = $client->get(static::$slug, array_merge(static::$filters, $filters), array_merge(static::$fields, $fields), $limit, $offset);
        return $rawModels->map(static fn($model) => new static($client, $model));
    }

    /**
     * Pull multiple records using a custom field
     *
     * @param OdooClient $client
     * @param string     $field
     * @param            $value
     * @param int|null   $limit
     * @param int        $offset
     * @return Collection
     */
    public static function findBy(OdooClient $client, string $field, $value, int|null $limit = null, int $offset = 0): Collection {
        $models = $client->findBy(static::$slug, $field, $value, $limit, $offset);

        $dummy = new static($client);

        foreach ($models as $model) {
            $dummy->{static::$key} = $model[static::$key];

            Cache::tags(["odoo-data", "odoo-" . static::$slug])->put($dummy->getCacheKey(), $model);
        }

        return $models->map(fn($record) => new static($client, $record));
    }

    /**
     * Pull multiple records using a custom field
     *
     * @param OdooClient $client
     * @param array      $filters
     * @return Collection
     * @throws JsonException
     * @throws OdooException
     */
    public static function search(OdooClient $client, array $filters): Collection {
        $models = $client->get(static::$slug, $filters);

        foreach ($models as $model) {
            Cache::tags(["odoo-data", "odoo-" . static::$slug])->put($model->getCacheKey(), $model);
        }

        return $models->map(fn($record) => new static($client, $record));
    }

    /**
     * Pull multiple records using their ids
     *
     * @param OdooClient       $client
     * @param array|Collection $ids
     * @return Collection<static>
     * @throws JsonException
     */
    public static function getMultiple(OdooClient $client, array|Collection $ids): Collection {
        $models = $client->getById(static::$slug, $ids, static::$fields);

        foreach ($models as $model) {
            Cache::tags(["odoo-data", "odoo-" . static::$slug])->put($model->getCacheKey(), $model);
        }

        return $models->mapWithKeys(fn($record) => [$record[static::$key] => new static($client, $record)]);
    }

    /**
     * Pull a specific record using its id
     *
     * @param OdooClient $client
     * @param mixed      $id Unique ID of the record
     * @return static|null
     * @throws JsonException
     */
    public static function get(OdooClient $client, $id): static|null {
        $response = Cache::tags(["odoo-data", "odoo-" . static::$slug])
                         ->remember(static::makeCacheKey($id), 3600 * 6, function () use ($id, $client) {
                             return $client->getById(static::$slug, $id, static::$fields);
                         });

        if (!$response) {
            return null;
        }

        return new static($client, $response);
    }

    /**
     * @throws JsonException
     */
    protected function handleMissingAttribute(string $attribute): void {
        // If the model is marked as incomplete, we pull it from the db
        if (!$this->isIncomplete) {
            return;
        }

        $this->setAttributes(static::get($this->client, $this->getKey())->toArray());
        $this->isIncomplete = false;
    }

    /**
     * Push the value of the specified fields to Odoo
     *
     * @param array $fields
     * @return bool
     */
    public function update(array $fields): bool {
        $values  = collect($fields)->mapWithKeys(fn($k) => [$k => $this->{$k}]);
        $success = $this->client->update($this, $values->toArray());

        if ($success) {
            $this->invalidate();
        }

        return $success;
    }

    /**
     * Store the model in Odoo
     *
     * @param OdooClient $client
     * @param array      $fields
     * @param bool       $pullRecord
     * @return static|int
     * @throws JsonException
     */
    public static function create(OdooClient $client, array $fields, bool $pullRecord = true): static|int {
        $recordId = $client->create(static::$slug, $fields);
        return $pullRecord ? static::get($client, $recordId) : $recordId;
    }

    /**
     * Store multiple records in Odoo
     *
     * @param OdooClient $client
     * @param array      $records
     * @return Collection<integer>
     */
    public static function createMany(OdooClient $client, array $records): Collection {
        return $client->createMany(static::$slug, $records);
    }

    /**
     * @param OdooClient $client
     * @param array      $where
     * @return Collection|string|true
     */
    public static function delete(OdooClient $client, array $where) {
        $response = $client->delete(static::$slug, $where);
        Cache::tags(["odoo-" . static::$slug])->flush();
        return $response;
    }

    /**
     * @return Collection|string|true
     */
    public function remove() {
        $response = $this->client->delete(static::$slug, [[static::$key, "=", $this->getKey()]]);
        $this->invalidate();
        return $response;
    }
}
