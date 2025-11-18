<?php

/**
 * ProjectModel
 *
 * Enterprise-grade project model with:
 * - DB → cache → defaults fallback
 * - Zero-failure architecture
 * - Strong defensive layers
 * - Pagination with safe filtering
 */


class ProjectModel {
    private int $defaultTTL = 3600; // 1 hour cache


    public function __construct(){
        require_once ROOT_PATH . "app/Services/CacheService.php";
    }

    /**
     * Load multiple rows with defaults fallback
     */
    private function loadMultiple(string $key, callable $dbFn, callable $defaultFn): array
    {
        // 1. CACHE
        if ($cached = CacheService::load($key)) {
            return $cached;
        }

        // 2. DB
        try {
            $rows = $dbFn();
            if (!empty($rows)) {
                CacheService::save($key, $rows, $this->defaultTTL);
                return $rows;
            }
        } catch (Throwable $e) {
            app_log("ProjectModel loadMultiple error ({$key}): " . $e->getMessage(), "error");
        }

        // 3. DEFAULTS
        return $defaultFn();
    }


    /**
     * Load a single record table with defaults
     */
    private function loadSingle(string $key, callable $dbFn, callable $defaultFn): array
    {
        if ($cached = CacheService::load($key)) {
            return $cached;
        }

        try {
            $row = $dbFn();
            if (!empty($row)) {
                CacheService::save($key, $row, $this->defaultTTL);
                return $row;
            }
        } catch (Throwable $e) {
            app_log("ProjectModel loadSingle error ({$key}): " . $e->getMessage(), "error");
        }

        return $defaultFn();
    }


    /* ============================================================
    * FEATURED PROJECTS (Home page)
    * ============================================================ */

    public function getFeatured(): array
    {
        return $this->loadMultiple(
            "projects_featured",

            // DB FUNCTION
            function () {
                $pdo = DB::getInstance()->pdo();
                $stmt = $pdo->prepare("
                    SELECT *
                    FROM projects
                    WHERE is_active = 1 AND is_featured = 1
                    ORDER BY sort_order ASC, id DESC
                ");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            },

            // DEFAULTS FUNCTION
            function () {
                return $this->defaultProjects();
            }
        );
    }

    /* ============================================================
     * ALL ACTIVE PROJECTS (fallback layer)
     * ============================================================ */

    public function getAllActive(): array
    {
        return $this->loadMultiple(
            "projects_all",

            function () {
                $pdo = DB::getInstance()->pdo();
                $stmt = $pdo->prepare("
                    SELECT *
                    FROM projects
                    WHERE is_active = 1
                    ORDER BY sort_order ASC, id DESC
                ");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            },

            function () {
                return $this->defaultProjects();
            }
        );
    }

    /* ============================================================
     * MAIN QUERY: Pagination + Filters (works even if DB missing)
     * ============================================================ */

    public function fetchActiveProjects(array $params): array
    {
        $offset   = $params['offset'] ?? 0;
        $limit    = $params['limit'] ?? 12;
        $tech     = $params['tech'] ?? null;
        $featured = $params['featured'] ?? false;

        try {
            /* -------- Build WHERE -------- */
            $where = "WHERE p.is_active = 1";
            $bind  = [];
            $join  = "";

            if ($featured) {
                $where .= " AND p.is_featured = 1";
            }

            if ($tech) {
                $join = "LEFT JOIN project_tech pt ON pt.project_id = p.id";
                $where .= " AND pt.tech_name LIKE :tech";
                $bind[":tech"] = "%{$tech}%";
            }

            $sql = "
                SELECT SQL_CALC_FOUND_ROWS p.*
                FROM projects p
                {$join}
                {$where}
                ORDER BY p.sort_order ASC, p.id DESC
                LIMIT :limit OFFSET :offset
            ";

            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->prepare($sql);

            foreach ($bind as $k => $v) {
                $stmt->bindValue($k, $v);
            }

            $stmt->bindValue(":limit",  (int)$limit,  PDO::PARAM_INT);
            $stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);

            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $total = (int)($pdo->query("SELECT FOUND_ROWS()")->fetchColumn() ?? 0);

            return [
                "items" => $items,
                "total" => $total
            ];
        }

        catch (Throwable $e) {
            app_log("ProjectModel fetchActiveProjects DB ERROR: " . $e->getMessage(), "error");
        }

        /* ============================================================
         * DB FAILURE → FALLBACK TO CACHED ALL PROJECTS OR DEFAULTS
         * ============================================================ */

        $fallback = CacheService::load("projects_all");

        if (empty($fallback)) {
            $fallback = $this->defaultProjects(); // ultimate fallback
        }

        // Apply filters manually
        $filtered = $fallback;

        if ($featured) {
            $filtered = array_filter($filtered, fn($p) => ($p['is_featured'] ?? 0) == 1);
        }

        if ($tech) {
            $filtered = array_filter($filtered, fn($p) =>
                stripos($p['technologies'] ?? "", $tech) !== false
            );
        }

        $total = count($filtered);
        $items = array_slice($filtered, $offset, $limit);

        return [
            "items" => array_values($items),
            "total" => $total
        ];
    }

    /* ============================================================
     * TECH LIST (safe fallback)
     * ============================================================ */

    public function getTechList(int $projectId): array
    {
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->prepare("
                SELECT tech_name, color_class
                FROM project_tech
                WHERE project_id = ?
            ");
            $stmt->execute([$projectId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
        catch (Throwable $e) {
            app_log("ProjectModel getTechList ERROR: " . $e->getMessage(), "error");
            return []; // always safe
        }
    }

    /* ============================================================
     * DEFAULT PROJECT LIST (non-empty guaranteed)
     * ============================================================ */

    public function defaultProjects(): array
    {
        return [
            [
                "id" => 0,
                "title" => "Portfolio Website",
                "description" => "Dynamic PHP + MySQL website with enterprise caching, controllers & models.",
                "image_path" => IMG_URL . "default-project.jpg",
                "project_link" => "#",
                "sort_order" => 1,
                "is_featured" => 1
            ],
            [
                "id" => 0,
                "title" => "E-Commerce Backend",
                "description" => "Cart system, product management, authentication & admin panel.",
                "image_path" => IMG_URL . "default-project.jpg",
                "project_link" => "#",
                "sort_order" => 2,
                "is_featured" => 0
            ],
            [
                "id" => 0,
                "title" => "Analytics Dashboard",
                "description" => "Tailwind + PHP analytics dashboard with charts & API integration.",
                "image_path" => IMG_URL . "default-project.jpg",
                "project_link" => "#",
                "sort_order" => 3,
                "is_featured" => 0
            ]
        ];
    }
}
