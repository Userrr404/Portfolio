<?php

/**
 * HomeModel
 *
 * Enterprise-level model with:
 * - DB → cache → defaults fallback
 * - Guaranteed return structure (never empty)
 * - Protection against DB failure, missing table, missing columns
 * - Matches AboutModel architecture
 */

class HomeModel
{
    private string $cacheKey = "home";
    private int $defaultTTL = 3600; // 1 hour (tunable)

    public function __construct()
    {
        require_once ROOT_PATH . "app/Services/CacheService.php";
    }


    /* ============================================================
     * PUBLIC: Returns the hero/home section
     * ============================================================ */

    public function get(): array
    {
        // 1. CACHE
        if ($cache = CacheService::load($this->cacheKey)) {
            return $cache;
        }

        // 2. DB FETCH
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->prepare("
                SELECT * 
                FROM home_section 
                WHERE is_active = 1 
                LIMIT 1
            ");
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            // Save ONLY if DB returned meaningful data
            if (!empty($row)) {
                CacheService::save($this->cacheKey, $row, $this->defaultTTL);
                return $row;
            }

        } catch (Throwable $e) {

            // Never crash the home page — log and fallback
            app_log("HomeModel@get error: " . $e->getMessage(), "error");
        }

        // 3. ABSOLUTE SAFE DEFAULTS
        $defaults = $this->defaultHome();

        CacheService::save($this->cacheKey, $defaults, $this->defaultTTL);

        return $defaults;
    }


    /* ============================================================
     * DEFAULTS (GUARANTEED SAFE, NON-EMPTY)
     * ============================================================ */

    public function defaultHome(): array
    {
        return [
            "hero_title"       => "Hi, I’m " . SITE_TITLE . " 👋",
            "hero_subtitle"    => "Full Stack & Android Developer | Turning ideas into scalable digital products.",
            "hero_description" => "I build fast, modern, scalable applications using PHP, MySQL, JavaScript, TailwindCSS, and Android.",
            "cta_projects"     => "View Projects",
            "cta_contact"      => "Contact Me",
            "animation_url"    => "https://assets10.lottiefiles.com/packages/lf20_kyu7xb1v.json",
            "background_image" => IMG_URL . "default-hero-bg.jpg",
            "is_active"        => 1
        ];
    }
}
