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
     * Specify the format of the parameter value
     *
     * @return string
     */
    public function format(): string;

    /**
     * Specify which capability is required to edit the parameter
     *
     * @return Capability
     */
    public function capability(): Capability;
}
