<?php

namespace App\Utils;

use \Exception;

class UrlHandler
{
    public static function compareHosts(string $url, string $host): bool
    {
        if (parse_url($url, PHP_URL_HOST) !== $host) {
            return false;
        }
        return true;
    }
    public static function findInUrl(string $url, string $pattern): ?array
    {
        preg_match($pattern, $url, $matches);

        if (empty($matches)) {
            return null;
        }

        return $matches;
    }
}
