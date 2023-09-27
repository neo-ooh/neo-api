<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ViewModelBackedBuilder.php
 */

namespace Neo\Models\Utils;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 *
 */
class ViewModelBackedBuilder extends Builder {
	public function setModel(Model $model) {
		parent::setModel($model);

		return $this;
	}

	protected function setForRead(): void {
		$this->query->from($this->model->getReadTable());
	}

	protected function setForWrite(): void {
		$this->query->from($this->model->getWriteTable());

		// Relations store the table name with the column name
		// We therefore need to rename the where who uses the read table to the write table
		// otherwise actions on the related lines `->related()->delete()` will have columns referencing
		// the read table
		$wheres      = $this->query->wheres;
		$writeWheres = [];

		foreach ($wheres as $where) {
			if (array_key_exists("column", $where) && is_string($where["column"])) {
				$writeWheres[] = [
					...$where,
					"column" => str_replace($this->model->getReadTable(), $this->model->getWriteTable(), $where["column"]),
				];
			} else {
				$writeWheres[] = $where;
			}
		}

		$this->query->wheres = $writeWheres;
	}

	protected function writeStmt(Closure $callback) {
		// Switch the builder `from` to the write table
		$this->setForWrite();

		// Execute the statement
		$result = $callback();

		// Switch back to the read view
		$this->setForRead();

		return $result;
	}

	public function insert(array $values) {
		return $this->writeStmt(fn() => parent::insert($values));
	}

	public function insertGetId(array $values, $sequence = null) {
		return $this->writeStmt(fn() => parent::insertGetId($values, $sequence));
	}

	public function insertOrIgnore(array $values) {
		return $this->writeStmt(fn() => parent::insertOrIgnore($values));
	}

	public function insertUsing(array $columns, $query) {
		return $this->writeStmt(fn() => parent::insertUsing($columns, $query));
	}

	public function update(array $values) {
		return $this->writeStmt(fn() => parent::update($values));
	}

	public function updateFrom(array $values) {
		return $this->writeStmt(fn() => parent::updateFrom($values));
	}

	public function upsert(array $values, $uniqueBy, $update = null) {
		return $this->writeStmt(fn() => parent::upsert($values, $uniqueBy, $update));
	}

	public function touch($column = null) {
		return $this->writeStmt(fn() => parent::touch($column));
	}

	public function truncate() {
		$this->writeStmt(fn() => parent::truncate());
	}

	public function increment($column, $amount = 1, array $extra = []) {
		return $this->writeStmt(fn() => parent::increment($column, $amount, $extra));
	}

	public function decrement($column, $amount = 1, array $extra = []) {
		return $this->writeStmt(fn() => parent::decrement($column, $amount, $extra));
	}

	public function delete() {
		return $this->writeStmt(fn() => parent::delete());
	}

	public function forceDelete() {
		return $this->writeStmt(fn() => parent::forceDelete());
	}
}
