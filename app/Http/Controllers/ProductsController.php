<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsController.php
 */

namespace Neo\Http\Controllers;

use Neo\Http\Requests\Products\ImportMappingsRequest;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ProductsController {
    public function _importMappings(ImportMappingsRequest $request) {
        $xlsx = new Xlsx();
        $xlsx->load($request->file("file")->path());
    }
}
