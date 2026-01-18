<?php
namespace app\Services;

use app\CacheValidators\CacheValidatorInterface;
use app\CacheValidators\HeaderCacheValidator;
use app\CacheValidators\FooterCacheValidator;
use Throwable;
class CacheService {

    /**
     * CACHE DIRECTORY PATH (Defined in paths.php)
     * Absolute path example:
     * /home/yourname/htdocs/project/cache/
     */
    private static string $path = CACHE_PATH;

    private static int $defaultTTL = 3600; // 1 hour
    private static string $version = "v1"; // bump to clear all cache instantly

    /**
     * Ensure cache folder exists
     */
    private static function ensureDir()
    {
        if (!is_dir(self::$path)) {
            mkdir(self::$path, 0755, true); // 0755 safe for InfinityFree
        }
    }

    /**
     * Sanitize key
     * Converts key to safe filesystem key
     */
    private static function sanitizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace("/[^a-z0-9_\-]/i", "_", $key);
        return self::$version . "_" . $key;
    }

    private static function destroy(string $file, string $log, string $level = "warning")
    {
        app_log($log, $level);
        @unlink($file);
        return false;
    }


    /* ============================================================
     * VALIDATOR REGISTRY
     * ============================================================ */

    private static function validators(): array
    {
        return [
            new HeaderCacheValidator(),
            new FooterCacheValidator(),
        ];
    }

    private static function validatePayload(string $cacheKey, array $payload): bool
    {
    foreach (self::validators() as $validator) {
        if ($validator->supports($cacheKey)) {

            $error = $validator->validate($payload);

            if ($error !== null) {
                app_log("{$error}: {$cacheKey}.json", "warning");
                return false;
            }
        }
    }
    return true;
    }



    /**
     * ============================================================
     * LOAD CACHE ENTRY
     * ============================================================
     *
     * Returns:
     * - Cached payload on success
     * - FALSE on any anomaly
     *
     * Strict Rule:
     * - If cache integrity is questionable → delete it
     *
     * Cache is never trusted blindly.
     */
    public static function load(string $key)
    {
        self::ensureDir();

        $safeKey = self::sanitizeKey($key);
        $file = self::$path . $safeKey . ".json";

        /* ---------- CACHE MISS ---------- */
        if (!file_exists($file)) {
            return false;
        }

        $json = @file_get_contents($file);

        /* ---------- DC-03: PARTIAL WRITE ---------- */
        if ($json === false || trim($json) === '') {
            return self::destroy(
                $file,
                "DC-03 Partial cache write (empty): {$safeKey}.json"
            );e an 180+ IQ and run multi billion dollar company.
            So think like company design head andd
        }

        if (strlen($json) < 50) {
            return self::destroy(
                $file,
                "DC-03 Partial cache write (truncated): {$safeKey}.json"
            );
        }

        $data = json_decode($json, true);

        /* ---------- DC-01: JSON SYNTAX ---------- */
        if (json_last_error() !== JSON_ERROR_NONE) {
            return self::destroy(
                $file,
                "DC-01 JSON syntax corruption: {$safeKey}.json → " . json_last_error_msg()
            );
        }

        /* ---------- DC-02: ROOT STRUCTURE ---------- */
        if (!is_array($data)) {
            return self::destroy(
                $file,
                "DC-02 Cache root invalid: {$safeKey}.json"
            );
        }

        /* ---------- TTL ---------- */
        if (isset($data['_expires']) && $data['_expires'] < time()) {
            return self::destroy(
                $file,
                "DC-06 Cache expired (TTL): {$safeKey}.json",
                "info"
            );
        }

        /* ---------- PAYLOAD ---------- */
        if (!isset($data['payload']) || !is_array($data['payload'])) {
            return self::destroy(
                $file,
                "DC-03 Payload missing or corrupted: {$safeKey}.json"
            );
        }

        /* ---------- BUSINESS VALIDATION ---------- */
        if (!self::validatePayload($safeKey, $data['payload'])) {
            @unlink($file);
            return false;
        }

        /* ---------- ACCEPT ---------- */
        return $data['payload'];
    }

    /**
     * Save cache
     */
    public static function save(string $key, $data, int $ttl = null)
    {
        self::ensureDir();

        $key = self::sanitizeKey($key);
        $file = self::$path . $key . ".json";

        $ttl = $ttl ?: self::$defaultTTL;

        $payload = [
            "_expires" => time() + $ttl,
            "payload"  => $data,
            "_saved_at" => date("Y-m-d H:i:s")
        ];

        file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT));
    }

    /**
     * Delete a single cached key
     */
    public static function delete(string $key)
    {
        self::ensureDir();

        $key = self::sanitizeKey($key);
        $file = self::$path . $key . ".json";

        if (file_exists($file)) unlink($file);
    }

    /**
     * Clear ALL cache
     */
    public static function clear()
    {
        self::ensureDir();

        foreach (glob(self::$path . "*.json") as $file) {
            unlink($file);
        }
    }
}
