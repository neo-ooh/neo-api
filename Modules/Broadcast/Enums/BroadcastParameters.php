<?php

namespace Neo\Modules\Broadcast\Enums;

use Neo\Enums\Capability;
use Neo\Enums\ParametersEnum;

enum BroadcastParameters: string implements ParametersEnum {
    case CreativeImageMaxSizeMiB = 'CREATIVE_IMAGE_MAX_SIZE_MIB';
    case CreativeVideoMaxSizeMiB = 'CREATIVE_VIDEO_MAX_SIZE_MIB';
    case CreativeLengthFlexibilitySec = 'CREATIVE_LENGTH_FLEXIBILITY_SEC';

    public function defaultValue(): mixed {
        return match ($this) {
            self::CreativeImageMaxSizeMiB      => 10,
            self::CreativeVideoMaxSizeMiB      => 15,
            self::CreativeLengthFlexibilitySec => .1,
        };
    }

    public function format(): string {
        return match ($this) {
            self::CreativeImageMaxSizeMiB      => "number",
            self::CreativeVideoMaxSizeMiB      => "number",
            self::CreativeLengthFlexibilitySec => "number",
        };
    }

    public function capability(): Capability {
        return match ($this) {
            self::CreativeImageMaxSizeMiB      => Capability::broadcast_settings,
            self::CreativeVideoMaxSizeMiB      => Capability::broadcast_settings,
            self::CreativeLengthFlexibilitySec => Capability::broadcast_settings,
        };
    }
}
