<?php

namespace Neo\Services\API;

interface APIClientInterface {
    public function call($endpoint, $payload, array $headers = []);
}
