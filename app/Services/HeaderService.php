<?php
namespace app\Services;

use app\Services\CacheService;
use app\Core\DB;
use Throwable;

class HeaderData {
    private string $cacheKeyHeader = "header_settings";
    private string $cacheKeyNav    = "header_navigation";

    private string $defaultHeaderPath;
    private string $defaultNavPath;

    private array $requiredKeys = [
        "site_title",
        "logo_path",
        "button_text",
        "button_link",
        "accent_color"
    ];

    public function __construct($pdo = null)
    {
        $this->defaultHeaderPath = HEADER_DEFAULT_FILE;
        $this->defaultNavPath    = NAV_DEFAULT_FILE;
    }

    /* ============================================================
       PUBLIC: RETURNS BULLETPROOF HEADER + NAV
    ============================================================ */
    public function get()
    {
        return [
            "header" => $this->getHeader(),
            "nav"    => $this->getNav()
        ];
    }

    /* ============================================================
       HEADER FETCH + FALLBACK CHAIN
    ============================================================ */
    private function getHeader(): array
    {
        // 1. Cache
        if ($cache = CacheService::load($this->cacheKeyHeader)) {
            return $this->normalize($cache);
        }

        // 2. DB
        $db = $this->getHeaderDB();
        if (!empty($db)) {
            CacheService::save($this->cacheKeyHeader, $db);
            return $this->normalize($db);
        }

        // 3. Default JSON
        if (file_exists($this->defaultHeaderPath)) {
            $json = json_decode(file_get_contents($this->defaultHeaderPath), true);
            if (!empty($json)) return $this->normalize($json);
        }

        // 4. Hard-coded fallback
        return $this->normalize($this->defaultHeader());
    }

    private function getHeaderDB(): array
    {
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("SELECT * FROM header_settings LIMIT 1");
            return $stmt->fetch() ?: [];
        } catch (Throwable $e) {
            app_log("HeaderData DB error: " . $e->getMessage(), "error");
        }
        return [];
    }

    /* ============================================================
       NAVIGATION FETCH + FALLBACK CHAIN
    ============================================================ */
    private function getNav(): array
    {
        // 1. Cache
        if ($cache = CacheService::load($this->cacheKeyNav)) {
            return $cache;
        }

        // 2. DB
        $db = $this->getNavDB();
        if (!empty($db)) {
            CacheService::save($this->cacheKeyNav, $db);
            return $db;
        }

        // 3. Default JSON
        if (file_exists($this->defaultNavPath)) {
            $json = json_decode(file_get_contents($this->defaultNavPath), true);
            if (!empty($json)) return $json;
        }

        // 4. Hardcoded fallback
        return $this->defaultNav();
    }

    private function getNavDB(): array
    {
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("
                SELECT label, url 
                FROM navigation_links 
                WHERE is_active = 1
                ORDER BY order_no ASC
            ");

            return $stmt->fetchAll() ?: [];
        } catch (Throwable $e) {
            app_log("HeaderData NAV DB error: " . $e->getMessage(), "error");
        }
        return [];
    }

    /* ============================================================
       DEFAULTS (Bulletproof Hardcoded)
    ============================================================ */

    private function defaultHeader(): array
    {
        return [
            "site_title"   => SITE_TITLE,
            "logo_path"    => SITE_LOGO,
            "button_text"  => CTA_TEXT,
            "button_link"  => CTA_LINK,
            "accent_color" => ACCENT_COLOR
        ];
    }

    private function defaultNav(): array
    {
        return [
            ["label" => "Home",    "url" => HOME_URL_NO_BASE],
            ["label" => "About",   "url" => ABOUT_URL_NO_BASE],
            ["label" => "Projects","url" => PROJECTS_URL_NO_BASE],
            ["label" => "Notes",    "url" => NOTES_URL_NO_BASE],
            ["label" => "Contact", "url" => CONTACT_URL_NO_BASE]
        ];
    }

    /* ============================================================
       NORMALIZATION (100% NO ERRORS EVER)
    ============================================================ */
    private function normalize(array $header): array
    {
        $header = array_filter($header); // remove null/empty

        foreach ($this->requiredKeys as $key) {
            if (!isset($header[$key])) {
                $header[$key] = ""; // safe empty value
            }
        }

        return $header;
    }
}
