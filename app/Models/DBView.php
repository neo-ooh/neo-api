<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DBView.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Neo\Exceptions\DBViewException;

/**
 * Simple representation of DB view conserving most of the functionalities from Eloquent OdooModel
 * but making data are not written by error to a view.
 */
abstract class DBView extends Model {
    /**
     * @throws DBViewException
     */
    /*public function save(array $options = []): never {
        throw new DBViewException("Cannot update a database view row");
    }*/

    /**
     * @throws DBViewException
     */
    /*public function delete(): never {
        throw new DBViewException("Cannot delete a database view row");
    }*/
}
