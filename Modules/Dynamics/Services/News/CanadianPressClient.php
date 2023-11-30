<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CanadianPressClient.php
 */

namespace Neo\Modules\Dynamics\Services\News;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Neo\Models\NewsRecord;
use Storage;

class CanadianPressClient implements NewsAdapter {
	protected Filesystem|null $client = null;

	public function getClient(): Filesystem {
		if (is_null($this->client)) {
			$this->client = Storage::disk(config("services.canadian-press.disk"));
		}

		return $this->client;
	}

	/**
	 * @inheritDoc
	 */
	public function listRecords(): iterable {
		// Start by getting our access to the Canadian Press FTP
		$cpStorage = $this->getClient();

		// List all available directories/categories
		$categories = $cpStorage->directories();

		foreach ($categories as $category) {
			// List all files for the category, and keep only the articles (XML files)
			$files    = $cpStorage->files($category);
			$articles = array_filter($files, fn(string $file) => strpos($file, '.xml'));

			foreach ($articles as $articlePath) {
				// Load the article and try to parse it
				try {
					$article = simplexml_load_string($cpStorage->get($articlePath));
				} catch (Exception $e) {
					// Error while parsing, ignore article
					dump($e->getMessage());
					continue;
				}

				if (!$article) {
					continue;
				}

				// Build and return a NewsItem for the article
				// Validate the media first
				$medias = $article->xpath("//media-reference/@source");
				$media  = null;

				if (count($medias) > 0) {
					$mediaPath = "$category/" . $medias[0];
					$media     = in_array($mediaPath, $files, true) ? $mediaPath : null;
				}

				yield new NewsItem(
					id           : (string)$article->xpath("//doc-id/@id-string")[0],
					category_slug: $category,
					headline     : (string)$article->xpath("//hl1")[0],
					date         : Carbon::parse((string)$article->xpath("//story.date/@norm")[0]),
					media_path   : $media
				);
			}
		}

		return [];

//		foreach ($category["categories"] as $subject) {
//			// Get all fields (media & records) on the canadian FTP for the current subject and parse them
//			$cpFiles = $cpStorage->files($subject);
//
//			// Filter to only get articles (XML Files)
//			$cpRecords = array_filter($cpFiles, static function ($item) {
//				return strpos($item, '.xml');
//			});
//
//			foreach ($cpRecords as $record) {
//				try {
//					$xmlRecord = @simplexml_load_string($cpStorage->get($record));
//				} catch (Exception $e) {
//					// If we cannot parse a record, just ignore it
//					continue;
//				}
//
//				if (!$xmlRecord) {
//					// Ignore record on parse error
//					continue;
//				}
//
//				// Extract article's infos
//				try {
//					$articleInfos = [
//						"cp_id"    => (string)$xmlRecord->xpath("//doc-id/@id-string")[0],
//						"date"     => Date::createFromTimestamp(strtotime((string)$xmlRecord->xpath("//story.date/@norm")[0])),
//						"headline" => (string)$xmlRecord->xpath("//hl1")[0],
//						"media"    => $xmlRecord->xpath("//media-reference/@source"),
//						"subject"  => $subject,
//						"locale"   => $category["locale"],
//					];
//				} catch (Exception $e) {
//					// ignore record on error
//					continue;
//				}
//
//				if (count($articleInfos["media"]) > 0) {
//					$mediaName             = "$subject/" . $articleInfos["media"][0];
//					$articleInfos["media"] = in_array($mediaName, $cpFiles, true) ? $mediaName : null;
//				} else {
//					$articleInfos["media"] = null;
//				}
//
//				// Insert/Update the article in the DDB
//				/** @var NewsRecord $record */
//				$record = NewsRecord::query()->updateOrCreate(
//					[
//						'cp_id'   => $articleInfos['cp_id'],
//						'subject' => $articleInfos['subject'],
//					],
//					$articleInfos
//				);
//
//				$this->handleMedia($record, $cpStorage);
//
//				// Keep our record ID for cleanup
//				$activeRecords[] = $record->id;
//			}
//		}
//
//
//		// Now that all records have been imported from the DDB, we need to cleanup old records and their medias
//		$oldRecords = NewsRecord::query()->whereNotIn("id", $activeRecords)->get();
//
//		foreach ($oldRecords as $record) {
//			if ($record->media) {
//				Storage::disk("public")->delete(config("services.canadian-press.storage.path") . $record->media);
//			}
//
//			$record->delete();
//		}

		// Done
	}

	/*	protected function handleMedia(NewsRecord $record, Filesystem $cpDisk): void {
			if (!$record["media"]) {
				// No media for article, do nothing
				return;
			}

			$mediaPath = config("services.canadian-press.storage.path") . $record->media;

			// Check if the media already exist
			if (Storage::disk("public")->exists($mediaPath)) {
				return;
			}

			// Copy the media to our server
			try {
				Storage::disk("public")->writeStream(
					$mediaPath,
					$cpDisk->readStream($record->media)
				);
			} catch (Exception) {
				// Could not get media, ignore
				$record->media = null;
				$record->save();
				return;
			}

			// Get and store the media dimensions
			$contents = Storage::disk("public")->get($mediaPath);
			$im       = imagecreatefromstring($contents);

			$record->media_width  = imagesx($im);
			$record->media_height = imagesy($im);
			$record->save();
		}*/

	/**
	 * Return a stream for the media of the given news item
	 *
	 * @param NewsItem $item
	 * @return null|resource
	 */
	public function getMediaStream(NewsItem $item) {
		if ($item->media_path === null) {
			return null;
		}

		return $this->getClient()->readStream($item->media_path);
	}
}
