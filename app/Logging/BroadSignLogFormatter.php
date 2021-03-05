<?php

namespace Neo\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class BroadSignLogFormatter {
    /**
     * Customize the given logger instance.
     *
     * @param Logger $logger
     * @return void
     */
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                '[%datetime%] %channel%.%level_name%: %message%'. PHP_EOL,
                'c', // ISO 8601 Date format
                false,
                true
            ));
        }
    }
}
