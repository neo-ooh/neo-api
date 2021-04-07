<?php

namespace Neo\Services\News;

use Illuminate\Support\Collection;

interface NewsService {
    /**
     * Fetch new articles and remove old ones.
     * @return void
     */
    public function updateRecords(): void;

    public function getRecords(int $categoryId): Collection;
}
