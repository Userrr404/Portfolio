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
                $cached['safe_mode'] = false;
                return $this->view("pages/projects", $cached);
            }

            /* -----------------------------------------------
             * 2. LOAD PRIMARY ENTITY (PROJECTS)
             * ----------------------------------------------- */
            $projects = $this->wrap(
                $this->projects->getPaginatedProjects()
            );

            /* -----------------------------------------------
             * 3. NORMALIZE SOURCE
             * cache is DB-derived
             * ----------------------------------------------- */
            if ($projects["source"] === "cache") {
                $projects["source"] = "db";
            }

            /* -----------------------------------------------
             * 4. 🔒 STRICT SOURCE LOCK (THIS IS THE FIX)
             * ----------------------------------------------- */
            $tech = $this->projects->getTechBySource($projects["source"]);

            app_log(
                "ProjectController source locked: projects={$projects["source"]}, tech={$tech["source"]}",
                "debug"
            );

            /* ---------------------------------------------------
             * 5. FINAL VIEW DATA
             * --------------------------------------------------- */
            $proj = $projects["data"];

            $final = [
                "safe_mode"  => false,
                "projects"   => $proj["items"] ?? [],
                "techList"   => $tech["data"] ?? [],
                "page"       => $proj["page"] ?? 1,
                "totalPages" => $proj["totalPages"] ?? 1,
                "total"      => $proj["total"] ?? 0,
                "filters"    => $proj["filters"] ?? ["tech" => $tech, "featured" => (bool)$featured],
            ];

            /* ---------------------------------------------------
             * 5. Save CACHE only when ALL sections came from DB
             * --------------------------------------------------- */
            if ($projects["source"] === "db" && $tech["source"] === "db") {
                CacheService::save($this->cacheKey, $final);
            }

            return $this->view("pages/projects", $final);

        } catch (Throwable $e) {

            app_log("SAFE MODE — ProjectController@index: " . $e->getMessage(), "critical");

            return $this->view("pages/projects", [
                "safe_mode"  => true,
                "projects"   => [],
                "techList"   => [],
                "page"       => 1,
                "totalPages" => 1,
                "total"      => 0,
                "filters"    => [],
            ]);
        }
    }

    // Replace with safeload()
    private function wrap(array $payload): array
    {
        return [
            "source" => $payload["source"] ?? "fallback",
            "data"   => $payload["data"] ?? []
        ];
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
            if ($section["source"] !== "db" ) {
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
