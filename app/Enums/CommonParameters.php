<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CommonParameters.php
 */

namespace Neo\Enums;

enum CommonParameters: string implements ParametersEnum {
    case TermsOfService = 'TOS';

    public function defaultValue(): mixed {
        return match ($this) {
            self::TermsOfService => null,
        };
    }

    public function format(): string {
        return match ($this) {
            self::TermsOfService => "file:pdf",
        };
    }

    public function capability(): Capability {
        return match ($this) {
            self::TermsOfService => Capability::tos_update,
        };
    }
}
