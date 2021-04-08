<?php

namespace Neo\Services\News;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Neo\Models\NewsRecord;
use Storage;

class CanadianPressInterface implements NewsService {
    public const CATEGORY_NATIONAL = 1;
    public const CATEGORY_INTL = 2;
    public const CATEGORY_SPORTS = 3;
    public const CATEGORY_BUSINESS = 4;
    public const CATEGORY_ENTERTAINMENT = 5;
    public const CATEGORY_VARIETY = 6;
    public const CATEGORY_FR_NEWS = 7;
    public const CATEGORY_FR_VARIETY = 8;
    public const CATEGORY_FR_SPORTS = 9;

    public const CATEGORIES = [
        self::CATEGORY_NATIONAL      => [
            "name"     => "National News",
            "locale"   => "en",
            "subjects" => ["national"],
        ],
        self::CATEGORY_INTL          => [
            "name"     => "International News",
            "locale"   => "en",
            "subjects" => ["world"],
        ],
        self::CATEGORY_SPORTS        => [
            "name"     => "Sports",
            "locale"   => "en",
            "subjects" => ["sports", "hockey"],
        ],
        self::CATEGORY_BUSINESS      => [
            "name"     => "Business",
            "locale"   => "en",
            "subjects" => ["business"],
        ],
        self::CATEGORY_ENTERTAINMENT => [
            "name"     => "Entertainment",
            "locale"   => "en",
            "subjects" => ["oddities", "entertainment"],
        ],
        self::CATEGORY_VARIETY       => [
            "name"     => "Variety",
            "locale"   => "en",
            "subjects" => ["health", "consumerTech", "environment"],
        ],
        self::CATEGORY_FR_NEWS       => [
            "name"     => "News",
            "locale"   => "fr",
            "subjects" => ["FrNational", "FrWorld", "FrBusiness"],
        ],
        self::CATEGORY_FR_VARIETY    => [
            "name"     => "Variety",
            "locale"   => "fr",
            "subjects" => ["FrHealth", "FrEnvironment"],
        ],
        self::CATEGORY_FR_SPORTS     => [
            "name"     => "Sports",
            "locale"   => "fr",
            "subjects" => ["FrSport", "FrMedia"],
        ],
    ];

    /**
     * @inheritDoc
     */
    public function updateRecords(): void {
        // Start by getting our access to the Canadian Press FTP
        $cpStorage = Storage::disk(config("services.canadian-press.disk"));

        $activeRecords = [];

        // Update each subject in each category
        foreach (self::CATEGORIES as $category) {
            foreach ($category["subjects"] as $subject) {
                // Get all fields (media & records) on the canadian FTP for the current subject and parse them
                $cpFiles = $cpStorage->files($subject);

                // Filter to only get articles (XML Files)
                $cpRecords = array_filter($cpFiles, function ($item) {
                    return strpos($item, '.xml');
                });

                foreach ($cpRecords as $record) {
                    try {
                        $xmlRecord = simplexml_load_string($cpStorage->get($record));
                    } catch (Exception $e) {
                        // If we cannot parse a record, just ignore it
                        continue;
                    }

                    // Extract article's infos
                    $articleInfos = [
                        "cp_id"    => (string)$xmlRecord->xpath("//doc-id/@id-string")[0],
                        "date"     => Date::createFromTimestamp(strtotime((string)$xmlRecord->xpath("//story.date/@norm")[0])),
                        "headline" => (string)$xmlRecord->xpath("//hl1")[0],
                        "media"    => $xmlRecord->xpath("//media-reference/@source"),
                        "subject"  => $subject,
                        "locale"   => $category["locale"],
                    ];

                    if (count($articleInfos["media"]) > 0) {
                        $mediaName             = "$subject/" . $articleInfos["media"][0];
                        $articleInfos["media"] = in_array($mediaName, $cpFiles, true) ? $mediaName : null;
                    } else {
                        $articleInfos["media"] = null;
                    }

                    // Insert/Update the article in the DDB
                    /** @var NewsRecord $record */
                    $record = NewsRecord::query()->updateOrCreate(
                        [
                            'cp_id'   => $articleInfos['cp_id'],
                            'subject' => $articleInfos['subject']
                        ],
                        $articleInfos
                    );

                    $this->handleMedia($record, $cpStorage);

                    // Keep our record ID for cleanup
                    $activeRecords[] = $record->id;
                }
            }
        }

        // Now that all records have been imported from the DDB, we need to cleanup old records and their medias
        $oldRecords = NewsRecord::query()->whereNotIn("id", $activeRecords)->get();

        foreach ($oldRecords as $record) {
            if ($record->media) {
                Storage::disk("public")->delete(config("services.canadian-press.storage.path") . $record->media);
            }

            $record->delete();
        }

        // Done
    }

    protected function handleMedia(NewsRecord $record, Filesystem $cpDisk): void {
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
        } catch (Exception $e) {
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
    }

    public function getRecords(int $categoryId): Collection {
        if ($categoryId < 1 || $categoryId > 9) {
            return collect();
        }

        $category = self::CATEGORIES[$categoryId];

        return NewsRecord::query()->whereIn("subject", $category["subjects"])->get()->values();
    }
}
