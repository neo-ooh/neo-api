<?php

use Neo\Modules\Broadcast\Services\BroadSign\BroadSignAdapter;

return [
    'name' => 'Broadcast',

    'adapters' => [
        'broadsign' => BroadSignAdapter::class,
    ]
];
