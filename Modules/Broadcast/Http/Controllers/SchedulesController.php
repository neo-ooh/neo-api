<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SchedulesController.php
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Exceptions\BaseException;
use Neo\Http\Controllers\Controller;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleContentAnymoreException;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleIncompleteContentException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentFormatAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentLengthAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleBroadcastDaysException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleDatesException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleTimesException;
use Neo\Modules\Broadcast\Http\Requests\Schedules\DestroyScheduleRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\ListPendingSchedulesRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\ListSchedulesByIdsRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\ListSchedulesRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\StoreScheduleRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\UpdateScheduleRequest;
use Neo\Modules\Broadcast\Jobs\Schedules\DeleteScheduleJob;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\ScheduleReview;
use Neo\Modules\Broadcast\Utils\ScheduleUpdater;
use Neo\Modules\Broadcast\Utils\ScheduleValidator;
use Neo\Modules\Properties\Models\Product;
use Ramsey\Uuid\Uuid;

class SchedulesController extends Controller {
	public function index(ListSchedulesRequest $request): Response {
		$query = Schedule::query();

		if ($request->has("batch_id")) {
			$query->where("batch_id", "=", $request->input("batch_id"));
		}

		if ($request->input("product_id")) {
			// To load the schedules associated with a product, we need to know the format of the product
			$product = Product::query()->find($request->input("product_id"));

			$query->whereHas("campaign", function (Builder $query) use ($product) {
				$query->whereHas("locations", function (Builder $query) use ($product) {
					$query->whereHas("products", function (Builder $query) use ($product) {
						$query->where("id", "=", $product->getKey());
					});
				})->orWhereHas("products", function (Builder $query) use ($product) {
					$query->where("id", "=", $product->getKey());
				});
			});
			$query->whereHas("contents", function (Builder $query) use ($product) {
				$query->whereHas("layout", function (Builder $query) use ($product) {
					$query->whereHas("formats", function (Builder $query) use ($product) {
						$query->where("id", "=", $product->format_id ?? $product->category->format_id);
					});
				});
				$query->whereNotExists(function (\Illuminate\Database\Query\Builder $query) use ($product) {
					$query->from("schedule_content_disabled_formats");
					$query->where("schedule_content_disabled_formats.schedule_content_id", "=", DB::raw("schedule_contents.id"));
					$query->where("schedule_content_disabled_formats.format_id", "=", $product->format_id ?? $product->category->format_id);
				});
			});
		}

		if ($request->input("current", false)) {
			$query->where("start_date", "<=", \Carbon\Carbon::now())
			      ->where("end_date", ">=", Carbon::now());
		}

		if ($request->input("past", false)) {
			$query->where("end_date", "<", Carbon::now())->withTrashed();
		}

		if ($request->input("future", false)) {
			$query->where("start_date", ">", Carbon::now());
		}

		/** @var Collection<Schedule> $schedules */
		$schedules = $query->get();

		return new Response($schedules->loadPublicRelations());
	}

	public function byIds(ListSchedulesByIdsRequest $request): Response {
		$schedules = Schedule::query()->findMany($request->input("ids"));

		return new Response($schedules->loadPublicRelations());
	}

	public function show(ListSchedulesByIdsRequest $request, Schedule $schedule): Response {
		return new Response($schedule->loadPublicRelations());
	}

	/**
	 * @param ListPendingSchedulesRequest $request
	 *
	 * @return Response
	 */
	public function pending(ListPendingSchedulesRequest $request): Response {
		// List all accessible schedules pending review
		// A schedule pending review is a schedule who is locked, is not pre-approved, and who doesn't have any reviews

		/** @var Collection<integer> $users */
		$users     = Auth::user()?->getAccessibleActors(ids: true);
		$campaigns = Campaign::query()->whereIn("parent_id", $users)->pluck("id");

		$schedules = Schedule::query()
		                     ->select('schedules.*')
		                     ->join('schedule_details', 'schedule_details.schedule_id', '=', "schedules.id")
		                     ->whereIn("schedules.campaign_id", $campaigns)
		                     ->where("schedules.is_locked", "=", 1)
		                     ->where("schedule_details.is_approved", "=", 0)
		                     ->where('schedule_details.is_rejected', '=', 0)
		                     ->whereNotExists(fn($query) => $query->select(DB::raw(1))
		                                                          ->from('schedule_reviews')
		                                                          ->whereRaw('schedule_reviews.schedule_id = schedules.id'))
		                     ->get();

		return new Response($schedules->loadPublicRelations());
	}

	/**
	 * @param StoreScheduleRequest $request
	 * @return Response
	 * @throws BaseException
	 * @throws CannotScheduleContentAnymoreException
	 * @throws CannotScheduleIncompleteContentException
	 * @throws IncompatibleContentFormatAndCampaignException
	 * @throws IncompatibleContentLengthAndCampaignException
	 * @throws InvalidScheduleBroadcastDaysException
	 * @throws InvalidScheduleDatesException
	 * @throws InvalidScheduleTimesException
	 */
	public function store(StoreScheduleRequest $request): Response {
		/** @var Collection<Content> $contents */
		$contents = Content::query()->findMany($request->input("contents"));

		/** @var Collection<Campaign> $campaigns */
		$campaigns = Campaign::query()->whereIn("id", $request->input("campaigns"))->get();

		$startDate     = Carbon::createFromFormat("Y-m-d", $request->input("start_date"));
		$startTime     = Carbon::createFromFormat("H:i:s", $request->input("start_time"));
		$endDate       = Carbon::createFromFormat("Y-m-d", $request->input("end_date"));
		$endTime       = Carbon::createFromFormat("H:i:s", $request->input("end_time"));
		$broadcastDays = $request->input("broadcast_days");
		$broadcastTags = $request->input("tags", []);

		$asBundle = (bool)$request->input("schedule_as_bundle");

		// Prepare the schedule
		$schedule                 = new Schedule();
		$schedule->owner_id       = Auth::id();
		$schedule->start_date     = $startDate;
		$schedule->start_time     = $startTime;
		$schedule->end_date       = $endDate;
		$schedule->end_time       = $endTime;
		$schedule->broadcast_days = $broadcastDays;
		$schedule->is_locked      = $request->input('send_for_review');

		// If we are provided a batch id, or if there is more than one campaign,
		// or multiple contents to schedule individually, assign a batch id to the created schedules
		if ($request->has("batch_id") || $campaigns->count() > 1 || ($contents->count() > 0 && !$asBundle)) {
			$schedule->batch_id = $request->input("batch_id", Uuid::uuid4());
		}

		/** @var array<Schedule> $schedules */
		$schedules = [];

		// Validate the content and scheduling fit all the selected campaigns
		$validator = new ScheduleValidator();
		$forceFit  = $request->input("force", false);

		try {
			DB::beginTransaction();

			foreach ($campaigns as $campaign) {
				// List contents that match the campaign
				$campaignContents = [];
				foreach ($contents as $content) {
					try {
						$validator->validateContentFitCampaign($content, $campaign);
						$campaignContents[] = $content;
					} catch (BaseException) {
						continue;
					}
				}

				if (count($campaignContents) === 0) {
					// If no content match, skip
					continue;
				}

				// Create the schedule for the campaign
				$campaignSchedule              = clone $schedule;
				$campaignSchedule->campaign_id = $campaign->id;

				if ($forceFit) {
					[$schedule->start_date,
					 $schedule->start_time,
					 $schedule->end_date,
					 $schedule->end_time,
					 $schedule->broadcast_days,
					] = $validator->forceFitSchedulingInCampaign(
						campaign : $campaign,
						startDate: $startDate,
						startTime: $startTime,
						endDate  : $endDate,
						endTime  : $endTime,
						weekdays : $broadcastDays
					);
				} else {
					$validator->validateSchedulingFitCampaign(
						campaign : $campaign,
						startDate: $startDate,
						startTime: $startTime,
						endDate  : $endDate,
						endTime  : $endTime,
						weekdays : $broadcastDays
					);
				}

				// Depending if we want to schedule each content individually or together, the next steps are not the same
				if ($asBundle) {
					// Schedule is validated for campaign, store it
					$campaignSchedule->order = $campaign->schedules()->count();
					$campaignSchedule->save();

					// Attach the contents to the schedule
					$campaignSchedule->contents()->attach($contents->pluck("id"));
					$campaignSchedule->broadcast_tags()->sync($broadcastTags);

					$schedules[] = $campaignSchedule;
				} else { // if (!$asBundle) -> Schedule each content in a separate bundle
					foreach ($contents as $content) {
						// Schedule is validated for campaign, store it
						$campaignContentSchedule        = clone $campaignSchedule;
						$campaignContentSchedule->order = $campaign->schedules()->count();
						$campaignContentSchedule->save();

						// Attach the content to the schedule
						$campaignContentSchedule->contents()->attach($content->getKey());
						$campaignContentSchedule->broadcast_tags()->sync($broadcastTags);

						$schedules[] = $campaignContentSchedule;
					}
				}
			}

			// For each schedules, check if we need to lock it
			/**
			 * @var Schedule $schedule
			 */
			foreach ($schedules as $schedule) {
				// If the schedule is locked on creation, check if we should auto-approve it or warn someone to approve it
				if ($schedule->is_locked) {
					$schedule->locked_at = Date::now();
					$schedule->save();

					/** @var Actor $user */
					$user = Auth::user();

					if ($user->hasCapability(Capability::contents_review)) {
						$review              = new ScheduleReview();
						$review->reviewer_id = $user->getKey();
						$review->schedule_id = $schedule->getKey();
						$review->approved    = true;
						$review->message     = "[auto-approved]";
						$review->save();
					}
				}
			}

			DB::commit();
		} catch (BaseException $e) {
			DB::rollBack();

			throw $e;
		}

		// All schedules where created successfully, promote them
		foreach ($schedules as $schedule) {
			$schedule->promote();
		}

		return new Response(array_map(static fn(Schedule $schedule) => $schedule->getKey(), $schedules), 201);
	}

	public function updateWithCampaign(UpdateScheduleRequest $request, Campaign $campaign, Schedule $schedule) {
		return $this->update($request, $schedule);
	}

	/**
	 * @param UpdateScheduleRequest $request
	 * @param Schedule              $schedule
	 *
	 * @return Response
	 * @throws InvalidScheduleBroadcastDaysException
	 * @throws InvalidScheduleDatesException
	 * @throws InvalidScheduleTimesException
	 */
	public function update(UpdateScheduleRequest $request, Schedule $schedule): Response {
		$campaign      = $schedule->campaign;
		$startDate     = Carbon::createFromFormat("Y-m-d", $request->input("start_date"))->startOfDay();
		$startTime     = Carbon::createFromFormat("H:i:s", $request->input("start_time"));
		$endDate       = Carbon::createFromFormat("Y-m-d", $request->input("end_date"))->startOfDay();
		$endTime       = Carbon::createFromFormat("H:i:s", $request->input("end_time"));
		$broadcastDays = $request->input("broadcast_days");

		// Make sure the schedules dates can be edited
		if ($schedule->status !== ScheduleStatus::Draft && !Gate::allows(Capability::contents_review->value)) {
			return new Response(["You are not authorized to edit this schedule"], 403);
		}

		$updater = new ScheduleUpdater();
		$updater->setSchedule($schedule)
		        ->setCampaign($campaign)
		        ->update(
			        startDate: $startDate,
			        startTime: $startTime,
			        endDate  : $endDate,
			        endTime  : $endTime,
			        weekdays : $broadcastDays,
			        forceFit : false
		        );

		if (!$schedule->is_locked && $request->input("is_locked", false) && $schedule->end_date->isAfter(Carbon::now())) {
			$schedule->is_locked = true;
			$schedule->locked_at = Date::now();

			// If the schedule start date is set in the past, we move it to today
			if ($schedule->start_date->isBefore(Carbon::now()->startOfDay())) {
				$schedule->start_date = Carbon::now()->startOfDay();
			}

			/** @var Actor $user */
			$user = Auth::user();


			if ($user->hasCapability(Capability::contents_review)) {
				$review              = new ScheduleReview();
				$review->reviewer_id = $user->getKey();
				$review->schedule_id = $schedule->getKey();
				$review->approved    = true;
				$review->message     = "[auto-approved]";
				$review->save();
			}

			/*if ($schedule->contents->some("is_approved", "!==", true) && !Gate::allows(Capability::contents_review->value)) {
				SendReviewRequestEmail::dispatch($schedule->id);
			}*/
		}

		if ($request->input("remove_from_batch", false)) {
			$schedule->batch_id = null;
		}

		$schedule->save();

		if (Gate::allows(Capability::schedules_tags->value)) {
			$schedule->broadcast_tags()->sync($request->input("tags"));
		}

		$schedule->refresh();

		if (!$request->input("remove_from_batch", false)) {
			// Propagate the update to the associated BroadSign Schedule

			$schedule->promote();

			// If the schedule is part of a batch, we want to update the rest of it as well
			if ($schedule->batch_id !== null) {
				/** @var Collection<Schedule> $batchSchedules */
				$batchSchedules = Schedule::query()
				                          ->where("batch_id", "=", $schedule->batch_id)
				                          ->where("id", "<>", $schedule->getKey())
				                          ->with("campaign")
				                          ->get();

				foreach ($batchSchedules as $batchSchedule) {
					$updater->setSchedule($batchSchedule)
					        ->setCampaign($batchSchedule->campaign)
					        ->update(
						        startDate: $startDate,
						        startTime: $startTime,
						        endDate  : $endDate,
						        endTime  : $endTime,
						        weekdays : $broadcastDays,
						        forceFit : true
					        );

					if (Gate::allows(Capability::schedules_tags->value)) {
						$schedule->broadcast_tags()->sync($request->input("tags"));
					}

					$batchSchedule->promote();
				}
			}
		}

		return new Response($schedule->loadPublicRelations());
	}

	public function destroyWithCampaign(DestroyScheduleRequest $request, Campaign $campaign, Schedule $schedule) {
		return $this->destroy($request, $schedule);
	}

	/**
	 * @param DestroyScheduleRequest $request
	 * @param Schedule               $schedule
	 *
	 * @return Response
	 */
	public function destroy(DestroyScheduleRequest $request, Schedule $schedule): Response {
		$schedules = collect([$schedule]);


		if ($schedule->batch_id !== null && $request->input("delete_batch", false)) {
			$schedules = Schedule::query()->where("batch_id", "=", $schedule->batch_id)->get();
		}

		/** @var Schedule $s */
		foreach ($schedules as $s) {
			// If a schedule has not been reviewed, we want to completely remove it
			if ($s->status === ScheduleStatus::Draft || $s->status === ScheduleStatus::Pending) {
				$s->forceDelete();
				continue;
			}

			if ($s->status === ScheduleStatus::Rejected) {
				$s->delete();
				continue;
			}

			// If the schedule is approved, we check if it has started playing.
			// If it has started playing earlier than today, we set its end-date for yesterday, effectively stopping its
			// broadcast, but keeping it in the `expired` list
			if ($s->start_date < Carbon::now() && !$s->start_date->isToday()) {
				$s->end_date = Carbon::now()->subDay()->min($s->start_date);
				$s->save();
				$s->promote();

				continue;
			}

			// If the schedule started today, we change its end date to today, its end time to now, and we delete it
			if ($s->start_date < Carbon::now() && $s->start_date->isToday()) {
				$s->end_date   = Carbon::today();
				$s->end_time   = Carbon::now();
				$s->start_time = $s->start_time->isAfter($s->end_time) ? $s->end_time->clone()->subMinutes(1) : $s->start_time;
				$s->save();

				new DeleteScheduleJob($s->getKey());
				continue;
			}

			// Schedule has not started playing, delete it
			$s->delete();
		}

		return new Response($schedule);
	}
}
