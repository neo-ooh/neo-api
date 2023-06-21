<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Layout;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Properties\Models\Product;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     * @return void
     * @throws Exception
     */
    public function handle() {
        $productId = 617;
        $product   = Product::query()->find($productId);
        $format    = Format::query()->find($product->format_id ?? $product->category->format_id);
        $layoutIds = $format->layouts->pluck("id");

        $layouts = Layout::query()->whereHas("formats", function (Builder $query) use ($product) {
            $query->where("id", $product->format_id ?? $product->category->format_id);
        })->get();

        $query = Schedule::query();
        $query->whereHas("campaign", function (Builder $query) use ($productId) {
            $query->whereHas("locations", function (Builder $query) use ($productId) {
                $query->whereHas("products", function (Builder $query) use ($productId) {
                    $query->where("id", "=", $productId);
                });
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

        dump($query->toSql());
    }
}
