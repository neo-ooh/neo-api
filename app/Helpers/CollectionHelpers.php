<?php

namespace Neo\Helpers;

use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Neo\Models\Traits\WithPublicRelations;
use RuntimeException;

class CollectionHelpers {
    public static function loadPublicRelations(Collection $collection) {
        /** @var Collection $this */
        if ($collection->count() === 0) {
            return;
        }

        /** @var WithPublicRelations $model */
        $model = $collection->first();

        if (!in_array(WithPublicRelations::class, class_uses_recursive($model::class), true)) {
            throw new RuntimeException("Calling `loadPublicRelations` on a model collection without the trait");
        }

        $publicRelations = $model->getPublicRelationsList();

        foreach ($model->prepareRelationsList() as $requestedRelation) {
            clock($requestedRelation, $publicRelations);
            if (!array_key_exists($requestedRelation, $publicRelations)) {
                // Ignore invalid relations in dev
                if (config("app.env") === 'development') {
                    throw new InvalidArgumentException("Relation '$requestedRelation' is not marked as public for the model '" . static::class . "'");
                }

                continue;
            }

            self::performExpansion($collection, $publicRelations[$requestedRelation]);
        }
    }

    protected static function performExpansion(Collection $collection, string|array|callable $relation) {
        if (is_array($relation)) {
            foreach ($relation as $value) {
                self::performExpansion($collection, $value);
            }

            return;
        }

        if (is_callable($relation)) {
            foreach ($collection as $model) {
                $relation($model);
            }
            return;
        }

        // Relation is string
        $tokens = explode(":", $relation);
        // If only one token is found, we imply no action is given, and default to `load`
        if (count($tokens) === 1) {
            $action  = "load";
            $request = $tokens[0];
        } else {
            // Otherwise, we validate the given action, and if it is not recognized, we default to `load` as well
            switch ($tokens[0]) {
                case "load":
                case "append":
                    $action  = array_shift($tokens);
                    $request = implode(":", $tokens);
                    break;
                default:
                    $action  = "load";
                    $request = implode(":", $tokens);
            }
        }

        switch ($action) {
            case 'append':
                $collection->append($request);
                break;
            case 'load':
                $collection->loadMissing($request);
                break;
        }
    }
}
