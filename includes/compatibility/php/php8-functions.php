<?php

/*
*   Provide functions that where declared in php 8
*/


if (!function_exists('str_ends_with')) {
    /**
     * str_ends_with
     * 
     * checks, if string $haystack ends with $needle
     * 
     * @param string
     * @param string
     * @return bool
     */
    function str_ends_with(string $haystack, string $needle): bool
    {
        if (strlen($needle) <= strlen($haystack) && is_int(strpos($haystack, $needle, -strlen($needle)))) {
            return true;
        }
        return false;
    }
}
