<?php
namespace app\Controllers;

use app\Core\Controller;
use app\Models\AboutModel;
use app\Services\CacheService;
use Throwable;


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
                $cached['safe_mode'] = false;
                return $this->view("pages/about", $cached); // Return cached version immediately
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
                "safe_mode" => false,

                "hero"       => $this->wrap($this->about->getHero()),
                "content"    => $this->wrap($this->about->getContent()),
                "skills"     => $this->wrap($this->about->getSkills()),
                "experience" => $this->wrap($this->about->getExperience()),
                "education"  => $this->wrap($this->about->getEducation()),
                "stats"      => $this->wrap($this->about->getStats()),
            ];

            /** ---------------------------------------------------
             * C. Save full-page cache ONLY when ALL sections came
             *    from real DB calls (prevents caching defaults)
             * --------------------------------------------------- */
            if ($this->hasRealData($data)) {
                CacheService::save($this->cacheKey, $data);
            }

            return $this->view("pages/about", $data);

        } catch (Throwable $e) {

            app_log(
                "SAFE MODE ACTIVATED — AboutController@index: " . $e->getMessage(),
                "critical"
            );

            /** ---------------------------------------------------
             * D. Emergency fallback (controller-level protection)
             * --------------------------------------------------- */
            return $this->view("pages/about", [
                "safe_mode" => true,

                // Hero ALWAYS exists
                "hero"       => ["from_db" => false, "data" => $this->about->defaultHero()],

                // Disable rest
                "content"    => ["from_db" => false, "data" => []],
                "skills"     => ["from_db" => false, "data" => []],
                "experience" => ["from_db" => false, "data" => []],
                "education"  => ["from_db" => false, "data" => []],
                "stats"      => ["from_db" => false, "data" => []],
            ]);
        }
    }

    /* ============================================================
     * SECTION LOADER (safe wrapper)
     * ============================================================ */


    private function wrap(array $payload): array
    {
        return [
            "from_db" => ($payload["source"]) === "db",
            "data"    => $payload["data"]
        ];
    }


    /**
     * Returns TRUE only when ALL sections successfully loaded
     * REAL database content (each has from_db = true)
    */
    private function hasRealData(array $sections): bool
    {
        foreach ($sections as $key => $section) {
            if ($key === "safe_mode") continue;

            if (($section["from_db"] ?? false) !== true) {
                return false;
            }
        }
        return true;
    }
}
