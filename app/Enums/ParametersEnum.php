<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ParamtersEnum.php
 */

namespace Neo\Enums;

use BackedEnum;

interface ParametersEnum extends BackedEnum {
    /**
     * Give the default value for the parameter
     *
     * @return mixed
     */
    public function defaultValue(): mixed;

    /**
     * Specify the format of the paramter value
     *
     * @return string
     */
    public function format(): string;
}
