<?php
namespace app\Models;

use PDO;
use app\Services\CacheService;
use app\Core\DB;
use Throwable;

/**
 * AboutModel
 * ---------------------------------------------------------------
 * Provides the full “DB → Cache → Default JSON → Hard fallback”
 * data-loading architecture for every About page section.
 *
 * This ensures:
 *  - UI NEVER receives empty or invalid arrays
 *  - DB errors do NOT break the page
 *  - Caching happens ONLY when DB returns valid data
 *  - JSON defaults allow easy static editing
*/

class AboutModel {

    /** General fallback key used for the main about section */
    private string $cacheKey = "about";

    /** Path to default JSON files (residing in /resources/defaults/about/) */
    private ?string $defaultPath = null;
    // private int $defaultTTL = 3600; // seconds for section caches (tunable)

    public function __construct() {
        require_once CACHESERVICE_FILE;
        $this->defaultPath = safe_path('HOME_ABOUT_DEFAULT_FILE');
    }

    /**
     * Basic single-record loader for about_section
     * (This is separate from unified loader for legacy compatibility)
    */
    public function get()
    {
        // Try cache first
        if ($cache = CacheService::load($this->cacheKey)) {
            return [
                "source" => "cache",
                "data"   => $cache
            ];
        }

        try {
            // DB query
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->prepare("SELECT * FROM about_section WHERE is_active = 1 LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ? : [];

            // Only save when DB returns valid data
            if (!empty($row)) {
                CacheService::save($this->cacheKey, $row);
                return [
                    "source" => "db",
                    "data"   => $row
                ];
            }

        } catch (Throwable $e) {
            app_log("AboutModel@get DB error: " . $e->getMessage(), "error");

            return [
                "source" => "error",
                "data"   => $this->defaults()
            ];
        }

        /** ----------------------------------------------------
        * C. TRY DEFAULT JSON FILE
        * ----------------------------------------------------*/
        if ($this->defaultPath && file_exists($this->defaultPath)) {
            $json = json_decode(file_get_contents($this->defaultPath), true);
            if (!empty($json) && is_array($json)) {
                return [
                    "source" => "json",
                    "data"   => $json
                ];
            }
        }

        /** ----------------------------------------------------
        * D. HARD-CODED FALLBACK
        * ----------------------------------------------------*/
        return [
            "source" => "fallback",
            "data"   => $this->defaults()
        ];
    }

    /* ============================================================
     * UNIFIED FALLBACK LOADER
     * ============================================================ */

    /**
     * Loads ANY About page section using:
     *
     * A. Try cache
     * B. Try DB (single or multiple rows)
     * C. Try default JSON file (if exists)
     * D. Hard-coded fallback defaults
     *
     * @param string    $cacheKey           Cache identifier
     * @param string    $table              Database table name
     * @param string    $jsonPathConst      Default JSON file path
     * @param callable  $fallbackFn         Function returning default PHP data
     * @param bool      $single             If true → LIMIT 1, else → fetchAll()
    */
    private function loadUnified(string $cacheKey, string $table, string $jsonPathConst, callable $fallbackFn, bool $single = false){
        
        /** ----------------------------------------------------
         * A. Check cache
         * ---------------------------------------------------- */
        if ($cache = CacheService::load($cacheKey)) {
            return $cache;
        }

        /** ----------------------------------------------------
         * B. Attempt DB fetch
         * ---------------------------------------------------- */
        try {
            $pdo = DB::getInstance()->pdo();

            if ($single) {
                // Load one row
                $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE is_active = 1 LIMIT 1");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            } else {
                // Load many rows
                $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE is_active = 1 ORDER BY sort_order ASC");
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            // Only store when DB returns valid results
            if (!empty($result)) {
                CacheService::save($cacheKey, $result);
                return $result;
            }

        } catch (Throwable $e) {
            app_log("AboutModel DB error {$table}: " . $e->getMessage(), "error");
        }

        /** ----------------------------------------------------
         * C. Try loading JSON defaults
         * ---------------------------------------------------- */
        $jsonFile = safe_path($jsonPathConst);

        if ($jsonFile && file_exists($jsonFile)) {
            $json = json_decode(file_get_contents($jsonFile), true);

            if (!empty($json)) {
                // ALWAYS wrap JSON defaults for controller consistency
                return [
                    "is_default_json" => true,
                    "data" => $json
                ];
            }
        }

        /** ----------------------------------------------------
         * D. Final fallback – guaranteed non-empty
         * ---------------------------------------------------- */
        return $fallbackFn();
    }


    /* ============================================================
     * PUBLIC GETTERS — each section with a clean unified call
     * ============================================================ */

    public function getHero()
    {
        return $this->loadUnified("about_hero",       "about_hero",       'ABOUT_HERO_DEFAULT_FILE',       [$this, 'defaultHero'], true);
    }

    public function getContent()
    {
        return $this->loadUnified("about_content",    "about_content",    'ABOUT_CONTENT_DEFAULT_FILE',    [$this, 'defaultContent'], true);
    }

    public function getSkills(): array
    {
        return $this->loadUnified("about_skills",     "about_skills",     'ABOUT_SKILLS_DEFAULT_FILE',     [$this, 'defaultSkills']);
    }

    public function getExperience(): array
    {
        return $this->loadUnified("about_experience", "about_experience", 'ABOUT_EXPERIENCE_DEFAULT_FILE', [$this, 'defaultExperience']);
    }

    public function getEducation(): array
    {
        return $this->loadUnified("about_education",  "about_education",  'ABOUT_EDUCATION_DEFAULT_FILE',  [$this, 'defaultEducation']);
    }

    public function getStats(): array
    {
        return $this->loadUnified("about_stats",      "about_stats",      'ABOUT_STATS_DEFAULT_FILE',      [$this, 'defaultStats']);
    }

    /* ============================================================
     * FALLBACK PROVIDERS
     * ============================================================ */

    /**
     * Returns the correct default set for a given section key.
     * Used by safeLoad() in the controller.
    */
    public function fallback(string $section)
    {
        return match ($section) {
            "hero"       => $this->defaultHero(),
            "content"    => $this->defaultContent(),
            "skills"     => $this->defaultSkills(),
            "experience" => $this->defaultExperience(),
            "education"  => $this->defaultEducation(),
            "stats"      => $this->defaultStats(),
            default      => []
        };
    }

    /* ============================================================
     * STATIC HARD-CODED FALLBACKS (never empty)
     * ============================================================ */

    public function defaults(): array
    {
        return [
            "title"   => "About Me D",
            "content" => "Hi, I'm Yogesh. I build optimized, scalable and user-friendly applications."
        ];
    }

    public function defaultHero(): array
    {
        return [
            "is_default" => true,
            "title" => "About D",
            "subtitle" => "Full Stack & Android Developer passionate about modern digital experiences.",
            "animation_url" => "https://assets10.lottiefiles.com/packages/lf20_jcikwtux.json",
            "background_opacity" => 0.15,
            "is_active" => 1
        ];
    }

    public function defaultContent(): array
    {
        return [
            "is_default" => true,
            "greeting_title" => "Hi, I'm " . SITE_TITLE . " 👋 D",
            "main_description" => "I\u2019m a Full Stack Developer & Android Engineer bridging creativity with logic. My mission is to design systems that are fast, scalable, and enjoyable to use.",
            "secondary_description" => "With hands-on experience in PHP, MySQL, JavaScript, Tailwind, and Java, I craft secure and visually engaging web and mobile applications. I also have strong fundamentals in DSA and API development.",
            "cta_text" => "Download CV",
            "cta_link" => "downloads/Yogesh_Lilake_Resume.pdf",
            "profile_image" => "profile.jpg",
            "is_active" => 1
        ];
    }

    public function defaultSkills(): array
    {
        return [
            ["is_default" => true, "skill_name" => "D PHP", "icon_class" => "fab fa-php", "color_class" => "text-blue-400"],
            ["is_default" => true, "skill_name" => "MySQL", "icon_class" => "fas fa-database", "color_class" => "text-yellow-400"],
            ["is_default" => true, "skill_name" => "JavaScript", "icon_class" => "fab fa-js", "color_class" => "text-yellow-300"],
            ["is_default" => true, "skill_name" => "TailwindCSS", "icon_class" => "fab fa-css3-alt", "color_class" => "text-sky-400"],
            ["is_default" => true, "skill_name" => "Android", "icon_class" => "fab fa-android", "color_class" => "text-green-400"],
        ];
    }

    public function defaultExperience(): array
    {
        return [
            [
                "is_default" => true,
                "title" => "D Full Stack Developer",
                "description" => "Built responsive UIs, REST APIs and improved app performance.",
                "company" => "Personal Projects",
                "period" => "2022 — Present",
            ],
            [
                "is_default" => true,
                "title" => "Android Developer",
                "description" => "Created performant mobile experiences and shipped production apps.",
                "company" => "Personal Projects",
                "period" => "2020 — 2022",
            ],
        ];
    }

    public function defaultEducation(): array
    {
        return [
            [
                "is_default" => true,
                "degree" => "D Bachelor of Engineering in Computer Science",
                "institution" => "Pune University",
                "period" => "2019 — 2023",
                "description" => "Graduated with a strong foundation in algorithms, full-stack web development, and mobile technologies.",
            ],
            [
                "is_default" => true,
                "degree" => "Higher Secondary Education (HSC)",
                "institution" => "Maharashtra State Board",
                "period" => "2017 — 2019",
                "description" => "Focused on science and mathematics with distinction.",
            ],
            [
                "is_default" => true,
                "degree" => "Secondary Education (SSC)",
                "institution" => "Maharashtra State Board",
                "period" => "2017",
                "description" => "Early problem-solving skills and academic excellence.",
            ],
        ];
    }

    public function defaultStats(): array
    {
        return [
            
            ["is_default" => true,"label" => "D Projects", "value" => "12+"],
            ["is_default" => true,"label" => "Years Experience", "value" => "3+"],
            ["is_default" => true,"label" => "Clients", "value" => "5+"],
            ["is_default" => true,"label" => "Open Source", "value" => "8+"],
        ];
    }
}
