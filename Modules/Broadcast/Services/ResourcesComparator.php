<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourcesComparator.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcastResource;

class ResourcesComparator {
    public function __construct(public readonly ExternalBroadcastResource      $expected,
                                public readonly ExternalBroadcastResource|null $counterpart) {
    }

    /**
     * Tell if the counterpart resource exist
     *
     * @return bool
     */
    public function isFound(): bool {
        return $this->counterpart !== null;
    }

    /**
     * Tell if the two resources being compared are the same, meaning all their properties have the same values
     *
     * @return bool
     */
    public function isSame(): bool {
        if (!$this->isFound()) {
            return false;
        }

        $expected = $this->expected->toArray();
        $found    = $this->counterpart->toArray();

        foreach ($expected as $property => $expectedValue) {
            if ($found[$property] !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a property value is different between the two resources.
     *
     * @param string $property
     * @return bool
     */
    public function isDifferent(string $property): bool {
        if (!$this->isFound()) {
            return true;
        }

        return $this->expected->$property !== $this->counterpart->$property;
    }

    /**
     * List all properties between the two resources that have different values
     *
     * @return array<string>
     */
    public function differences(): array {
        $expected = $this->expected->toArray();
        $found    = $this->isFound() ? $this->counterpart->toArray() : [];

        $differences = [];

        foreach ($expected as $property => $expectedValue) {
            if ($found[$property] !== $expectedValue) {
                $differences[] = $property;
            }
        }

        return $differences;
    }
}
