<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PublicRelations.php
 */

namespace Neo\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;
use Neo\Models\Traits\HasPublicRelations;
use RuntimeException;

class PublicRelations {
	/**
	 * @template T of Model|Collection
	 * @param T                 $subject
	 * @param string|array|null $relations
	 * @param bool              $bypassGates
	 * @return T
	 */
	public static function loadPublicRelations(Model|Collection $subject, string|array|null $relations = null, bool $bypassGates = false): Model|Collection {
		if ($subject instanceof Collection) {
			// Short-circuit if the collection is empty
			if ($subject->count() === 0) {
				return $subject;
			}

			$model = $subject->first();
		} else {
			$model = $subject;
		}

		// Make sure the target model as the `HasPublicRelation` trait
		if (!in_array(HasPublicRelations::class, class_uses_recursive($model::class), true)) {
			throw new RuntimeException("Calling `loadPublicRelations` on a Models/Models Collection without the `HasPublicRelation` Trait");
		}

		$publicRelations = $model->getPublicRelationsList();

		foreach (static::prepareRelationsList($relations) as $requestedRelation) {
			if (!array_key_exists($requestedRelation, $publicRelations)) {
				// Block on invalid relations in dev
				if (config("app.env") === 'development') {
					throw new InvalidArgumentException("Relation '$requestedRelation' is not marked as public for the model '" . static::class . "'");
				}

				continue;
			}

			self::performExpansion($subject, $publicRelations[$requestedRelation], $bypassGates);
		}

		return $subject;
	}

	protected static function prepareRelationsList(string|array|null $relations = null) {
		if ($relations !== null) {
			return is_array($relations) ? $relations : [$relations];
		}

		return Request::input("with", []);
	}

	protected static function performExpansion(Model|Collection $subject, Relation|string|array|callable $relation, bool $bypassGate = false): void {
		if (is_array($relation)) {
			foreach ($relation as $value) {
				self::performExpansion($subject, $value);
			}

			return;
		}

		$relationObject = $relation instanceof Relation ? $relation : Relation::fromLegacy($relation);
		$relationObject->expand($subject, $bypassGate);
	}
}
