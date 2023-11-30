<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeNewsRecordsCommand.php
 */

namespace Neo\Modules\Dynamics\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Neo\Modules\Dynamics\Models\NewsRecord;
use Neo\Modules\Dynamics\Services\News\NewsAdapter;

class SynchronizeNewsRecordsCommand extends Command {
	protected $signature = 'news:synchronize-records';

	protected $description = 'Synchronize news records from the provider to Connect';

	public function handle(): void {
		/** @var NewsAdapter $newsClient */
		$newsClient = app()->get(NewsAdapter::class);

		$articles = collect();

		foreach ($newsClient->listRecords() as $record) {
			$this->info($record->category_slug . ": " . $record->id . " - " . $record->headline);

			if ($record->media_path === null) {
				// Skip
				continue;
			}

			// Insert/update the record in the db
			$entry = NewsRecord::query()->updateOrCreate(
				[
					"cp_id" => $record->id,
					                                                                                                               "category" => $record->category_slug,
				], [
					"locale"   => str_starts_with($record->category_slug, "Fr"),
					"headline" => $record->headline,
					"date"     => $record->date,
					"media"    => $record->media_path,
				]);

			$articles[] = $entry;

			// If the articles come with a media, we check if we already have it
			if ($record->media_path === null) {
				continue;
			}

			// If a media already exist, do nothing
			if (Storage::disk("public")->exists($record->media_path)) {
				continue;
			}

			$entry->storeMediaStream($newsClient->getMediaStream($record));
		}

		$this->info("Cleaning up");

		// List all entries in our db that haven't been updated in this run
		$outdatedArticles = NewsRecord::query()->whereNotIn("id", $articles->pluck("id"))->get();

		// List all media attached to outdated articles
		$mediaPaths = $outdatedArticles->filter(fn(NewsRecord $record) => $record->media !== null)
		                               ->map(fn(NewsRecord $record) => $record->media_path);

		Storage::disk("public")->delete($mediaPaths);

		// And delete outdated articles from the DB
		NewsRecord::query()->whereNotIn("id", $articles->pluck("id"))->delete();
	}
}
