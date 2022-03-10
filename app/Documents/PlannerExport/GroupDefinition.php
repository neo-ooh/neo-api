<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GroupDefinition.php
 */

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
