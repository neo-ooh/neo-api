<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPPlan.php
 */

namespace Neo\Resources\CampaignPlannerPlan;

class CPPlan {
	public CPPlanMeta|null $meta = null;
	public CPPlanRoot|null $plan = null;

	protected function __construct(
		protected array $rawMeta,
		protected array $rawPlan
	) {
	}

	public static function fromRaw(string $rawPlan) {
		$data = json_decode($rawPlan, associative: true);

		return new static(
			rawMeta: $data["_meta"],
			rawPlan: $data["plan"]
		);
	}

	/**
	 * @return CPPlanMeta
	 */
	public function getMeta(): CPPlanMeta {
		if ($this->meta === null) {
			$this->meta = CPPlanMeta::from($this->rawMeta);
		}

		return $this->meta;
	}

	/**
	 * @return CPPlanRoot
	 */
	public function getPlan(): CPPlanRoot {
		if ($this->plan === null) {
			$this->plan = CPPlanRoot::from($this->rawPlan);
		}

		return $this->plan;
	}

	public function toJson(): string {
		$meta = $this->meta ?? $this->rawMeta;
		$plan = $this->plan ?? $this->rawPlan;

		return json_encode([
			                   "_meta" => $meta,
			                   "plan"  => $plan,
		                   ], JSON_UNESCAPED_UNICODE);
	}
}
