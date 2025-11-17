<?php

require_once ROOT_PATH . "app/Services/CacheService.php";

class ProjectModel {

    private $cacheKeyFeatured = "featured_projects";
    private $cacheKeyAll = "projects_all";

    public function featured()
    {
        if ($cache = CacheService::load($this->cacheKeyFeatured)) return $cache;

        try {
            $rows = safe_fetch_all(
                safe_query("SELECT * FROM projects WHERE is_active = 1 AND is_featured = 1 ORDER BY id DESC")
            );

            CacheService::save($this->cacheKeyFeatured, $rows);
            return $rows;

        } catch (Throwable $e) {
            app_log("ProjectModel:featured() failed", "error", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch ALL active projects using CacheService
     */
    public function getAllActive()
    {
        if ($cache = CacheService::load($this->cacheKeyAll))
            return $cache;

        try {
            $rows = safe_fetch_all(
                safe_query("SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order ASC, id DESC")
            );

            CacheService::save($this->cacheKeyAll, $rows);
            return $rows;

        } catch (Throwable $e) {
            app_log("ProjectModel:getAllActive() failed", "error", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Pagination + tech filtering + featured filtering
     */
    public function fetchActiveProjects($params = [])
    {
        $offset   = $params['offset'] ?? 0;
        $limit    = $params['limit'] ?? 12;
        $tech     = $params['tech'] ?? null;
        $featured = $params['featured'] ?? false;

        $where = "WHERE p.is_active = 1";
        $bind  = [];

        if ($featured)
            $where .= " AND p.is_featured = 1";

        $join = "";

        if ($tech) {
            $join = "LEFT JOIN project_tech pt ON pt.project_id = p.id";
            $where .= " AND pt.tech_name LIKE :tech";
            $bind[':tech'] = "%{$tech}%";
        }

        $sql = "
            SELECT SQL_CALC_FOUND_ROWS p.*
            FROM projects p
            $join
            $where
            ORDER BY p.sort_order ASC, p.id DESC
            LIMIT :limit OFFSET :offset
        ";

        try {
            $st = safe_prepare($sql);

            foreach ($bind as $key => $value)
                safe_bind($st, $key, $value);

            safe_bind($st, ':limit',  (int)$limit,  PDO::PARAM_INT);
            safe_bind($st, ':offset', (int)$offset, PDO::PARAM_INT);

            safe_execute($st);

            $items = safe_fetch_all($st);
            $total = safe_fetch(safe_query("SELECT FOUND_ROWS()"))["FOUND_ROWS()"] ?? 0;

            return ["items" => $items, "total" => $total];

        } catch (Throwable $e) {

            app_log("ProjectModel:fetchActiveProjects failed", "error", [
                "error"  => $e->getMessage(),
                "params" => $params
            ]);

            // FALLBACK to cached "all projects"
            $all = CacheService::load($this->cacheKeyAll);
            if (!$all) return ["items" => [], "total" => 0];

            // Filter in PHP fallback
            if ($featured)
                $all = array_filter($all, fn($p) => $p['is_featured'] == 1);

            if ($tech)
                $all = array_filter($all, fn($p) => stripos($p['technologies'] ?? "", $tech) !== false);

            $total = count($all);
            $items = array_slice($all, $offset, $limit);

            return ["items" => $items, "total" => $total];
        }
    }

    /**
     * Fetch project's tech list
     */
    public function getTechList($projectId)
    {
        try {
            return safe_fetch_all(
                safe_query("SELECT tech_name, color_class FROM project_tech WHERE project_id = ?", [$projectId])
            );

        } catch (Throwable $e) {
            app_log("ProjectModel:getTechList() failed", "error", ['project_id' => $projectId]);
            return [];
        }
    }
}
