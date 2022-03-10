<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignConfig.php
 */

namespace Neo\Services\Broadcast\BroadSign;

use Illuminate\Support\Facades\Storage;
use Neo\Services\Broadcast\Broadcaster;

class BroadSignConfig {
    public string $broadcaster = Broadcaster::BROADSIGN;

    public int $connectionID;

    public string $connectionUUID;

    public int $networkID;

    public string $networkUUID;

    public string $apiURL;

    public int $domainId;

    public int $customerId;

    public int $containerId;

    public int $trackingId;

    public int $reservationsContainerId;

    public int $adCopiesContainerId;

    /**
     * Give the path to the certificate used to authenticate requests.
     *
     * @return string
     */
    public function getCertPath(): string {
        $path = "secure/certs/$this->connectionUUID.pem";

        // We need a local copy of the certificate to be able to use it with Broadsign
        if (!Storage::disk("local")->exists($path)) {
            Storage::disk("local")->put($path, Storage::disk("public")->get($path));
        }

        return Storage::disk('local')->path($path);
    }
}
