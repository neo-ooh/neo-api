<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NewsService.php
 */

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
