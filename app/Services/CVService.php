<?php
namespace app\Services;

use app\Core\DB;

class CVService
{
    private static int $limitPerMinute = 3;

    public static function downloadCV()
    {
        self::rateLimit();

        $relativePath = "downloads/Yogesh_Lilake_Resume.pdf";

        // Convert to absolute filesystem path
        $cvFile = PUBLIC_PATH . $relativePath;

        // Missing file → JSON error
        if (!file_exists($cvFile)) {
            app_log("CV Download FAILED — File missing: $cvFile", "error");
            app_log("CV Download FAILED — File missing: $cvFile", "error", [
                "file" => CV_LOG_FILE
            ]);

            header("Content-Type: application/json");
            http_response_code(404);

            echo json_encode([
                "status" => "error",
                "message" => "CV file not found. Try again later."
            ]);
            exit;
        }

        // Log both DB + files
        self::logToDB();
        self::logToFiles($cvFile);

        // SUCCESS → Output file for download
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"Yogesh_Lilake_Resume.pdf\"");
        header("Content-Length: " . filesize($cvFile));
        header("Cache-Control: no-store, must-revalidate");

        readfile($cvFile);
        exit;
    }

    private static function rateLimit()
    {
        $ip  = get_user_ip();
        $pdo = DB::getInstance()->pdo();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM cv_downloads
            WHERE ip_address = ?
            AND downloaded_at >= NOW() - INTERVAL 1 MINUTE
        ");
        $stmt->execute([$ip]);
        $count = $stmt->fetch()['total'] ?? 0;

        // Too many attempts → JSON error
        if ($count >= self::$limitPerMinute) {
            app_log("CV Download RATE LIMIT EXCEEDED for IP: $ip", "warning");
            app_log("CV Download RATE LIMIT EXCEEDED for IP: $ip", "warning", [
                "file" => CV_LOG_FILE
            ]);

            header("Content-Type: application/json");
            http_response_code(429);

            echo json_encode([
                "status"  => "error",
                "message" => "Too many download attempts. Try again in 1 minute."
            ]);
            exit;
        }
    }

    private static function logToDB()
    {
        $pdo = DB::getInstance()->pdo();

        $stmt = $pdo->prepare("
            INSERT INTO cv_downloads (downloaded_at, ip_address, country, device, browser)
            VALUES (NOW(), ?, ?, ?, ?)
        ");

        $stmt->execute([
            get_user_ip(),
            get_country_from_ip(get_user_ip()),
            get_device_type(),
            get_browser_name()
        ]);
    }

    private static function logToFiles($cvFile)
    {
        $message = "CV DOWNLOAD SUCCESS: $cvFile | IP=" . get_user_ip();

        app_log($message, "info");
        app_log($message, "info", ["file" => CV_LOG_FILE]);
    }
}
