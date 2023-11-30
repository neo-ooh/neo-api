<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NewsAdapter.php
 */

namespace Neo\Modules\Dynamics\Services\News;

interface NewsAdapter {
	/**
	 * Fetch new articles and remove old ones.
	 *
	 * @return void
	 */
//	public function updateRecords(): void;

//	public function getRecords(int $categoryId): Collection;

	/**
	 * List all news items available on the server
	 *
	 * @return iterable<NewsItem>
	 */
	public function listRecords(): iterable;

	/**
	 * Return a stream for the media of the given news item
	 *
	 * @param NewsItem $item
	 * @return mixed
	 */
	public function getMediaStream(NewsItem $item);
}
