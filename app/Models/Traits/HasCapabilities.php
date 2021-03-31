<?php

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Neo\Enums\Capability as CapabilityEnum;
use Neo\Models\Capability;

/**
 * Trait HasCapabilities
 *
 * @package Neo\Models\Traits
 *
 * @property Collection<Capability> capabilities List all capabilities directly and indirectly applying to this user
 */
trait HasCapabilities {

    /**
     * Tell if the current Actor has the specified capability
     *
     * @param CapabilityEnum $capability
     *
     * @return bool
     */
    public function hasCapability(CapabilityEnum $capability): bool {
        return $this->capabilities->pluck("slug")->contains($capability->value);
    }
}
