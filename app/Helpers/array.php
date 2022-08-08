<?php

if (!function_exists("array_any")) {
    /**
     * Return true if the callback return true for at least on value in the array
     *
     * @param array    $array
     * @param callable $fn
     * @return bool
     */
    function array_any(array $array, callable $fn) {
        foreach ($array as $value) {
            if ($fn($value)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists("array_every")) {
    /**
     * Return true if the callback return true for all the values in the array
     *
     * @param array    $array
     * @param callable $fn
     * @return bool
     */
    function array_every(array $array, callable $fn) {
        foreach ($array as $value) {
            if (!$fn($value)) {
                return false;
            }
        }
        return true;
    }
}
