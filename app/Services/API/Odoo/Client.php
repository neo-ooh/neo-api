<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Client.php
 */

namespace Neo\Services\API\Odoo;

use Arr;
use Edujugon\Laradoo\Odoo;

/**
 * Handles communication with Odoo XMLRPC API
 */
class Client {
    public Odoo $client;

    /**
     * @param string $basepath URL to the Odoo server
     * @param string $db       The odoo database to use
     * @param string $userLogin
     * @param string $userPassword
     */
    public function __construct(string $url, protected string $db, string $userLogin, string $userPassword) {
        $this->client = \Edujugon\Laradoo\Facades\Odoo::host($url)
                                                    ->apiSuffix('/')
                                                    ->db($db)
                                                    ->username($userLogin)
                                                    ->password($userPassword)
                                                    ->connect();

    }

    public function get(string $model, array $filters = [], array $fields = []) {
        foreach ($filters as $filter) {
            $this->client->where(...$filter);
        }

        return $this->client->fields($fields)
                     ->get($model);
    }

    /**
     * Pull one or more models from the Odoo API.
     * If an int is passed as the id, only the first result will be returned
     *
     * @param string    $model
     * @param array|int $ids
     * @param array     $fields
     * @return mixed
     */
    public function getById(string $model, array|int $ids, $fields = []) {
        $modelIds = is_int($ids) ? [$ids] : $ids;
        $models = $this->client->call($model, 'read', [$modelIds], ["fields" => $fields]);

        return is_int($ids) ? $models->get(0, null) : $models;
    }

    public function update(Model $model, array $values): bool {
        return $this->client->where("id", "=", $model->getKey())
                    ->update($model::$slug, $values);
    }

    public function findBy(string $model, string $field, $value) {
        return $this->client->where($field, "=", $value)
                            ->get($model);
    }

    public function create(string $model, array $fields) {
        return $this->client->create($model, $fields);
    }

    public function delete(string $model, array $where) {
        foreach ($where as $whereCondition) {
            $this->client->where(...$whereCondition);
        }

        return $this->client->delete($model);
    }
}
