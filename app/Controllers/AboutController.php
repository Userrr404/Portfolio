<?php

class AboutController extends Controller
{
    /** @var AboutModel Handles all DB/cache/default logic for About page sections */
    private AboutModel $about;

    /** 
     * Cache key for storing the entire About page structure.
     * Only saved when ALL sections return REAL DB data.
    */
    private string $cacheKey = "about_page";

    public function __construct()
    {
        require_once ABOUT_MODEL_FILE;
        require_once CACHESERVICE_FILE;

        $this->about = new AboutModel();
    }

    /**
     * ABOUT PAGE CONTROLLER
     * -------------------------------------------------------------
     * Loads the full About page using the unified 4-step architecture:
     *  A. Load FULL PAGE from cache (fastest, if DB previously succeeded)
     *  B. Load each section safely (DB → JSON → fallback)
     *  C. Cache full page ONLY if ALL sections were from DB
     *  D. Emergency fallback if controller crashes
    */
    public function index()
    {
        try {
            
            /** ---------------------------------------------------
             * 1. Try loading full page from cache
             * --------------------------------------------------- */
            if ($cached = CacheService::load($this->cacheKey)) {
                return $cached; // Return cached version immediately
            }

            /** ---------------------------------------------------
             * B. Load each section safely & independently
             * Each section returns:
             *   [
             *     "from_db" => bool,
             *     "data"    => [...]
             *   ]
             * --------------------------------------------------- */
            $data = [
                "hero"       => $this->safeLoad(fn() => $this->about->getHero(),       "hero"),
                "content"    => $this->safeLoad(fn() => $this->about->getContent(),    "content"),
                "skills"     => $this->safeLoad(fn() => $this->about->getSkills(),     "skills"),
                "experience" => $this->safeLoad(fn() => $this->about->getExperience(), "experience"),
                "education"  => $this->safeLoad(fn() => $this->about->getEducation(),  "education"),
                "stats"      => $this->safeLoad(fn() => $this->about->getStats(),      "stats"),
            ];

            /** ---------------------------------------------------
             * C. Save full-page cache ONLY when ALL sections came
             *    from real DB calls (prevents caching defaults)
             * --------------------------------------------------- */
            if ($this->hasRealData($data)) {
                CacheService::save($this->cacheKey, $data);
            }

            return $data;

        } catch (Throwable $e) {

            app_log("AboutController@index failed: " . $e->getMessage(), "error");

            /** ---------------------------------------------------
             * D. Emergency fallback (controller-level protection)
             * --------------------------------------------------- */
            return [
                "hero"       => ["data" => $this->about->defaultHero()],
                "content"    => ["data" => $this->about->defaultContent()],
                "skills"     => ["data" => $this->about->defaultSkills()],
                "experience" => ["data" => $this->about->defaultExperience()],
                "education"  => ["data" => $this->about->defaultEducation()],
                "stats"      => ["data" => $this->about->defaultStats()],
            ];
        }
    }

    /* ============================================================
     * SECTION LOADER (safe wrapper)
     * ============================================================ */

    /**
     * Safely loads a single About section.
     *
     * Ensures:
     *  - A failing model function will NOT break the page.
     *  - Differentiates between DB data and default/fallback.
     *  - Standardizes section output for the view.
    */
    private function safeLoad(callable $fn, string $label): array
    {
        try {
            $data = $fn();

            // Invalid result (null, bool, string, etc)
            if (!is_array($data)) {
                return ["from_db" => false, "data" => $this->about->fallback($label)];
            }

            // If Manual JSON files are there then always check JSON files then latter check hard-coded fallback
            // JSON defaults wrapped as: [ "is_default_json" => true, "data" => [...] ] for single & multi lines recored
            if (isset($data["is_default_json"]) && isset($data["data"])) {
                return [
                    "from_db" => false,
                    "data"    => $data["data"]
                ];
            }

            // Detect default fallback (prevents caching)
            // CASE 1: Model returned fallback defaults (contains is_default)
            if (isset($data["is_default"]) && $data["is_default"] === true) {
                return [
                    "from_db" => false,
                    "data"    => $data
                ];
            }

            // Valid DB rows returned
            if (!empty($data)) {
                return ["from_db" => true, "data" => $data];
            }

            // Empty → use fallback
            return ["from_db" => false, "data" => $this->about->fallback($label)];

        } catch (Throwable $e) {
            app_log("AboutController: Failed loading section {$label}: " . $e->getMessage(), "warning");
            return ["from_db" => false, "data" => $this->about->fallback($label)];
        }
    }

    /**
     * Returns TRUE only when ALL sections successfully loaded
     * REAL database content (each has from_db = true)
    */
    private function hasRealData(array $sections): bool
    {
        foreach ($sections as $section) {
            if (!isset($section["from_db"]) || $section["from_db"] !== true) {
                return false;
            }
        }
        return true;
    }
}
