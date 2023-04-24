<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExportLevel.php
 */

namespace Neo\Documents\PropertiesExport;

enum ExportLevel: string {
    case Properties = "properties";
    case Products = "products";
    case Locations = "locations";
    case Players = "players";
}
