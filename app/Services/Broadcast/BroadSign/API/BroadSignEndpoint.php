<?php

namespace Neo\Services\Broadcast\BroadSign\API;

use Neo\Services\API\Endpoint;

class BroadSignEndpoint extends Endpoint {
    public bool $includeDomainID = true;

    public ?string $unwrapKey;

    /**
     * Specified if the parameter "domain_id" should be automatically added to the request or not
     *
     * @param bool $includeDomain
     *
     * @return $this
     */
    public function domain(bool $includeDomain = true): BroadSignEndpoint {
        $this->includeDomainID = $includeDomain;
        return $this;
    }

    /**
     * Specify the unwrap key for the received response
     *
     * @param string $key
     * @return $this
     */
    public function unwrap(string $key): BroadSignEndpoint {
        $this->unwrapKey = $key;
        return $this;
    }
}