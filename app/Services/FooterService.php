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

    private ?string $defaultFooterPath = null;
    private ?string $defaultLinksPath  = null;
    private ?string $defaultSocialPath = null;

    private array $requiredFooterKeys = [
        "brand_name",
        "footer_description",
        "developer_name",
        "accent_color"
    ];

    public function __construct()
    {
        $this->defaultFooterPath = safe_path('FOOTER_DEFAULT_FILE');
        $this->defaultLinksPath  = safe_path('FOOTER_LINKS_DEFAULT_FILE');
        $this->defaultSocialPath = safe_path('FOOTER_SOCIAL_DEFAULT_FILE');
    }

    /* ============================================================
       PUBLIC API (MATCHES HomeModel / HeaderData)
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
       FOOTER SETTINGS
    ============================================================ */
    private function getFooter(): array
    {
        /** A. Cache */
        if ($cache = CacheService::load($this->cacheKeyFooter)) {

            return [
                "source" => "cache",
                "data"   => $this->normalizeFooter($cache)
            ];
        }

        /** B. DB */
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("SELECT * FROM footer_settings WHERE is_active = 1 LIMIT 1");
            $row  = $stmt->fetch() ?: [];

            if (!empty($row)) {
                CacheService::save($this->cacheKeyFooter, $row);

                return [
                    "source" => "db",
                    "data"   => $this->normalizeFooter($row)
                ];
            }

        } catch (Throwable $e) {
            app_log("FooterData@getFooter DB error: " . $e->getMessage(), "error");

            return [
                "source" => "error",
                "data"   => $this->normalizeFooter($this->defaultFooter())
            ];
        }

        /** C. Default JSON */
        if ($this->defaultFooterPath && file_exists($this->defaultFooterPath)) {
            $json = json_decode(file_get_contents($this->defaultFooterPath), true);

            if (!empty($json)) {

                return [
                    "source" => "json",
                    "data"   => $this->normalizeFooter($json)
                ];
            }
        }

        /** D. Hard fallback */
        return [
            "source" => "fallback",
            "data"   => $this->normalizeFooter($this->defaultFooter())
        ];
    }

    /* ============================================================
       FOOTER QUICK LINKS
    ============================================================ */
    private function getLinks(): array
    {
        /** A. Cache */
        if ($cache = CacheService::load($this->cacheKeyLinks)) {

            return [
                "source" => "cache",
                "data"   => $cache
            ];
        }

        /** B. DB */
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("
                SELECT label, url
                FROM navigation_links
                WHERE is_active = 1
                ORDER BY order_no ASC
            ");

            $rows = $stmt->fetchAll() ?: [];

            if (!empty($rows)) {
                CacheService::save($this->cacheKeyLinks, $rows);

                return [
                    "source" => "db",
                    "data"   => $rows
                ];
            }

        } catch (Throwable $e) {
            app_log("FooterData@getLinks DB error: " . $e->getMessage(), "error");

            return [
                "source" => "error",
                "data"   => $this->defaultLinks()
            ];
        }

        /** C. Default JSON */
        if ($this->defaultLinksPath && file_exists($this->defaultLinksPath)) {
            $json = json_decode(file_get_contents($this->defaultLinksPath), true);

            if (!empty($json)) {

                return [
                    "source" => "json",
                    "data"   => $json
                ];
            }
        }

        /** D. Hard fallback */
        return [
            "source" => "fallback",
            "data"   => $this->defaultLinks()
        ];
    }

    /* ============================================================
       SOCIAL LINKS
    ============================================================ */
    private function getSocial(): array
    {
        /** A. Cache */
        if ($cache = CacheService::load($this->cacheKeySocial)) {

            return [
                "source" => "cache",
                "data"   => $cache
            ];
        }

        /** B. DB */
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("
                SELECT platform, url, icon_class
                FROM social_links
                WHERE is_active = 1
            ");

            $rows = $stmt->fetchAll() ?: [];

            if (!empty($rows)) {
                CacheService::save($this->cacheKeySocial, $rows);

                return [
                    "source" => "db",
                    "data"   => $rows
                ];
            }


        } catch (Throwable $e) {
            app_log("FooterData@getSocial DB error: " . $e->getMessage(), "error");

            return [
                "source" => "error",
                "data"   => $this->defaultSocial()
            ];
        }

        /** C. Default JSON */
        if ($this->defaultSocialPath && file_exists($this->defaultSocialPath)) {
            $json = json_decode(file_get_contents($this->defaultSocialPath), true);

            if (!empty($json)) {

                return [
                    "source" => "json",
                    "data"   => $json
                ];
            }
        }

        /** D. Hard fallback */
        return [
            "source" => "fallback",
            "data"   => $this->defaultSocial()
        ];
    }

    /* ============================================================
       DEFAULTS
    ============================================================ */
    private function defaultFooter(): array
    {
        return [
            "brand_name"         => SITE_TITLE,
            "footer_description" => "DDFull Stack & Android Developer — Crafting digital solutions.",
            "developer_name"     => SITE_TITLE,
            "accent_color"       => ACCENT_COLOR
        ];
    }

    private function defaultLinks(): array
    {
        return [
            ["label" => "DDHome",     "url" => HOME_URL_NO_BASE],
            ["label" => "About",    "url" => ABOUT_URL_NO_BASE],
            ["label" => "Projects", "url" => PROJECTS_URL_NO_BASE],
            ["label" => "Notes",    "url" => NOTES_URL_NO_BASE],
            ["label" => "Contact",  "url" => CONTACT_URL_NO_BASE],
        ];
    }

    private function defaultSocial(): array
    {
        return [
            ["platform" => "GitHub",   "url" => "DDhttps://github.com",   "icon_class" => "fa-github"],
            ["platform" => "LinkedIn", "url" => "https://linkedin.com", "icon_class" => "fa-linkedin"],
            ["platform" => "Twitter",  "url" => "https://twitter.com",  "icon_class" => "fa-twitter"],
            ["platform" => "Email",    "url" => "mailto:hello@example.com", "icon_class" => "fa-envelope"],
        ];
    }

    /* ============================================================
       NORMALIZATION (NO array_filter BUGS)
    ============================================================ */
    private function normalizeFooter(array $footer): array
    {
        foreach ($this->requiredFooterKeys as $key) {
            if (!array_key_exists($key, $footer) || $footer[$key] === null) {
                $footer[$key] = "";
            }
        }

        return $footer;
    }
}
