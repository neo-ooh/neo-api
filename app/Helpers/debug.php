<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - debug.php
 */

use Symfony\Component\Console\Output\ConsoleOutput;

if (!function_exists("console_log")) {
    /**
     * Prints the given string to the terminal if running in console mode
     *
     * @param string $log
     */
    function console_log(string $log) {
       if(\Illuminate\Support\Facades\App::runningInConsole()) {
           (new ConsoleOutput())->writeln($log);
       }
    }
}
