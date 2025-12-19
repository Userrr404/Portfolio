<?php
namespace app\Models;

use PDO;
use app\Services\CacheService;
use app\Core\DB;
use Throwable;

class ProjectModel {
    // private int $defaultTTL = 3600; // 1 hour cache

    public function __construct()
    {
        // require_once CACHESERVICE_FILE;
        
    }

    /* ============================================================
    * FEATURED PROJECTS (Home page)
    * ============================================================ */
    public function getFeatured()
    {
        $cacheKey = "featured_projects";

        if ($cache = CacheService::load($cacheKey)) {
            return $cache;
        }

        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("SELECT * FROM projects WHERE is_active = 1 AND is_featured = 1 ORDER BY sort_order ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                CacheService::save($cacheKey, $rows);
                return $rows;
            }

        } catch (Throwable $e) {
            app_log("ProjectModel getFeatured ERROR: " . $e->getMessage(), "error");
        }

        /** ----------------------------------------------------
        * C. TRY DEFAULT JSON FILE
        * ----------------------------------------------------*/
        $jsonFile = HOME_PROJECTS_DEFAULT_FILE;

        if (file_exists($jsonFile)) {
            $json = json_decode(file_get_contents($jsonFile), true);
            if (!empty($json) && is_array($json)) {
                return $json;
            }
        }

        /** ----------------------------------------------------
        * D. HARD-CODED FALLBACK
        * ----------------------------------------------------*/
        return $this->defaultFeatured();

    }

    /**
    * Default fallback for FEATURED projects only
    */
    public function defaultFeatured(): array
    {
        return [
            [
                "id" => 0,
                "title"=> "DD Portfolio Website",
                "slug"=> "personal-portfolio",
                "short_desc"=> "Modern developer portfolio website",
                "description" => "Dynamic PHP + MySQL website with caching, controllers, models & animations.",
                "full_desc"=> "This project demonstrates a full MVC PHP architecture with routing, caching, controllers, models, and clean UI animations.",
                "image_path"=> "portfolio.png",
                "cover_image"=> null,
                "github_url"=> "https://github.com/Yogesh-Lilake/Portfolio",
                "live_url"=> "https://github.com/Yogesh-Lilake/Portfolio",
                "project_link"=> "#",
                "is_featured"=> 1,
                "sort_order"=> 1,
                "is_default"=> true
            ],
            [
                "id"=> 0,
                "title"=> "D E-Commerce Backend",
                "slug"=> "footwear-ecommerce",
                "short_desc"=> "",
                "description"=> "A full-stack online store built with PHP, MySQL, and Razorpay integration. Features secure checkout, admin dashboard, and product management.",
                "full_desc"=> "",
                "image_path"=> "footwear.png",
                "cover_image"=> null,
                "github_url"=> null,
                "live_url"=> null,
                "project_link"=> "#",
                "is_featured"=> 1,
                "sort_order"=> 2,
                "is_default"=> true
            ]
        ];

    }




    /* ============================================================
     * MAIN PAGINATION + FILTERING (Enterprise Version)
     * Follows: A. Cache → B. DB → C. default JSON → D. fallback
     * ============================================================ */
    public function getPaginatedProjects(): array
    {
        /* ------- Get query params (always sanitized) ------- */
        $page     = isset($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;
        $perPage  = 3; // change as needed
        $offset   = ($page - 1) * $perPage;

        $tech     = isset($_GET["tech"]) ? trim(strip_tags($_GET["tech"])) : null;
        if ($tech === "") $tech = null;

        $featured = isset($_GET["featured"]) ? true : false;

        /* ------- Build cache key for this dataset ------- */
        $cacheKey = "projects_list_" . md5(json_encode([
            "p" => $page, "per" => $perPage, "tech" => $tech, "featured" => $featured
        ]));

        /* ----------------------------
         * A. Try cache
         * ---------------------------- */
        if ($cached = CacheService::load($cacheKey)) {
            return $cached;
        }

        /* ----------------------------
         * B. Try DB
         * ---------------------------- */
        try {
            $pdo = DB::getInstance()->pdo();

            $where = "WHERE p.is_active = 1";
            $bind  = [];
            $join  = "";

            if ($featured) {
                $where .= " AND p.is_featured = 1";
            }

            if ($tech) {
                $join = "LEFT JOIN project_tech1 t ON t.project_id = p.id";
                $where .= " AND t.tech_name LIKE :tech";
                $bind[":tech"] = "%$tech%";
            }

            $sql = "
                SELECT SQL_CALC_FOUND_ROWS p.*
                FROM projects1 p
                $join
                $where
                GROUP BY p.id
                ORDER BY p.sort_order ASC, p.id DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $pdo->prepare($sql);

            foreach ($bind as $k => $v) {
                $stmt->bindValue($k, $v);
            }

            $stmt->bindValue(":limit",  $perPage, PDO::PARAM_INT);
            $stmt->bindValue(":offset", $offset,  PDO::PARAM_INT);
            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $total = (int) ($pdo->query("SELECT FOUND_ROWS()")->fetchColumn() ?? 0);

            $response = [
                "items"      => $items,
                "total"      => $total,
                "page"       => $page,
                "totalPages" => max(1, (int)ceil($total / $perPage)),
                "filters"    => ["tech" => $tech, "featured" => $featured]
            ];

            // Save only when DB returned real rows
            if (!empty($items)) {
                CacheService::save($cacheKey, $response);
            }

            return $response;

        } catch (Throwable $e) {
            app_log("ProjectModel getPaginatedProjects DB ERROR: " . $e->getMessage(), "error");
        }

        /* ----------------------------
         * C. Try default JSON file (paginated)
         * ---------------------------- */
        $jsonPath = PROJECTS_DEFAULT_FILE;
        if (file_exists($jsonPath)) {
            $all = json_decode(file_get_contents($jsonPath), true);
            if (!empty($all) && is_array($all)) {
                // paginate JSON array
                $filtered = $all;

                if ($featured) {
                    $filtered = array_filter($filtered, fn($p) => ($p['is_featured'] ?? 0) == 1);
                }

                if ($tech) {
                    $filtered = array_filter($filtered, fn($p) =>
                        stripos($p['description'] ?? '', $tech) !== false ||
                        stripos($p['title'] ?? '', $tech) !== false
                    );
                }

                $total = count($filtered);
                $items = array_slice(array_values($filtered), $offset, $perPage);

                return [
                    "items"      => array_values($items),
                    "total"      => $total,
                    "page"       => $page,
                    "totalPages" => max(1, (int)ceil($total / $perPage)),
                    "filters"    => ["tech" => $tech, "featured" => $featured],
                    "is_default" => true
                ];
            }
        }

        /* ----------------------------
         * D. Hard fallback
         * ---------------------------- */
        return $this->fallbackPaginated($page, $perPage, $tech, $featured);
    }


    /* ============================================================
     * TECH LIST STRUCTURED — A → B → C → D
     * Returns: [ project_id => [ {project_id, tech_name, color_class}, ... ], ... ]
     * ============================================================ */
    public function getAllTechStructured(): array
    {
        $cacheKey = "projects_tech_structured";

        /* A. Cache */
        if ($cached = CacheService::load($cacheKey)) {
            return $cached;
        }

        /* B. DB */
        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("SELECT project_id, tech_name, color_class FROM project_tech1 ORDER BY id ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $structured = [];
            foreach ($rows as $r) {
                $structured[(int)$r['project_id']][] = $r;
            }

            if (!empty($structured)) {
                CacheService::save($cacheKey, $structured);
                return $structured;
            }
        } catch (Throwable $e) {
            app_log("ProjectModel getAllTechStructured DB ERROR: " . $e->getMessage(), "error");
        }

        /* C. default JSON */
        $jsonPath = PROJECTS_TECHLIST_DEFAULT_FILE;
        if (file_exists($jsonPath)) {
            $json = json_decode(file_get_contents($jsonPath), true);
            if (!empty($json) && is_array($json)) {
                // mark default and return (do not cache)
                $json['is_default'] = true;
                return $json;
            }
        }

        /* D. fallback */
        return $this->defaultTechList();
    }


    /* ============================================================
     * FALLBACK PAGINATION SYSTEM (hard-coded PHP fallback)
     * ============================================================ */
    private function fallbackPaginated($page, $perPage, $tech, $featured)
    {
        $all = $this->defaultProjects();

        if ($featured) {
            $all = array_filter($all, fn($p) => ($p["is_featured"] ?? 0) == 1);
        }

        if ($tech) {
            $all = array_filter($all, fn($p) =>
                stripos($p["description"] ?? '', $tech) !== false ||
                stripos($p["title"] ?? '', $tech) !== false
            );
        }

        $total = count($all);
        $items = array_slice(array_values($all), ($page - 1) * $perPage, $perPage);

        return [
            "items"      => array_values($items),
            "total"      => $total,
            "page"       => $page,
            "totalPages" => max(1, (int)ceil($total / $perPage)),
            "filters"    => ["tech" => $tech, "featured" => $featured],
            "is_default" => true
        ];
    }


    /* ============================================================
     * FALLBACK PROVIDERS
     * ============================================================ */
    public function fallback(string $section)
    {
        return match ($section) {
            "projects" => [
                "items"      => $this->defaultProjects(),
                "total"      => count($this->defaultProjects()),
                "page"       => 1,
                "totalPages" => 1,
                "filters"    => ["tech" => null, "featured" => false],
                "is_default" => true
            ],
            "tech" => $this->defaultTechList(),
            default => []
        };
    }


    /* ============================================================
     * HARD DEFAULT PROJECTS (guaranteed non-empty)
     * Use unique IDs (0..n) so tech mapping works
     * ============================================================ */
    public function defaultProjects(): array
    {
        return [
            [
                "id" => 0,
                "title" => "D Portfolio Website",
                "slug"=> "personal-portfolio",
                "short_desc"=> "Modern developer portfolio website",
                "description" => "A modern developer portfolio built with PHP, MySQL, TailwindCSS and enterprise-level caching. Includes dynamic pages, models, controllers, and caching layers.",
                "full_desc"=> "This project demonstrates a full MVC PHP architecture with routing, caching, controllers, models, and clean UI animations.",
                "image_path" => "portfolio.png",
                "cover_image"=> null,
                "github_url"=> "https://github.com/Yogesh-Lilake/Portfolio",
                "live_url"=> "https://github.com/Yogesh-Lilake/Portfolio",
                "project_link" => "#",
                "is_featured" => 1,
                "sort_order" => 1
            ],
            [
                "id" => 1,
                "title" => "Footwear E-Commerce Website",
                "slug"=> "footwear-ecommerce",
                "short_desc"=> "",
                "description"=> "A full-stack online store built with PHP, MySQL, and TailwindCSS featuring products, cart, checkout, and admin panel.",
                "full_desc"=> "",
                "image_path"=> "footwear.png",
                "cover_image"=> null,
                "github_url"=> null,
                "live_url"=> null,
                "project_link" => "#",
                "is_featured" => 0,
                "sort_order" => 2
            ],
            [
                "id" => 2,
                "title" => "Android Expense Tracker",
                "slug"=> "android-expense-tracker",
                "short_desc"=> "",
                "description"=> "An Android app to track daily expenses, built using Java, SQLite, and chart visualizations.",
                "full_desc"=> "",
                "image_path"=> "expense.png",
                "cover_image"=> null,
                "github_url"=> null,
                "live_url"=> null,
                "project_link" => "#",
                "is_featured" => 0,
                "sort_order" => 3
            ],
            [
                "id" => 3,
                "title" => "Online Quiz Platform",
                "slug"=> "online-quiz-platform",
                "short_desc"=> "",
                "description"=> "An interactive quiz system where users can take timed quizzes and evaluate their scores instantly.",
                "full_desc"=> "",
                "image_path"=> "quiz.png",
                "cover_image"=> null,
                "github_url"=> null,
                "live_url"=> null,
                "project_link" => "#",
                "is_featured" => 0,
                "sort_order" => 4
            ],
            [
                "id" => 4,
                "title" => "Weather Forecast Web App",
                "slug"=> "weather-forecast",
                "short_desc"=> "",
                "description"=> "Displays real-time weather data fetched using OpenWeather API. Built using PHP + JavaScript.",
                "full_desc"=> "",
                "image_path"=> "weather.png",
                "cover_image"=> null,
                "github_url"=> null,
                "live_url"=> null,
                "project_link" => "#",
                "is_featured" => 0,
                "sort_order" => 5
            ],
        ];
    }


    /**
     * Default Tech List (fallback when DB fails)
     * Must return same structure as project_tech table => keyed by project_id
     */
    public function defaultTechList(): array
    {
        $defaultColor = "bg-accent/20 text-accent";

        return [
            0 => [
                ["project_id" => 0, "tech_name" => "D PHP", "color_class" => $defaultColor],
                ["project_id" => 0, "tech_name" => "MySQL", "color_class" => $defaultColor],
                ["project_id" => 0, "tech_name" => "TailwindCSS", "color_class" => $defaultColor],
                ["project_id" => 0, "tech_name" => "JavaScript", "color_class" => $defaultColor],
            ],
            1 => [
                ["project_id" => 1, "tech_name" => "PHP", "color_class" => $defaultColor],
                ["project_id" => 1, "tech_name" => "REST API", "color_class" => $defaultColor],
                ["project_id" => 1, "tech_name" => "MySQL", "color_class" => $defaultColor],
            ],
            2 => [
                ["project_id" => 2, "tech_name" => "PHP", "color_class" => $defaultColor],
                ["project_id" => 2, "tech_name" => "Chart.js", "color_class" => $defaultColor],
                ["project_id" => 2, "tech_name" => "MySQL", "color_class" => $defaultColor],
            ],
            3 => [
                ["project_id" => 3, "tech_name" => "PHP", "color_class" => $defaultColor],
                ["project_id" => 3, "tech_name" => "JavaScript", "color_class" => $defaultColor],
                ["project_id" => 3, "tech_name" => "MySQL", "color_class" => $defaultColor],
            ],
            4 => [
                ["project_id" => 4, "tech_name" => "PHP", "color_class" => $defaultColor],
                ["project_id" => 4, "tech_name" => "MySQL", "color_class" => $defaultColor],
                ["project_id" => 4, "tech_name" => "HTML/CSS", "color_class" => $defaultColor],
            ]
        ];
    }

    public function getBySlug(string $slug): ?array
{
    $cacheKey = "project_slug_" . $slug;

    if ($cache = CacheService::load($cacheKey)) {
        return $cache;
    }

    try {
        $pdo = DB::getInstance()->pdo();
        $stmt = $pdo->prepare("
            SELECT * 
            FROM projects1 
            WHERE slug = :slug AND is_active = 1 
            LIMIT 1
        ");
        $stmt->execute(['slug' => $slug]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project) {
            CacheService::save($cacheKey, $project);
            return $project;
        }
    } catch (Throwable $e) {
        app_log("ProjectModel@getBySlug DB ERROR: " . $e->getMessage(), "error");
    }

    // JSON fallback
    $jsonPath = PROJECTS_DEFAULT_FILE;
    if (file_exists($jsonPath)) {
        $json = json_decode(file_get_contents($jsonPath), true);
        foreach ($json as $p) {
            if (($p['slug'] ?? '') === $slug) {
                return $p;
            }
        }
    }

    // ✅ C. HARD FALLBACK
    foreach ($this->defaultProjects() as $p) {
        if (($p['slug'] ?? '') === $slug) {
            return $p;
        }
    }

    return null;
}

}
