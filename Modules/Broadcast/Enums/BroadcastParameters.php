<?php

namespace Neo\Modules\Broadcast\Enums;

use Neo\Enums\ParametersEnum;

enum BroadcastParameters: string implements ParametersEnum {
    case CreativeImageMaxSizeMiB = 'CREATIVE_IMAGE_MAX_SIZE_MIB';
    case CreativeVideoMaxSizeMiB = 'CREATIVE_VIDEO_MAX_SIZE_MIB';

    public function defaultValue(): mixed {
        return match ($this) {
            self::CreativeImageMaxSizeMiB => 10,
            self::CreativeVideoMaxSizeMiB => 15,
        };
    }

    public function format(): string {
        return match ($this) {
            self::CreativeImageMaxSizeMiB => "number",
            self::CreativeVideoMaxSizeMiB => "number",
        };
    }
}
