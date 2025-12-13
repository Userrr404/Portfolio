<?php
namespace app\Models;

use app\Services\CacheService;
use app\Core\DB;
use Throwable;

class HomeModel
{
    private string $cacheKey = "home";
    private string $defaultPath;
    // private int $defaultTTL = 3600; // 1 hour (tunable)

    public function __construct()
    {

        // Path to /resources/defaults/home.json
        $this->defaultPath = HOME_DEFAULT_FILE;
    }


    /* ============================================================
     * PUBLIC: Returns the hero/home section
     * ============================================================ */

    public function get(bool $pure = false): array
    {
        // Return pure DB result (no fallback mixing) Or Normal fallback system
        return $pure ? $this->getOnlyDB() : $this->getFallbackMode();

    }

    private function getOnlyDB(): array{
        // 2. DB FETCH
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("SELECT * FROM home_section WHERE is_active=1 LIMIT 1");

            $row = $stmt->fetch() ?: [];

            // Save ONLY if DB returned meaningful data
            if (!empty($row)) {
                CacheService::save($this->cacheKey, $row);
                return $row;
            }

        } catch (Throwable $e) {

            // Never crash the home page — log and fallback
            app_log("HomeModel@get error: " . $e->getMessage(), "error");
        }

        return [];
    }



    private function getFallbackMode(): array{
        /** A. Try cache */
        if ($cache = CacheService::load($this->cacheKey)) {
            return $cache;
        }

        /** B. Try DB */
        $row = $this->getOnlyDB();
        if (!empty($row)) {
            CacheService::save($this->cacheKey, $row);
            return $row;
        }

        /** C. Try default JSON */
        if (file_exists($this->defaultPath)) {
            $json = json_decode(file_get_contents($this->defaultPath), true);
            if (!empty($json)) return $json;
        }

        /** D. Hard-coded fallback */
        return $this->defaultHome();
    }


    /* ============================================================
     * DEFAULTS (GUARANTEED SAFE, NON-EMPTY)
     * ============================================================ */

    public function defaultHome(): array
    {
        return [
            "hero_heading"      => "Welcome to My Portfolio",
            "hero_subheading"   => "Full Stack Developer",
            "hero_description"  => "Building scalable, high-performance applications.",
            "background_image"  => IMG_URL . "default-hero.jpg",
            "background_lottie" => "https://assets10.lottiefiles.com/packages/lf20_kyu7xb1v.json",
            "profile_image"     => IMG_URL . "profile-default.png",

            "cta_primary_text"  => "View Projects",
            "cta_primary_link"  => "projects",

            "cta_secondary_text" => "Download CV",
            "cta_secondary_link" => "downloadcv",

            "cv_file_path"      => "downloads/Yogesh_Lilake_Resume.pdf",

            "seo_title"         => "Avinash Portfolio | Full Stack & Android Developer",
            "seo_description"   => "Portfolio of Avinash, a Full Stack Developer building high-performance web and mobile applications using PHP, JS, MySQL and modern frameworks.",

            "is_active"   => 1,
            "is_default"  => true
        ];
    }
}
