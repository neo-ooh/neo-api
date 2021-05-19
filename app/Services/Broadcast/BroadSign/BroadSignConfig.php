<?php

namespace Neo\Services\Broadcast\BroadSign;

use Illuminate\Support\Facades\Storage;

class BroadSignConfig {
    public string $connectionUUID;

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
        return Storage::url("secure/certs/$this->connectionUUID.pem");
    }
}
