<?php
namespace app\Controllers;

use app\core\Controller;
use app\Services\CVService;
use Throwable;

class DownloadController extends Controller
{
    public function cvdownload()
    {
        try {
            CVService::downloadCV(); // This will stream file or JSON error

        } catch (Throwable $e) {
            // Log error
            app_log("CV Download Error: " . $e->getMessage(), "error");
            app_log("CV Download Error: " . $e->getMessage(), "error", [
                "file" => CV_LOG_FILE
            ]);

            // JSON error output
            header("Content-Type: application/json");
            http_response_code(500);

            echo json_encode([
                "status"  => "error",
                "message" => "Unexpected error occurred. Please try again later."
            ]);
            exit;
        }
    }
}
