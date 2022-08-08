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

use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResource;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use ReflectionException;
use ReflectionProperty;

class ResourcesComparator {
    public function __construct(public readonly ExternalBroadcasterResource      $expected,
                                public readonly ExternalBroadcasterResource|null $counterpart) {
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
        $expected = $this->expected->toArray();

        foreach ($expected as $property => $expectedValue) {
            if (!$this->compareProperty($property)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a property or list of properties value is different between the two resources.
     *
     * @param string|array<string> $properties
     * @return bool
     */
    public function isDifferent(string|array $properties): bool {
        $propertiesList = is_array($properties) ? $properties : [$properties];

        foreach ($propertiesList as $property) {
            if (!$this->compareProperty($property)) {
                return true;
            }
        }

        return false;
    }

    /**
     * List all properties between the two resources that have different values
     *
     * @return array<string>
     */
    public function differences(): array {
        $expected    = $this->expected->all();
        $differences = [];

        foreach ($expected as $property => $expectedValue) {
            if (!$this->compareProperty($property)) {
                $differences[] = $property;
            }
        }

        return $differences;
    }

    /**
     * Return true if the two resources are the same, false otherwise.
     *
     * This method will properly compare two `ExternalBroadcasterResourceId`, and will compare array by checking their length and
     * comparing each elements in it.
     *
     * @param string $property
     * @return bool
     */
    protected function compareProperty(string $property): bool {
        if (!$this->isFound()) {
            return false;
        }

        // Check if the `DoNotCompare` attribute is present on the property
        try {
            $ignore = count((new ReflectionProperty($this->expected, $property))->getAttributes(DoNotCompare::class)) > 0;
        } catch (ReflectionException $e) {
            $ignore = false;
        }

        if ($ignore) {
            // Ignore property
            return true;
        }

        return $this->compareValues($this->expected->$property, $this->counterpart->$property);
    }

    protected function compareValues(mixed $a, mixed $b): bool {
        if ($a instanceof ExternalBroadcasterResourceId && $b instanceof ExternalBroadcasterResourceId) {
            return $a->external_id === $b->external_id;
        }

        if ($a instanceof ExternalBroadcasterResource && $b instanceof ExternalBroadcasterResource) {
            return (new ResourcesComparator($a, $b))->isSame();
        }

        if (is_array($a) && is_array($b)) {
            if (count($a) !== count($b)) {
                return false;
            }

            for ($i = 0, $iMax = count($a); $i < $iMax; ++$i) {
                if (!$this->compareValues($a[$i], $b[$i])) {
                    return false;
                }
            }

            return true;
        }

        return $a === $b;
    }
}
