<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Location.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

use Neo\Modules\Broadcast\Enums\ExternalResourceType;

class Location extends ExternalBroadcasterResourceId {
    public ExternalResourceType $type = ExternalResourceType::Location;

    public bool $enabled;

    public string $name;

    public ExternalBroadcasterResourceId $external_display_type_id;

    public ExternalBroadcasterResourceId $container_id;

    public string|null $address = null;
    public string|null $city = null;
    public string|null $province = null;
    public string|null $country = null;
    public string|null $zipcode = null;

    public string|null $lat = null;
    public string|null $lng = null;
}
