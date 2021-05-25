<?php

namespace Neo\Services\Broadcast;

use Neo\Exceptions\InvalidBroadcastServiceException;
use Neo\Models\Network;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\BroadSignServiceAdapter;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;
use Neo\Services\Broadcast\PiSignage\PiSignageServiceAdapter;

abstract class Broadcast {
    /**
     * Get the appropriate broadcaster adapter for the specified network
     * @throws InvalidBroadcastServiceException
     */
    public static function network(int $networkId): BroadcastService {
        // Get the network and the connection and the type of broadcaster
        /** @var Network $network */
        $network         = Network::with("broadcaster_connection")->findOrFail($networkId);
        $broadcasterType = $network->broadcaster_connection->broadcaster;

        // Build the appropriate service adapter
        switch($broadcasterType) {
            case Broadcaster::BROADSIGN:
                $config = new BroadSignConfig();
                $config->connectionID = $network->broadcaster_connection->id;
                $config->connectionUUID = $network->broadcaster_connection->uuid;
                $config->networkID = $network->id;
                $config->networkUUID = $network->uuid;
                $config->apiURL = config("broadsign.api.url");
                $config->domainId = $network->broadcaster_connection->settings->domain_id;
                $config->customerId = $network->settings->customer_id;
                $config->containerId = $network->settings->container_id;
                $config->trackingId = $network->settings->tracking_id;

                return new BroadSignServiceAdapter($config);

            case Broadcaster::PISIGNAGE:
                $config = new PiSignageConfig();
                $config->connectionID = $network->broadcaster_connection->id;
                $config->connectionUUID = $network->broadcaster_connection->uuid;
                $config->networkID = $network->id;
                $config->networkUUID = $network->uuid;
                $config->apiURL = $network->broadcaster_connection->settings->server_url;
                $config->apiToken = $network->broadcaster_connection->settings->token;

                return new PiSignageServiceAdapter($config);

            default:
                throw new InvalidBroadcastServiceException($broadcasterType);
        }
    }
}
