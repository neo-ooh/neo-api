<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledFlight.php
 */

namespace Neo\Resources\CampaignPlannerPlan\CompiledPlan;

use Carbon\Carbon;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile\CPCompiledMobileFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHFlight;
use Neo\Resources\FlightType;
use Spatie\LaravelData\Data;

class CPCompiledFlight extends Data {
	public function __construct(
		public string     $id,
		public string     $name,
		public FlightType $type,

		public Carbon     $start_date,
		public Carbon     $end_date,

		public int        $order,
		public bool       $send,
		public bool       $is_compiled,

		public Carbon     $updated_at,

		public array      $rawFlight,
	) {
	}


	public function isOOHFlight() {
		return in_array($this->type, [FlightType::Guaranteed, FlightType::Bonus, FlightType::BUA]);
	}

	public function getAsOOHFlight() {
		return CPCompiledOOHFlight::from($this->rawFlight);
	}

	public function isMobileFlight() {
		return $this->type === FlightType::Mobile;
	}

	public function getAsMobileFlight() {
		return CPCompiledMobileFlight::from($this->rawFlight);
	}

	public function getWeekLength() {
		$diffInDays = ($this->start_date->diffInDays($this->end_date, absolute: true) + 1) / 7;
		return round($diffInDays, 1);
	}
}
