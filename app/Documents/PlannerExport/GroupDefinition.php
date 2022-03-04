<?php

namespace Neo\Documents\PlannerExport;

class GroupDefinition {
    /**
     * @param string $name
     * @param array  $categories
     * @param array  $cities
     * @param array  $markets
     * @param array  $networks
     * @param array  $provinces
     * @param array  $tags
     * @param string $color HEX color code with the starting #
     */
    public function __construct(
        public string $name,
        public array  $categories,
        public array  $cities,
        public array  $markets,
        public array  $networks,
        public array  $provinces,
        public array  $tags,
        public string $color,
    ) {
    }
}
