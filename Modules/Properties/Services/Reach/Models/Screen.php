<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Screen.php
 */

namespace Neo\Modules\Properties\Services\Reach\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\NamedIdentityAttribute;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenAspectRatio;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenBidFloor;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenPublisher;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenResolution;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\ScreenVenueType;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Services\API\Endpoint;

/**
 * @property int                                $id
 * @property string                             $name
 * @property string                             $device_id
 *
 * @property Collection<ScreenVenueType>        $venue_types
 *
 * @property string                             $address
 * @property array                              $geo
 * @property float                              $longitude
 * @property float                              $latitude
 * @property NamedIdentityAttribute             $time_zone
 *
 * @property ScreenResolution                   $resolution
 * @property ScreenAspectRatio                  $aspect_ratio
 * @property array                              $screen_types
 * @property float|null                         $diagonal_size
 * @property string                             $diagonal_size_units
 *
 * @property float                              $max_ad_duration
 * @property float                              $min_ad_duration
 *
 * @property Collection<NamedIdentityAttribute> $allowed_ad_types
 * @property bool                               $allows_motion
 * @property boolean                            $is_audio
 *
 * @property float|null                         $floor_cpm
 *
 * @property array                              $iad_categories
 *
 * @property float                              $average_weekly_impressions
 * @property Collection<NamedIdentityAttribute> $audience_data_sources
 * @property string                             $demography_type
 * @property null                               $males
 * @property int|null                           $males_12
 * @property int|null                           $males_18
 * @property int|null                           $males_25
 * @property int|null                           $males_35
 * @property int|null                           $males_45
 * @property int|null                           $males_55
 * @property int|null                           $males_65
 * @property null                               $females
 * @property int|null                           $females_12
 * @property int|null                           $females_18
 * @property int|null                           $females_25
 * @property int|null                           $females_35
 * @property int|null                           $females_45
 * @property int|null                           $females_65
 *
 * @property Collection<NamedIdentityAttribute> $tags
 *
 * @property string                             $transact_status
 * @property bool                               $ox_enabled
 * @property float                              $total
 * @property array                              $audience_segments
 * @property string                             $internal_publisher_screen_id
 * @property Collection<ScreenBidFloor>         $bid_floors
 * @property array                              $transact_status_errors_ox
 * @property null                               $dma
 *
 * @property string|null                        $hivestack_id
 * @property string|null                        $vistar_id
 *
 * @property boolean                            $is_active
 * @property ScreenPublisher                    $publisher
 *
 * @property string|null                        $screen_img_url
 *
 * @property float|null                         $bearing
 * @property string                             $bearing_direction
 *
 * @property string                             $transact_status_ox
 * @property array                              $transact_status_errors
 * @property int                                $connectivity
 */
class Screen extends ReachModel {
    public string $key = "id";

    protected array $casts = [
        "allowed_ad_types"      => NamedIdentityAttribute::class,
        "aspect_ratio"          => ScreenAspectRatio::class,
        "audience_data_sources" => NamedIdentityAttribute::class,
        "bid_floors"            => ScreenBidFloor::class,
        "publisher"             => ScreenPublisher::class,
        "resolution"            => ScreenResolution::class,
        "tags"                  => NamedIdentityAttribute::class,
        "time_zone"             => NamedIdentityAttribute::class,
        "venue_types"           => ScreenVenueType::class,
    ];

    public function toInventoryResourceId(int $inventoryId): InventoryResourceId {
        return new InventoryResourceId(
            inventory_id: $inventoryId,
            external_id : $this->getKey(),
            type        : InventoryResourceType::Product,
            context     : [
                              "venue_type_id"        => $this->venue_types[0]->id ?? 0,
                              "location_external_id" => explode(":", $this->device_id)[1],
                          ],
        );
    }

    public function fillImpressions(array $weekdaysSpotImpressions) {
        // First, build a csv
        $csv = "";
        // Headers
        $csv .= "screen_id,start_date,end_date,start_time,end_time,mon,tue,wed,thu,fri,sat,sun,demography_type,total,males,females,males_12,males_18,males_25,males_35,males_45,males_55,males_65,females_12,females_18,females_25,females_35,females_45,females_55,females_65\n";

        foreach ($weekdaysSpotImpressions as $weekDay => $spotImpressions) {
            // broadsign.com:example,2017-01-01,2017-12-31,12:00:01,18:00:00,1,1,1,1,0,0,0,basic,50,,,,,,,,,,,,,,,,
            $csv .= $this->device_id . ",";
            $csv .= Carbon::now()->startOfYear()->toDateString() . ",";
            $csv .= Carbon::now()->addYear()->endOfYear()->toDateString() . ",";
            $csv .= "00:00:00,";
            $csv .= "23:59:59,";
            $csv .= ($weekDay === 1 ? 1 : 0) . ","; // Monday
            $csv .= ($weekDay === 2 ? 1 : 0) . ","; // Tuesday
            $csv .= ($weekDay === 3 ? 1 : 0) . ","; // Wednesday
            $csv .= ($weekDay === 4 ? 1 : 0) . ","; // Thursday
            $csv .= ($weekDay === 5 ? 1 : 0) . ","; // Friday
            $csv .= ($weekDay === 6 ? 1 : 0) . ","; // Saturday
            $csv .= ($weekDay === 7 ? 1 : 0) . ","; // Sunday
            $csv .= "basic,";
            $csv .= max(1, floor($spotImpressions * 10000) / 10000) . ",";   // Impressions per play rounded to 4 decimals
            $csv .= ",,,,,,,,,,,,,,,\n";                                     // Columns not used
        }

        $endpoint = Endpoint::post("/schedules/")->multipart();
        $this->client->call(
            $endpoint,
            [[
                 "contents" => $csv,
                 "filename" => $this->device_id . "-audience.csv",
                 "name"     => $this->device_id . "-audience.csv",
             ]],
        );
    }
}
