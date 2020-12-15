<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Location.php
 */

namespace Neo\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\BroadSign\Endpoint;

/**
 * Class ActorsLocations
 *
 * @package Neo\BroadSign\Models
 *
 * @property int    id
 * @property string name
 * @property bool   active
 * @property int    container_id
 * @property int    domain_id
 * @property int    display_unit_type_id
 * @property string timezone
 * @property int    host_screen_count
 * @property bool   enforce_day_parts
 * @property string zipcode
 * @property string address
 * @property string external_id
 * @property bool   enforce_screen_controls
 * @property int     geolocation
 * @property bool    export_enabled
 * @property int     export_first_enabled_tm
 * @property int     export_first_enabled_by_user_id
 * @property int     export_retired_on_tm
 * @property int     export_retired_by_user_id
 * @property int     virtual_host_screen_count
 * @property string  virtual_id
 * @property int     bmb_host_id
 *
 * @property Container container
 *
 * @method static Collection all()
 * @method static Collection byReservable(array $params)
 */
class Location extends BroadSignModel {
    protected static string $unwrapKey = "display_unit";

    protected static function actions (): array {
        return [
            "all"          => Endpoint::get("/display_unit/v12")->multiple(),
            "byReservable" => Endpoint::get("/display_unit/v12/by_reservable")->domain(false)->multiple()->cache(3600),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Get the location's container
     *
     * @return Container|null
     */
    public function container (): ?Container {
        if($this->container_id === 0) {
            return null;
        }

        return Container::get($this->container_id);
    }
}
