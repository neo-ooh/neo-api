<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - aspectRatio.php
 */

if (!function_exists("aspect_ratio")) {
    /**
     * @param float $val Division result of the width and height we are trying to find the aspect ratio (width/height)
     * @param int   $lim Maximum accepted value for the returned aspect ratio
     * @return int[]
     * @author ccpizza
     * @link   https://stackoverflow.com/questions/1186414/whats-the-algorithm-to-calculate-aspect-ratio/43016456#43016456
     * @note   ccpizza answer has been converted to PHP
     *
     */
    function aspect_ratio(float $val, int $lim = 16): array {
        $lower = [0, 1];
        $upper = [1, 0];

        while (true) {
            $mediant = [$lower[0] + $upper[0], $lower[1] + $upper[1]];

            if ($val * $mediant[1] > $mediant[0]) {
                if ($lim < $mediant[1]) {
                    return $upper;
                }
                $lower = $mediant;
            } else if ($val * $mediant[1] === $mediant[0]) {
                if ($lim >= $mediant[1]) {
                    return $mediant;
                }
                if ($lower[1] < $upper[1]) {
                    return $lower;
                }
                return $upper;
            } else {
                if ($lim < $mediant[1]) {
                    return $lower;
                }
                $upper = $mediant;
            }
        }
    }
}
