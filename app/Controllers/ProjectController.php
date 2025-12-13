<?php
namespace app\Controllers;

use app\core\Controller;
use app\Models\ProjectModel;
use app\Services\CacheService;
use Throwable;
class ProjectController extends Controller
{
    private ProjectModel $projects;

    /** Full page cache - dynamic per query */
    private string $cacheKey;

    public function __construct()
    {

        $this->projects = new ProjectModel();
    }

    /**
     * Controller entry point for projects listing page
     */
    public function index()
    {
        try {
            /* -----------------------------------------------
             * 0. BUILD DYNAMIC CACHE KEY PER PAGE + FILTERS
             * ----------------------------------------------- */
            $page     = isset($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;
            $tech     = isset($_GET["tech"]) && $_GET["tech"] !== "" ? trim(strip_tags($_GET["tech"])) : null;
            $featured = isset($_GET["featured"]) ? 1 : 0;

            $this->cacheKey = "projects_page_" . md5(json_encode([
                "p" => $page,
                "tech" => $tech,
                "featured" => $featured
            ]));

            /* ---------------------------------------------------
             * 1. FULL PAGE CACHE
             * --------------------------------------------------- */
            if ($cached = CacheService::load($this->cacheKey)) {
                return $this->view("pages/projects", $cached);
            }

            /* ---------------------------------------------------
             * 2. Load project listing (includes pagination & filters)
             *    This internally follows A->B->C->D
             * --------------------------------------------------- */
            $projectData = $this->safeLoad(
                fn() => $this->projects->getPaginatedProjects(),
                "projects"
            );

            /* ---------------------------------------------------
             * 3. Load tech list (for badges) - follows A->B->C->D
             * --------------------------------------------------- */
            $techData = $this->safeLoad(
                fn() => $this->projects->getAllTechStructured(),
                "tech"
            );

            /* ---------------------------------------------------
             * 4. FINAL response (view always gets perfect data)
             * --------------------------------------------------- */
            // ensure shape even if fallback returned different shaped data
            $proj = $projectData["data"];
            $final = [
                "projects"   => $proj["items"] ?? [],
                "techList"   => $techData["data"] ?? [],
                "page"       => $proj["page"] ?? 1,
                "totalPages" => $proj["totalPages"] ?? 1,
                "total"      => $proj["total"] ?? 0,
                "filters"    => $proj["filters"] ?? ["tech" => $tech, "featured" => (bool)$featured],
            ];

            /* ---------------------------------------------------
             * 5. Save CACHE only when ALL sections came from DB
             * --------------------------------------------------- */
            if ($this->hasRealData([
                "projects" => $projectData,
                "techList" => $techData
            ])) {
                CacheService::save($this->cacheKey, $final);
            }

            return $this->view("pages/projects", $final);

        } catch (Throwable $e) {

            app_log("ProjectController@index ERROR: " . $e->getMessage(), "error");

            return $this->view("pages/projects", [
                "projects"   => $this->projects->defaultProjects(),
                "techList"   => $this->projects->defaultTechList(),
                "page"       => 1,
                "totalPages" => 1,
                "total"      => count($this->projects->defaultProjects()),
                "filters"    => [
                    "tech" => null,
                    "featured" => false
                ],
            ]);
        }
    }


    /* ============================================================
     * SAFE LOAD (same structure as AboutController)
     * ============================================================ */
    private function safeLoad(callable $fn, string $label): array
    {
        try {
            $data = $fn();

            // not an array → fallback
            if (!is_array($data)) {
                return ["from_db" => false, "data" => $this->projects->fallback($label)];
            }

            // JSON default marker
            if (isset($data["is_default"]) && $data["is_default"]) {
                return ["from_db" => false, "data" => $data];
            }

            // DB result (non-empty)
            if (!empty($data)) {
                return ["from_db" => true, "data" => $data];
            }

            // empty → fallback
            return ["from_db" => false, "data" => $this->projects->fallback($label)];

        } catch (Throwable $e) {
            app_log("ProjectController safeLoad({$label}) FAILED: " . $e->getMessage(), "warning");
            return ["from_db" => false, "data" => $this->projects->fallback($label)];
        }
    }

    /**
     * Controller entry point for individual project detail page
     */
    public function show(string $slug)
    {
        // 🔍 TEMP DEBUG (remove after test)
        app_log("Project slug requested: " . $slug, "debug");

        $project = $this->projects->getBySlug($slug);

        if (!$project) {
            http_response_code(404);
            echo "<h1>404 - Project not found</h1>";
            exit;
        }

        return $this->view("pages/project-detail", [
            "project" => $project
        ]);
    }

    /** TRUE only when ALL sections were from DB */
    private function hasRealData(array $sections): bool
    {
        foreach ($sections as $section) {
            if (!isset($section["from_db"]) || $section["from_db"] !== true) {
                return false;
            }
        }
        return true;
    }

    public function abort(int $code, string $message = "")
    {
        http_response_code($code);
        echo "<h1>{$code} - {$message}</h1>";
        exit;
    }
}
