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
            CVService::downloadCV();
        } catch (Throwable $e) {
            app_log("CV Download Error: " . $e->getMessage(), "error");
            app_log(
                "CV Download Error: " . $e->getMessage(),
                "error",
                ["file" => CV_LOG_FILE]
            );
            http_response_code(404);
            echo "<h2>Unable to download the CV right now.</h2>";
            exit;
        }
    }
}
