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

        if (config('app.env') !== "production") {
            $filterString = json_encode($filters, JSON_THROW_ON_ERROR);
            clock()->event("GET: " . $model . "[" . $filterString . "]")->color("purple")->begin();
        }

        $response = $this->client->fields($fields)
                            ->get($model);

        if (config('app.env') !== "production") {
            clock()->event("GET: " . $model . "[" . $filterString . "]")->end();
        }

        return $response;
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

        if (config('app.env') !== "production") {
            $__clockIds = implode(",", $modelIds);
            clock()->event("GETbyID: " . $model . "[" . $__clockIds . "]")->color("purple")->begin();
        }

        $models   = $this->client->call($model, 'read', [$modelIds], ["fields" => $fields]);

        if (config('app.env') !== "production") {
            clock()->event("GETbyID: " . $model . "[" . $__clockIds . "]")->end();
        }

        return is_int($ids) ? $models->get(0, null) : $models;
    }

    public function update(Model $model, array $values): bool {
        if (config('app.env') !== "production") {
            $__clockString = json_encode($values, JSON_THROW_ON_ERROR);
            clock()->event("UPDATE: " . $model . "[" . $__clockString . "]")->color("purple")->begin();
        }

        $response = $this->client->where("id", "=", $model->getKey())
                            ->update($model::$slug, $values);

        if (config('app.env') !== "production") {
            clock()->event("UPDATE: " . $model . "[" . $__clockString . "]")->end();
        }

        return $response;
    }

    public function findBy(string $model, string $field, $value) {
        if (config('app.env') !== "production") {
            clock()->event("FINDBY: " . $model . "[$field => $value]")->color("purple")->begin();
        }

        $response =  $this->client->where($field, "=", $value)
                            ->get($model);

        if (config('app.env') !== "production") {
            clock()->event("FINDBY: " . $model . "[$field => $value]")->end();
        }

        return $response;
    }

    public function create(string $model, array $fields) {
        if (config('app.env') !== "production") {
            clock()->event("CREATE: " . $model)->color("purple")->begin();
        }

        $response =  $this->client->create($model, $fields);

        if (config('app.env') !== "production") {
            clock()->event("CREATE: " . $model)->end();
        }

        return $response;
    }

    public function delete(string $model, array $where) {
        if (config('app.env') !== "production") {
            $__clockString = json_encode($where, JSON_THROW_ON_ERROR);
            clock()->event("DELETE: " . $model . "[$__clockString]")->color("purple")->begin();
        }

        foreach ($where as $whereCondition) {
            $this->client->where(...$whereCondition);
        }

        $response = $this->client->delete($model);

        if (config('app.env') !== "production") {
            clock()->event("DELETE: " . $model . "[$__clockString]")->end();
        }

        return $response;
    }
}
