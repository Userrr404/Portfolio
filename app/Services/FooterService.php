<?php
namespace app\Services;

use app\Services\CacheService;
use app\Core\DB;
use Throwable;

class FooterData
{
    private string $cacheKeyFooter = "footer_settings";
    private string $cacheKeyLinks  = "footer_quick_links";
    private string $cacheKeySocial = "footer_social_links";

    private string $defaultFooterPath;
    private string $defaultLinksPath;
    private string $defaultSocialPath;

    // REQUIRED keys to prevent undefined index errors
    private array $requiredFooterKeys = [
        "brand_name",
        "footer_description",
        "developer_name",
        "accent_color"
    ];

    public function __construct()
    {
        // Set default JSON paths
        $this->defaultFooterPath = FOOTER_DEFAULT_FILE;
        $this->defaultLinksPath  = FOOTER_LINKS_DEFAULT_FILE;
        $this->defaultSocialPath = FOOTER_SOCIAL_DEFAULT_FILE;
    }

    /* ============================================================
       PUBLIC MAIN METHOD
    ============================================================ */
    public function get(): array
    {
        return [
            "footer" => $this->getFooter(),
            "links"  => $this->getLinks(),
            "social" => $this->getSocial()
        ];
    }

    /* ============================================================
       FOOTER SETTINGS (Cache → DB → JSON → Hardcoded)
    ============================================================ */
    private function getFooter(): array
    {
        // 1. Cache
        if ($cache = CacheService::load($this->cacheKeyFooter)) {
            return $this->normalizeFooter($cache);
        }

        // 2. DB
        $db = $this->getFooterFromDB();
        if (!empty($db)) {
            CacheService::save($this->cacheKeyFooter, $db);
            return $this->normalizeFooter($db);
        }

        // 3. JSON fallback
        if (file_exists($this->defaultFooterPath)) {
            $json = json_decode(file_get_contents($this->defaultFooterPath), true);
            if (!empty($json)) return $this->normalizeFooter($json);
        }

        // 4. Hardcoded fallback
        return $this->normalizeFooter($this->defaultFooter());
    }

    private function getFooterFromDB(): array
    {
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("SELECT * FROM footer_settings LIMIT 1");
            return $stmt->fetch() ?: [];
        } catch (Throwable $e) {
            app_log("FooterData@DB footer error: " . $e->getMessage(), "error");
            return [];
        }
    }

    /* ============================================================
       FOOTER QUICK LINKS (Cache → DB → JSON → Hardcoded)
    ============================================================ */
    private function getLinks(): array
    {
        // 1. Cache
        if ($cache = CacheService::load($this->cacheKeyLinks)) {
            return $cache;
        }

        // 2. DB
        $db = $this->getLinksFromDB();
        if (!empty($db)) {
            CacheService::save($this->cacheKeyLinks, $db);
            return $db;
        }

        // 3. JSON
        if (file_exists($this->defaultLinksPath)) {
            $json = json_decode(file_get_contents($this->defaultLinksPath), true);
            if (!empty($json)) return $json;
        }

        // 4. Hardcoded fallback
        return $this->defaultLinks();
    }

    private function getLinksFromDB(): array
    {
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("
                SELECT *
                FROM navigation_links
                WHERE is_active = 1
                ORDER BY order_no ASC
            ");

            return $stmt->fetchAll() ?: [];
        } catch (Throwable $e) {
            app_log("FooterData@DB links error: " . $e->getMessage(), "error");
            return [];
        }
    }

    /* ============================================================
       SOCIAL LINKS (Cache → DB → JSON → Hardcoded)
    ============================================================ */
    private function getSocial(): array
    {
        // 1. Cache
        if ($cache = CacheService::load($this->cacheKeySocial)) {
            return $cache;
        }

        // 2. DB
        $db = $this->getSocialFromDB();
        if (!empty($db)) {
            CacheService::save($this->cacheKeySocial, $db);
            return $db;
        }

        // 3. JSON
        if (file_exists($this->defaultSocialPath)) {
            $json = json_decode(file_get_contents($this->defaultSocialPath), true);
            if (!empty($json)) return $json;
        }

        // 4. Hardcoded fallback
        return $this->defaultSocial();
    }

    private function getSocialFromDB(): array
    {
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("
                SELECT platform, url, icon_class 
                FROM social_links 
                WHERE is_active = 1
            ");

            return $stmt->fetchAll() ?: [];
        } catch (Throwable $e) {
            app_log("FooterData@DB social error: " . $e->getMessage(), "error");
            return [];
        }
    }

    /* ============================================================
       DEFAULTS (Hardcoded)
    ============================================================ */
    private function defaultFooter(): array
    {
        return [
            "brand_name"        => SITE_TITLE,
            "footer_description"=> "DD Full Stack & Android Developer — Crafting digital solutions.",
            "developer_name"    => SITE_TITLE,
            "accent_color"      => ACCENT_COLOR
        ];
    }

    private function defaultLinks(): array
    {
        return [
            ["label" => "DD Home",     "url" => HOME_URL],
            ["label" => "About",    "url" => ABOUT_URL],
            ["label" => "Projects", "url" => PROJECTS_URL],
            ["label" => "Contact",  "url" => CONTACT_URL]
        ];
    }

    private function defaultSocial(): array
    {
        return [
            ["platform" => "GitHub",   "url" => "DDhttps://github.com",   "icon_class" => "fa-github"],
            ["platform" => "LinkedIn", "url" => "https://linkedin.com", "icon_class" => "fa-linkedin"],
            ["platform" => "Twitter",  "url" => "https://twitter.com",  "icon_class" => "fa-twitter"],
            ["platform" => "Email",    "url" => "mailto:hello@example.com", "icon_class" => "fa-envelope"]
        ];
    }

    /* ============================================================
       NORMALIZATION LAYER
       Prevents ALL "undefined index" warnings
    ============================================================ */
    private function normalizeFooter(array $footer): array
    {
        $footer = array_filter($footer); // Remove empty/null

        foreach ($this->requiredFooterKeys as $key) {
            if (!array_key_exists($key, $footer)) {
                $footer[$key] = ""; // Safe value to avoid warnings
            }
        }

        return $footer;
    }
}
