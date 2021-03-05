<?php

namespace Neo\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class LaravelLogFormatter {
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
                '[%datetime%] %channel%.%level_name%: %message% %context% %extra%'. PHP_EOL,
                'c', // ISO 8601 Date format
                true,
                true
            ));
        }
    }
}
