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
