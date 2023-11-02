<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsGetter.php
 */

namespace Neo\Models\Utils;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Neo\Models\Actor;

class ActorsGetter {
	public const CLOSURES_TABLE = 'actors_closures';
	public const SHARES_TABLE = 'actors_shares';
	public const CONTRACTS_TABLE = 'contracts';

	/**
	 * @var Collection<int> List of selected actor ids
	 */
	protected Collection $selection;

	protected function __construct(protected int $focus) {
		$this->selection = collect();
	}

	/**
	 * Create a new instance of ActorsGetter with the given actor/actor id as focus
	 *
	 * @param int|Actor $actor
	 * @return static
	 */
	public static function from(int|Actor $actor) {
		if ($actor instanceof Actor) {
			return new static($actor->getKey());
		}

		return new static($actor);
	}

	/**
	 * Update the getter focus with the given actor
	 *
	 * @param int|Actor $actor
	 * @return $this
	 */
	public function setFocus(int|Actor $actor): self {
		if ($actor instanceof Actor) {
			$this->focus = $actor->getKey();
		} else {
			$this->focus = $actor;
		}

		return $this;
	}

	public function reset(): self {
		$this->selection = collect();

		return $this;
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Give the focused actor ID
	 *
	 * @return int
	 */
	public function getFocus(): int {
		return $this->focus;
	}

	/**
	 * List all the selected actor ids
	 *
	 * @return Collection<int>
	 */
	public function getSelection(): Collection {
		return $this->selection->unique()->whereNotNull();
	}

	/**
	 * List all the selected actors
	 *
	 * @return EloquentCollection<Actor>
	 */
	public function getActors(): EloquentCollection {
		return Actor::findMany($this->getSelection());
	}

	/*
	|--------------------------------------------------------------------------
	| Internal Selectors
	|--------------------------------------------------------------------------
	*/

	/**
	 * @param int $focus
	 * @return int|null
	 */
	public static function getParent(int $focus): int|null {
		return DB::table(static::CLOSURES_TABLE)
		         ->where("descendant_id", "=", $focus)
		         ->where("depth", "=", 1)
		         ->limit(1)
		         ->pluck("ancestor_id")
		         ->first();
	}

	/**
	 * @param int $focus
	 * @return Collection<int>
	 */
	public static function getParents(int $focus): Collection {
		return DB::table(static::CLOSURES_TABLE)
		         ->where("descendant_id", "=", $focus)
		         ->where("depth", ">", 0)
		         ->orderBy("depth", 'desc')
		         ->pluck("ancestor_id");
	}

	/**
	 * @param int  $focus
	 * @param bool $recursive
	 * @return Collection<int>
	 */
	public static function getChildren(int $focus, bool $recursive): Collection {
		return DB::table(static::CLOSURES_TABLE)
		         ->where("ancestor_id", "=", $focus)
		         ->when($recursive, function (Builder $query) {
			         $query->where("depth", ">", 0);
		         }, function (Builder $query) {
			         $query->where("depth", "=", 1);
		         })
		         ->pluck("descendant_id");
	}

	/**
	 * @param int  $focus
	 * @param bool $getChildren
	 * @return Collection<int>
	 */
	public static function getSiblings(int $focus, bool $getChildren): Collection {
		$parent = static::getParent($focus);

		// Check if we have a parent, if not, we are at the root of the tree and sibling querying works differently
		if (!$parent) {
			$actors = DB::table("actors_details")
			            ->whereNull("parent_id")
			            ->where("id", "<>", $focus)
			            ->pluck("id");
		} else {
			// List the parent's children, excluding the current focus
			$actors = static::getChildren($parent, recursive: false)
			                ->where(null, '!==', $focus);
		}

		// If the siblings children are requested, load them as well
		if ($getChildren) {
			$actors = $actors->merge(
				DB::table(static::CLOSURES_TABLE)
				  ->whereIn("ancestor_id", $actors)
				  ->where("depth", ">", "0")
				  ->pluck("descendant_id")
			);
		}

		return $actors;
	}

	/**
	 * @param int  $focus
	 * @param bool $getChildren
	 * @return Collection<int>
	 */
	public static function getShared(int $focus, bool $getChildren): Collection {
		$actors = DB::table(static::SHARES_TABLE)
		            ->where("shared_with_id", "=", $focus)
		            ->pluck("sharer_id");

		if ($getChildren) {
			$actors = $actors->merge(
				DB::table(static::CLOSURES_TABLE)
				  ->whereIn("ancestor_id", $actors)
				  ->where("depth", ">", "0")
				  ->pluck("descendant_id")
			);
		}

		return $actors;
	}

	public static function getContracts(int|null $salespersonId = null, bool $getChildren = true) {
		$actors = DB::table(static::CONTRACTS_TABLE)
		            ->when("salesperson_id", function (Builder $query) use ($salespersonId) {
			            $query->where("salesperson_id", "=", $salespersonId);
		            })
		            ->pluck("group_id")
		            ->unique()
		            ->whereNotNull();

		if ($getChildren) {
			$actors = $actors->merge(
				DB::table(static::CLOSURES_TABLE)
				  ->whereIn("ancestor_id", $actors)
				  ->where("depth", ">", "0")
				  ->pluck("descendant_id")
			);
		}

		return $actors;
	}

	/*
	|--------------------------------------------------------------------------
	| Selectors
	|--------------------------------------------------------------------------
	*/

	public function selectFocus(): self {
		$this->selection->push($this->focus);

		return $this;
	}

	public function selectParent(): self {
		$parent = static::getParent($this->focus);

		// Check if we have a parent, if not, short-circuit to prevent adding a null value to the selection
		if (!$parent) {
			return $this;
		}

		$this->selection->push($parent);

		return $this;
	}

	public function selectParents(): self {
		$this->selection = $this->selection->merge(
			static::getParents($this->focus),
		);

		return $this;
	}

	public function selectChildren(bool $recursive = false): self {
		$this->selection = $this->selection->merge(
			static::getChildren($this->focus, $recursive),
		);

		return $this;
	}

	public function selectSiblings(bool $getChildren = false): self {
		$this->selection = $this->selection->merge(
			static::getSiblings($this->focus, $getChildren),
		);

		return $this;
	}

	public function selectShared(bool $getChildren = false): self {
		$this->selection = $this->selection->merge(
			static::getShared($this->focus, $getChildren),
		);

		return $this;
	}

	public function selectContracts(bool $all = false, bool $getChildren = false) {
		$this->selection = $this->selection->merge(
			static::getContracts($all ? null : $this->focus, $getChildren),
		);

		return $this;
	}
}
