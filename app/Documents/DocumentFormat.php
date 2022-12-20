<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DocumentFormat.php
 */

namespace Neo\Documents;

enum DocumentFormat: string {
    case PDF = "application/pdf";
    case XLSX = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
    case CSV = "text/csv";
}
