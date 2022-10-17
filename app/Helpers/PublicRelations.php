<?php

namespace Neo\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;
use Neo\Models\Traits\HasPublicRelations;
use RuntimeException;

class PublicRelations {
    public static function loadPublicRelations(Model|Collection $subject, string|array|null $relations = null): void {
        if ($subject instanceof Collection) {
            // Short-circuit if the collection is empty
            if ($subject->count() === 0) {
                return;
            }

            $model = $subject->first();
        } else {
            $model = $subject;
        }

        // Make sure the target model as the `HasPublicRelation` trait
        if (!in_array(HasPublicRelations::class, class_uses_recursive($model::class), true)) {
            throw new RuntimeException("Calling `loadPublicRelations` on a Model/Model Collection without the `HasPublicRelation` Trait");
        }

        $publicRelations = $model->getPublicRelationsList();

        foreach (static::prepareRelationsList($relations) as $requestedRelation) {
            clock($requestedRelation, $publicRelations);
            if (!array_key_exists($requestedRelation, $publicRelations)) {
                // Block on invalid relations in dev
                if (config("app.env") === 'development') {
                    throw new InvalidArgumentException("Relation '$requestedRelation' is not marked as public for the model '" . static::class . "'");
                }

                continue;
            }

            self::performExpansion($subject, $publicRelations[$requestedRelation]);
        }
    }

    protected static function prepareRelationsList(string|array|null $relations = null) {
        if ($relations !== null) {
            return is_array($relations) ? $relations : [$relations];
        }

        return Request::input("with", []);
    }

    protected static function performExpansion(Model|Collection $subject, string|array|callable $relation): void {
        if (is_array($relation)) {
            foreach ($relation as $value) {
                self::performExpansion($subject, $value);
            }

            return;
        }

        if (is_callable($relation)) {
            if ($subject instanceof Collection) {
                foreach ($subject as $model) {
                    $relation($model);
                }
            } else {
                $relation($subject);
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
                case "count":
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
                $subject->append($request);
                break;
            case 'load':
                $subject->loadMissing($request);
                break;
            case 'count':
                $subject->loadCount($request);
                break;
        }
    }
}
