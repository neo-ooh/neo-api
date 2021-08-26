<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Model.php
 */

namespace Neo\Services\API\Odoo;

use ArrayAccess;
use Illuminate\Support\Collection;
use Neo\Services\API\Traits\HasAttributes;

abstract class Model {
    use HasAttributes;

    /**
     * @var string The model identifier (eg. res.partner)
     */
    protected static string $slug;

    /**
     * @var array List of filters that will be applied to every get request for this model.
     */
    protected static array $filters;

    /**
     * @var array The fields that should be pulled when retrieving this model. An empty array means all fields.
     */
    protected static array $fields = [];

    protected static array $relations = [];

    protected Client $client;

    /**
     * @param Client           $client       Odoo client for future requests from this model
     * @param array|Collection $attributes   Attributes of the model
     * @param bool             $isIncomplete If true, the model will fetch itself when trying to access a missing property
     */
    final public function __construct(
        Client         $client,
        array|Collection          $attributes = [],
        protected bool $isIncomplete = false
    ) {
        $this->client = $client;
        $this->setAttributes((array)$attributes);
    }

    protected function setAttributes(array $attributes) {
        $this->attributes = (array)$attributes;

        // Format relations
        foreach (static::$relations as $relation => $modelType) {
            if (!isset($this->{$relation})) {
                continue;
            }

            $attr = [
                "id"   => $this->{$relation}[0],
                "name" => $this->{$relation}[1]
            ];

            // Set relation as incomplete model
            $this->{$relation} = new $modelType($this->client, $attr, true);
        }
    }

    /**
     * Pull all records for the current model
     * @param Client $client
     * @param array  $filters
     * @return Collection
     */
    public static function all(Client $client, $filters = []): Collection {
        $rawModels = $client->get(static::$slug, array_merge(static::$filters, $filters), static::$fields);

        return $rawModels->map(static fn($model) => new static($client, $model));
    }

    /**
     * Pull multiple records using their ids
     * @param Client           $client
     * @param array|Collection $ids
     * @return Collection
     */
    public static function getMultiple(Client $client, array|Collection $ids): Collection {
        return $client->getById(static::$slug, $ids, static::$fields)->map(fn($record) => new static($client, $record));
    }

    /**
     * Pull a specific record using its id
     * @param Client $client
     * @param mixed  $id Unique ID of the record
     * @return static
     */
    public static function get(Client $client, $id): static {
        return new static($client, $client->getById(static::$slug, $id, static::$fields));
    }

    protected function handleMissingAttribute(string $attribute): void {
        // If the model is marked as incomplete, we pull it from the db
        if (!$this->isIncomplete) {
            return;
        }

        $this->setAttributes(static::get($this->client, $this->id));
        $this->isIncomplete = false;
    }
}
