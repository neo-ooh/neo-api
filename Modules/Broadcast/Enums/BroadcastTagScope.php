<?php

namespace Neo\Modules\Broadcast\Enums;

enum BroadcastTagScope: string {
    case Format = "format";
    case Layout = "layout";
    case Frame = "frame";

    case Campaign = "campaign";
    case Schedule = "schedule";
    case Content = "content";
    case Creative = "creative";
}
