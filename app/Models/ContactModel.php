<?php
namespace app\Models;

use PDO;
use app\Services\CacheService;
use app\Core\DB;
use Throwable;

class ContactModel {

    private string $cacheKey = "contact";
    private ?string $defaultHomeJson = null;
    private ?string $defaultPath = null;

    public function __construct() {
        require_once CACHESERVICE_FILE;
        $this->defaultHomeJson = safe_path('HOME_CONTACT_DEFAULT_FILE');
    }

    public function get()
    {
        /** ----------------------------------------------------
         * A. TRY CACHE
         * ----------------------------------------------------*/
        if ($cache = CacheService::load($this->cacheKey)) {
            return [
                "source" => "cache",
                "data"   => $cache
            ];
        }

        /** ----------------------------------------------------
         * B. TRY DATABASE
         * ----------------------------------------------------*/
        try {
            $pdo = DB::getInstance()->pdo();

            $stmt = $pdo->prepare("
                SELECT title, subtitle, button_text, button_link
                FROM contact_section
                WHERE is_active = 1
                LIMIT 1
            ");
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC) ? : [];

            if (!empty($row)) {
                CacheService::save($this->cacheKey, $row);
                return [
                    "source" => "db",
                    "data"   => $row
                ];
            }

        } catch (Throwable $e) {
            app_log("ContactModel@get DB error: " . $e->getMessage(), "error");

            return [
                "source" => "error",
                "data"   => $this->defaults()
            ];
        }

        /** ----------------------------------------------------
         * C. TRY DEFAULT JSON FILE
         * ----------------------------------------------------*/
        if ($this->defaultHomeJson && file_exists($this->defaultHomeJson)) {
            $json = json_decode(file_get_contents($this->defaultHomeJson), true);
            if (!empty($json) && is_array($json)) {
                return [
                    "source" => "json",
                    "data"   => $json
                ];
            }
        }

        /** ----------------------------------------------------
         * D. HARD-CODED DEFAULT FALLBACK
         * ----------------------------------------------------*/
        return [
            "source" => "fallback",
            "data"   => $this->defaults()
        ];
    }

    private function defaults(): array
    {
        return [
            "is_default"  => true,
            "title"       => "D Get In Touch",
            "subtitle"    => "Feel free to contact me for collaborations, projects, or job opportunities.",
            "button_text" => "Contact Me",
            "button_link" => "contact"
        ];
    }

    /* ============================================================
     * UNIFIED SECTION LOADER
     * Cache → DB → JSON → Fallback
     * ============================================================ */
    private function loadUnified(string $cacheKey, string $sql, string $jsonPathConst, callable $fallbackFn, bool $single = false): array 
    {
        // A. Try cache (section-level)
        if ($cache = CacheService::load($cacheKey)) {
            return [
                "source" => "cache", 
                "data" => $cache
            ];
        }

        // B. Try DB
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $data = $single
                ? ($stmt->fetch(PDO::FETCH_ASSOC) ?: [])
                : ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

            if (!empty($data)) {
                CacheService::save($cacheKey, $data);
                return ["source" => "db", "data" => $data];
            }
        } catch (Throwable $e) {
            app_log("ContactModel DB error: ".$e->getMessage(), "error");
        }

        // C. JSON defaults (DC-02 path)
        $jsonFile = safe_path($jsonPathConst);

        if ($jsonFile && file_exists($jsonFile)) {
            $json = json_decode(file_get_contents($jsonFile), true);
            if (!empty($json)) {
                return [
                    "source" => "json", 
                    "data" => $json
                ];
            }
        }

        // D. Hard fallback
        return [
            "source" => "fallback", 
            "data" => $fallbackFn()
        ];
    }

    /* --------------------------
     * Public section getters
     * -------------------------- */

    public function getHero(): array
    {
        return $this->loadUnified(
            "contact_hero",
            "SELECT * FROM contact_page_settings WHERE is_active = 1 LIMIT 1",
            "CONTACT_HERO_DEFAULT_FILE",
            [$this, "defaultHero"],
            true
        );
    }

    public function getInfo(): array
    {
        return $this->loadUnified(
            "contact_info",
            "SELECT * FROM contact_info WHERE is_active = 1 ORDER BY sort_order ASC",
            "CONTACT_INFO_DEFAULT_FILE",
            [$this, "defaultInfo"]
        );
    }

    public function getSocials(): array
    {
        return $this->loadUnified(
            "contact_socials",
            "SELECT * FROM contact_social_links WHERE is_active = 1 ORDER BY sort_order ASC",
            "CONTACT_SOCIALS_DEFAULT_FILE",
            [$this, "defaultSocials"]
        );
    }

    public function getMap(): array
    {
        return $this->loadUnified(
            "contact_map",
            "SELECT * FROM contact_page_settings WHERE is_active = 1 LIMIT 1",
            "CONTACT_MAP_DEFAULT_FILE",
            [$this, "defaultMap"],
            true
        );
    }

    public function getToast(): array
    {
        return $this->loadUnified(
            "contact_toast",
            "SELECT * FROM contact_page_settings WHERE is_active = 1 LIMIT 1",
            "CONTACT_TOAST_DEFAULT_FILE",
            [$this, "defaultToast"],
            true
        );
    }

    /* --------------------------
     * Fallback dispatcher used by the controller
     * -------------------------- */
    public function fallback(string $section)
    {
        return match ($section) {
            "hero"    => $this->defaultHero(),
            "info"    => $this->defaultInfo(),
            "socials" => $this->defaultSocials(),
            "map"     => $this->defaultMap(),
            "toast"   => $this->defaultToast(),
            default   => []
        };
    }

    /* --------------------------
     * Hard-coded fallbacks (guaranteed non-empty)
     * -------------------------- */

    public function defaultHero(): array
    {
        // NOTE: Using the uploaded file path from your session as a default image asset
        // local file path: /mnt/data/05c99886-c53b-4cdc-afaf-4e5712fdc5f3.png
        return [
            "is_default" => true,
            "heading"    => "D Let’s Build Something Great Together 🚀",
            "subheading" => "D Whether it's collaboration or learning — I'm open!",
            // Use file:///... or relative path if you plan to serve it via web server.
            // This local path was supplied in the conversation and is included here for testing.
            "hero_lottie_url"     => "https://assets4.lottiefiles.com/packages/lf20_urbk83vw.json",
            "is_active"  => 1
        ];
    }

    public function defaultInfo(): array
    {
        return [
            [
                "is_default" => true,
                "label"      => "D Email",
                "value"      => "yogeshlilake02@gmail.com",
                "icon_class" => "fa-solid fa-envelope",
                "is_active"  => 1,
                "sort_order" => 1
            ],
            [
                "is_default" => true,
                "label"      => "D Location",
                "value"      => "Pune, Maharashtra, India",
                "icon_class" => "fa-solid fa-location-dot",
                "is_active"  => 1,
                "sort_order" => 2
            ]
        ];
    }

    public function defaultSocials(): array
    {
        return [
            [
                "is_default" => true,
                "platform"   => "GitHub",
                "icon_class" => "fab fa-github",
                "url"        => "https://github.com/YogeshLilake",
                "is_active"  => 1,
                "sort_order" => 1
            ],
            [
                "is_default" => true,
                "platform"   => "LinkedIn",
                "icon_class" => "fab fa-linkedin",
                "url"        => "https://linkedin.com/in/yogeshlilake",
                "is_active"  => 1,
                "sort_order" => 2
            ],
            [
                "is_default" => true,
                "platform"   => "LeetCode",
                "icon_class" => "fa-solid fa-code",
                "url"        => "https://leetcode.com/YogeshLilake",
                "is_active"  => 1,
                "sort_order" => 3
            ]
        ];
    }

    public function defaultMap(): array
    {
        return [
            "is_default" => true,
            // fallback to Pune embed
            "map_embed_url" => "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3782.996705972482!2d73.85674347434634!3d18.5204300713009!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bc2c064e02ff0d1%3A0x8f7d96a2e97cfd2!2sPune%2C%20Maharashtra%2C%20India!5e0!3m2!1sen!2sin!4v1709915738344!5m2!1sen!2sin"
        ];
    }

    public function defaultToast(): array
    {
        return [
            "is_default" => true,
            "message" => "Thank you! Your message has been sent successfully 🎉"
        ];
    }
}
