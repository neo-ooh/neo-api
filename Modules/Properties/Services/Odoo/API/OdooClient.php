<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OdooClient.php
 */

namespace Neo\Modules\Properties\Services\Odoo\API;

use Edujugon\Laradoo\Exceptions\OdooException;
use Edujugon\Laradoo\Facades\Odoo as OdooFacade;
use Edujugon\Laradoo\Odoo;
use JsonException;
use Neo\Modules\Properties\Services\Odoo\Models\OdooModel;

/**
 * Handles communication with Odoo XMLRPC API
 */
class OdooClient {
    public Odoo $client;

    /**
     * @param string $url
     * @param string $db The odoo database to use
     * @param string $userLogin
     * @param string $userPassword
     * @throws OdooException
     */
    public function __construct(string $url, protected string $db, string $userLogin, string $userPassword) {
        $this->client = OdooFacade::host($url)
                                  ->apiSuffix('/')
                                  ->db($db)
                                  ->username($userLogin)
                                  ->password($userPassword)
                                  ->connect();

    }

    /**
     * @throws OdooException
     * @throws JsonException
     */
    public function get(string $model, array $filters = [], array $fields = [], int|null $limit = null, int $offset = 0, string $locale = 'en_CA') {
        $parameters = [
            "fields"  => $fields,
            "context" => [
                "lang" => $locale,
            ],
        ];

        if ($limit !== null) {
            $parameters["limit"]  = $limit;
            $parameters["offset"] = $offset;
        }

        $event = uniqid('', true);
        clock()->event("[Odoo] get@$model")->name($event)->color("cyan")->begin();

        $response = $this->client->call($model, 'search_read', [$filters], $parameters);

        clock()->event($event)->end();
        
        return $response;
    }

    /**
     * Pull one or more models from the Odoo API.
     * If an int is passed as the id, only the first result will be returned
     *
     * @param string    $model
     * @param array|int $ids
     * @param array     $fields
     * @param string    $locale
     * @return mixed
     */
    public function getById(string $model, array|int $ids, array $fields = [], string $locale = 'en_CA') {
        $modelIds = is_int($ids) ? [$ids] : $ids;

        $event = uniqid('', true);

        clock()
            ->event("[Odoo] ids@$model [" . implode(', ', $modelIds) . "]")
            ->name($event)
            ->color("cyan")
            ->begin();

        $models = $this->client->call($model, 'read', [$modelIds], [
            "fields"  => $fields,
            "context" => [
                "lang" => $locale,
            ],
        ]);

        clock()->event($event)->end();

        return is_int($ids) ? $models->get(0, null) : $models;
    }

    public function update(OdooModel $model, array $values): bool {
        $event = uniqid('', true);
        clock()->event("[Odoo] get@{$model::$slug}")->name($event)->color("cyan")->begin();

        $response = $this->client->where("id", "=", $model->getKey())
                                 ->update($model::$slug, $values);

        clock()->event($event)->end();

        return $response;
    }

    public function findBy(string $model, string $field, $value, int|null $limit = null, int $offset = 0) {
        $request = $this->client->where($field, "=", $value);

        if ($limit !== null) {
            $request->limit($limit, $offset);
        }

        $event = uniqid('', true);
        clock()->event("[Odoo] find@$model [$field => $value]", ["data" => [$field => $value]])
               ->name($event)->color("cyan")->begin();

        $response = $request->get($model);

        clock()->event($event)->end();

        return $response;
    }

    public function create(string $model, array $fields) {
        $event = uniqid('', true);
        clock()->event("[Odoo] create@$model")->name($event)->color("cyan")->begin();

        $response = $this->client->create($model, $fields);

        clock()->event($event)->end();

        return $response;
    }

    public function createMany(string $model, array $records) {
        $event = uniqid('', true);
        clock()->event("[Odoo] create@$model")->name($event)->color("cyan")->begin();

        $response = $this->client->call($model, "create", [$records]);

        clock()->event($event)->end();

        return $response;
    }

    public function delete(string $model, array $where) {
        foreach ($where as $whereCondition) {
            $this->client->where(...$whereCondition);
        }

        $event = uniqid('', true);
        clock()->event("[Odoo] delete@$model")->name($event)->color("cyan")->begin();

        $response = $this->client->delete($model);

        clock()->event($event)->end();

        return $response;
    }

    public function custom(string $model, string $method, array $arguments) {
        $event = uniqid('', true);
        clock()->event("[Odoo] $method@$model")->name($event)->color("cyan")->begin();

        $response = $this->client->call($model, $method, [$arguments]);

        clock()->event($event)->end();

        return $response;
    }
}
