<?php

class ProjectController extends Controller
{
    private ProjectModel $model;

    public function __construct()
    {
        require_once ROOT_PATH . "app/Models/ProjectModel.php";
        require_once ROOT_PATH . "app/Services/CacheService.php";

        $this->model = new ProjectModel();
    }

    /**
     * Controller entry point for projects listing page
     */
    public function index()
    {
        try{
            // Get request inputs
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage  = 12;
            $offset   = ($page - 1) * $perPage;

            // tech — only allow safe strings
            $tech = isset($_GET['tech']) ? trim(strip_tags($_GET['tech'])) : null;
            if ($tech === "") $tech = null;

            // featured filter
            $featured = isset($_GET['featured']) ? true : false;

            /** ---------------------------------------------------
            *  2. CACHE KEY BASED ON PAGE + FILTERS
            * --------------------------------------------------- */

            $cacheKey = "projects_page_" . md5(json_encode([
                    "page"     => $page,
                    "perPage"  => $perPage,
                    "tech"     => $tech,
                    "featured" => $featured
            ]));

            $cached = CacheService::load($cacheKey);

            if (!empty($cached) && $this->hasRealData($cached)) {
                return $cached; // Defensive: return only if actual content exists
            }

            /** ---------------------------------------------------
            *  3. QUERY DATABASE
            * --------------------------------------------------- */
            $params = [
                "offset"   => $offset,
                "limit"    => $perPage,
                "tech"     => $tech,
                "featured" => $featured
            ];

            // Get data — model handles DB + CacheService fallback
            $result = $this->model->fetchActiveProjects($params);

            /** ---------------------------------------------------
            *  4. VALIDATE RESULT STRUCTURE
            * --------------------------------------------------- */
            if (!isset($result["items"]) || !is_array($result["items"])) {
                throw new Exception("Invalid structure returned from ProjectModel");
            }

            // Extract items + total count
            $projects   = $result["items"];
            $totalCount = isset($result["total"]) ? (int) $result["total"] : 0;
            $totalPages = max(1, (int) ceil($totalCount / $perPage));

            /** ---------------------------------------------------
            *  5. GET TECH LIST FOR EACH PROJECT (SAFE LOOP)
            * --------------------------------------------------- */
            $techList = [];
            foreach ($projects as $p) {
                // Ensure ID exists
                if (!isset($p['id'])) continue;

                try {
                    $techList[$p['id']] = $this->model->getTechList($p['id']);
                } catch (Throwable $innerErr) {
                    app_log("Failed loading tech list for project ID {$p['id']}: " . $innerErr->getMessage(), "warning");
                    $techList[$p['id']] = [];
                }
            }

            /** ---------------------------------------------------
            *  6. PROPER FINAL DATA STRUCTURE
            * --------------------------------------------------- */
            $final = [
                "projects"   => $projects,
                "techList"   => $techList,
                "page"       => $page,
                "totalPages" => $totalPages,
                "perPage"    => $perPage,
                "total"      => $totalCount,
                "filters"    => [
                    "tech"     => $tech,
                    "featured" => $featured
                ]
            ];

            /** ---------------------------------------------------
            *  7. SAVE TO CACHE ONLY IF REAL CONTENT EXISTS
            * --------------------------------------------------- */
            if ($this->hasRealData($final)) {
                CacheService::save($cacheKey, $final);
            }

            return $final;
        } catch (Throwable $e) {
            /** ---------------------------------------------------
            *  8. EMERGENCY FALLBACK (NO HARD FAIL)
            * --------------------------------------------------- */
            app_log("ProjectController@index failed: " . $e->getMessage(), "error");

            return [
                "projects"   => [],
                "techList"   => [],
                "page"       => 1,
                "totalPages" => 1,
                "perPage"    => 12,
                "total"      => 0,
                "filters"    => [
                    "tech"     => null,
                    "featured" => false
                ]
            ];
        }
    }

    /**
     * Checks if there is at least some meaningful data
     */
    private function hasRealData(array $data): bool
    {
        foreach ($data as $section) {
            if (!empty($section)) {
                return true;
            }
        }
        return false;
    }
}
