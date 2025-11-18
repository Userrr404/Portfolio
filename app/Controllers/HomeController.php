<?php

class HomeController extends Controller
{
    private HomeModel $home;
    private AboutModel $about;
    private SkillModel $skills;
    private ProjectModel $projects;
    private ContactModel $contact;

    private string $cacheKey = "home_page";

    public function __construct()
    {
        // Load all models
        require_once ROOT_PATH . "app/Models/HomeModel.php";
        require_once ROOT_PATH . "app/Models/AboutModel.php";
        require_once ROOT_PATH . "app/Models/SkillModel.php";
        require_once ROOT_PATH . "app/Models/ProjectModel.php";
        require_once ROOT_PATH . "app/Models/ContactModel.php";
        require_once ROOT_PATH . "app/Services/CacheService.php";

        $this->home     = new HomeModel();
        $this->about    = new AboutModel();
        $this->skills   = new SkillModel();
        $this->projects = new ProjectModel();
        $this->contact  = new ContactModel();
    }

    /**
     * Home page controller
     * Returns ALL SECTIONS as cached + DB fallback data.
     */
    public function index()
    {
        try {

            /* ---------------------------------------------------
             * 1. Try loading full page from cache
             * --------------------------------------------------- */
            $cached = CacheService::load($this->cacheKey);

            if (!empty($cached) && $this->hasRealData($cached)) {
                return $cached;  // cache hit
            }

            /* ---------------------------------------------------
             * 2. SAFE DB LOAD (per section)
             * --------------------------------------------------- */
            $data = [
                "home"     => $this->safeLoad(fn() => $this->home->get(),       "home"),
                "about"    => $this->safeLoad(fn() => $this->about->get(),      "about"),
                "skills"   => $this->safeLoad(fn() => $this->skills->all(),     "skills"),
                "projects" => $this->safeLoad(fn() => $this->projects->getFeatured(), "projects"),
                "contact"  => $this->safeLoad(fn() => $this->contact->get(),    "contact"),
            ];

            /* ---------------------------------------------------
             * 3. Save to cache only if meaningful
             * --------------------------------------------------- */
            if ($this->hasRealData($data)) {
                CacheService::save($this->cacheKey, $data);
            }

            return $data;

        } catch (Throwable $e) {

            /* ---------------------------------------------------
             * 4. PAGE-WIDE EMERGENCY FALLBACK
             * --------------------------------------------------- */
            app_log("HomeController@index FAILED: " . $e->getMessage(), "error");

            return [
                "home"     => $this->fallback("home"),
                "about"    => $this->fallback("about"),
                "skills"   => [],
                "projects" => [],
                "contact"  => $this->fallback("contact"),
            ];
        }
    }


    /* ============================================================
     * SECTION WRAPPERS (safe load)
     * ============================================================ */

    /**
     * Safely loads a model section.
     * Prevents any model failure from breaking the home page.
     */
    private function safeLoad(callable $fn, string $label)
    {
        try {
            $value = $fn();
            return !empty($value) ? $value : $this->fallback($label);
        } catch (Throwable $e) {
            app_log("HomeController: Failed loading section {$label}: " . $e->getMessage(), "warning");
            return $this->fallback($label);
        }
    }


    /* ============================================================
     * FALLBACKS
     * ============================================================ */

    /**
     * Returns minimal guaranteed-safe fallback for each section
     */
    private function fallback(string $section)
    {
        return match ($section) {

            "home" => [
                "title"       => "Welcome",
                "subtitle"    => SITE_TITLE,
                "button_text" => "Explore",
            ],

            "about" => [
                "greeting_title" => "About Me",
            ],

            "contact" => [
                "email" => SUPPORT_EMAIL ?? "contact@example.com",
            ],

            default => []
        };
    }


    /* ============================================================
     * REAL DATA CHECK
     * Prevents cache poisoning & ensures non-empty payload
     * ============================================================ */

    private function hasRealData(array $data): bool
    {
        foreach ($data as $section) {
            if (!empty($section)) return true;
        }
        return false;
    }
}
