<?php

namespace Neo\Services\Broadcast\BroadSign;

use Illuminate\Support\Facades\Storage;

class BroadSignConfig {
    public int $connectionID;

    public string $connectionUUID;

    public int $networkID;

    public string $networkUUID;

    public string $apiURL;

    public int $domainId;

    public int $customerId;

    public int $containerId;

    public int $trackingId;

    /**
     * Give the path to the certificate used to authenticate requests.
     *
     * @return string
     */
    public function getCertPath(): string {
        $path = "secure/certs/$this->connectionUUID.pem";

        // We need a local copy of the certificate to be able to use it with Broadsign
        if(!Storage::disk("local")->exists($path)) {
            Storage::disk("local")->put($path, Storage::get($path));
        }

        return Storage::disk('local')->path($path);
    }
}
