<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActivityLogFormatter.php
 */

namespace Neo\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class ActivityLogFormatter {
    /**
     * Customize the given logger instance.
     *
     * @param Logger $logger
     * @return void
     */
    public function __invoke(Logger $logger): void {
        foreach ($logger->getHandlers() as $handler) {
            $lineFormatter = new LineFormatter(
                '[%datetime%] %channel%.%level_name%: %message% context:%context%' . PHP_EOL,
                'c', // ISO 8601 Date format
                true,
                true
            );
            $lineFormatter->includeStacktraces(true);
            $handler->setFormatter($lineFormatter);
        }
    }
}
