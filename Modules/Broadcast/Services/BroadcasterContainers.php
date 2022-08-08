<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterContainers.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\Container;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

interface BroadcasterContainers {
    /**
     * Give the configuration root container ID
     *
     * @return ExternalBroadcasterResourceId
     */
    public function getRootContainerId(): ExternalBroadcasterResourceId;

    /**
     * Give the broadcaster Container with the given ID
     *
     * @param ExternalBroadcasterResourceId $container
     * @return Container|null
     */
    public function getContainer(ExternalBroadcasterResourceId $container): Container|null;
}
