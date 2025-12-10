<?php
namespace app\Services;

class CVService
{
    public static function downloadCV()
    {
        // Relative location inside public folder
        $relativePath = "downloads/Yogesh_Lilake_Resume.pdf";

        // Convert to absolute filesystem path
        $cvFile = PUBLIC_PATH . $relativePath;

        // If file missing → log in BOTH LOGS
        if (!file_exists($cvFile)) {
            app_log("CV Download FAILED — File missing: $cvFile", "error");  
            app_log("CV Download FAILED — File missing: $cvFile", "error", ["file" => CV_LOG_FILE]);


            http_response_code(404);
            echo "<h2>CV not available at the moment.</h2>";
            exit;
        }

        // Log SUCCESS in BOTH logs
        app_log("CV DOWNLOAD SUCCESS — $cvFile", "info");
        app_log("CV DOWNLOAD SUCCESS — $cvFile", "info", ["file" => CV_LOG_FILE]);

        // Output file headers
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"Yogesh_Lilake_Resume.pdf\"");
        header("Content-Length: " . filesize($cvFile));
        header("Cache-Control: no-store, must-revalidate");

        readfile($cvFile);
        exit;
    }
}
