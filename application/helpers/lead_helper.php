<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('normalize_mobile')) {
    function normalize_mobile($num)
    {
        $num = preg_replace('/\D+/', '', (string) $num);
        if (strlen($num) > 10) {
            $num = substr($num, -10);
        }
        return trim($num);
    }
}