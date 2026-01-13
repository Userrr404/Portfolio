<?php
namespace app\Services;

use app\Core\DB;
use Throwable;

class CVService
{
    private static int $limitPerMinute = 3;
    private static int $windowSeconds  = 60;

    /* ============================================================
     * PUBLIC ENTRY
     * ============================================================ */
    public static function downloadCV()
    {
        self::startSession();
        self::sessionRateLimit();   //  PRIMARY
        self::jsonRateLimit();      //  FALLBACK SAFETY

        $relativePath = "downloads/Yogesh_Lilake_Resume.pdf";

        // Convert to absolute filesystem path
        $cvFile = PUBLIC_PATH . $relativePath;

        // Missing file → JSON error
        if (!file_exists($cvFile)) {
            self::jsonError(404, "CV file not found. Try again later.");
        }

        // Optional logging (DB only if available)
        self::logToDBSafe();
        self::logToFileSafe($cvFile);

        // SUCCESS → Output file for download
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"Yogesh_Lilake_Resume.pdf\"");
        header("Content-Length: " . filesize($cvFile));
        header("Cache-Control: no-store, must-revalidate");

        readfile($cvFile);
        exit;
    }

    /* ============================================================
     * SESSION RATE LIMIT (PRIMARY)
     * ============================================================ */
    private static function sessionRateLimit(): void
    {
        $now = time();
        $_SESSION['cv_downloads'] ??= [];

        // keep only last 60 seconds
        $_SESSION['cv_downloads'] = array_values(array_filter(
            $_SESSION['cv_downloads'],
            fn ($t) => $t >= $now - self::$windowSeconds
        ));

        if (count($_SESSION['cv_downloads']) >= self::$limitPerMinute) {
            app_log("CV SESSION RATE LIMIT exceeded", "warning");
            self::jsonError(429, "Too many downloads. Please wait a minute.");
        }

        $_SESSION['cv_downloads'][] = $now;
    }

    /* ============================================================
     * JSON RATE LIMIT (DB-FREE BACKUP)
     * ============================================================ */
    private static function jsonRateLimit(): void
    {
        $ip  = get_user_ip();
        $now = time();

        $file = CV_RATE_LIMIT_FILE;

        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]));
        }

        $data = json_decode(file_get_contents($file), true) ?: [];

        $data[$ip] ??= [];

        // keep only last 60 seconds
        $data[$ip] = array_values(array_filter(
            $data[$ip],
            fn ($t) => $t >= $now - self::$windowSeconds
        ));

        if (count($data[$ip]) >= self::$limitPerMinute) {
            app_log("CV JSON RATE LIMIT exceeded for IP: $ip", "warning");
            self::jsonError(429, "Too many downloads. Please wait a minute.");
        }

        $data[$ip][] = $now;

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    /* ============================================================
     * SAFE DB LOGGING (OPTIONAL)
     * ============================================================ */
    private static function logToDBSafe(): void
    {
        try {
            $pdo = DB::getInstance()->pdo();
            if (!$pdo) return;

            $stmt = $pdo->prepare("
                INSERT INTO cv_downloads
                (downloaded_at, ip_address, country, device, browser)
                VALUES (NOW(), ?, ?, ?, ?)
            ");

            $stmt->execute([
                get_user_ip(),
                get_country_from_ip(get_user_ip()),
                get_device_type(),
                get_browser_name()
            ]);

        } catch (Throwable $e) {
            app_log("CV DB LOG skipped: " . $e->getMessage(), "debug");
        }
    }

    /* ============================================================
     * FILE LOGGING (ALWAYS SAFE)
     * ============================================================ */
    private static function logToFileSafe(string $file): void
    {
        $msg = "CV DOWNLOAD SUCCESS | IP=" . get_user_ip();
        app_log($msg, "info");
        app_log($msg, "info", ["file" => CV_LOG_FILE]);
    }

    /* ============================================================
     * HELPERS
     * ============================================================ */
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private static function jsonError(int $code, string $message): void
    {
        header("Content-Type: application/json");
        http_response_code($code);
        echo json_encode(["status" => "error", "message" => $message]);
        exit;
    }
}
