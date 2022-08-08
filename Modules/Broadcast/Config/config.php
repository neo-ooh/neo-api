<?php

return [
    'name' => 'Broadcast',

    'adapters' => [
        'broadsign' => \Neo\Modules\Broadcast\Services\BroadSign\BroadSignAdapter::class,
    ]
];
