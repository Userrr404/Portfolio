<?php

/**
 * Generate a full URL from a route path.
 * Works on localhost + production.
 *
 * Examples:
 *  url('/')        → http://localhost/Portfolio/public/
 *  url('projects') → http://localhost/Portfolio/public/projects
 *  url('/contact') → https://domain.com/contact
 */
function url(string $path = ''): string
{
    $path = trim($path);

    // Absolute URL → return as-is
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    // Normalize
    $path = ltrim($path, '/');

    return BASE_URL . $path;
}

function field($array, $key, $default = "") {
    return isset($array[$key]) ? safe($array[$key]) : $default;
}

if (!function_exists('safe')) {
    function safe($value, string $default = ''): string
    {
        if (!isset($value) || $value === '' || $value === false || $value === null) {
            return $default;
        }

        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('safeStr')) {
    function safeStr($value, int $length = 120, string $default = ''): string
    {
        if (!isset($value) || $value === '' || $value === false || $value === null) {
            return $default;
        }

        return htmlspecialchars(substr((string)$value, 0, $length), ENT_QUOTES, 'UTF-8');
    }
}

function logo(string $path): string 
{
    return IMG_URL . $path;
}

function asset(string $path): string
{
    return PROJECT_URL . $path;
}

