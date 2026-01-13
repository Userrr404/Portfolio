<?php
namespace app\Models;

use PDO;
use app\Services\CacheService;
use app\Core\DB;
use Throwable;

class SkillModel {

    private string $cacheKey = "skills";
    private ?string $defaultJson = null;

    public function __construct()
    {
        // CONFIG-SAFE resolution happens at runtime
        $this->defaultJson = safe_path('HOME_SKILLS_DEFAULT_FILE');
    }    
    
    public function all(bool $pure = false): array
    {
        return $pure ? $this->getOnlyDB() : $this->getFallbackMode();
    }

    /* ============================================================
     * DB ONLY (NO FALLBACK MIXING)
     * ============================================================ */
    private function getOnlyDB(): array
    {
        try {
            $pdo = DB::getInstance()->pdo();

            if (!$pdo) {
                app_log("DC-03: SkillModel@getOnlyDB DB unavailable", "error");
                return [
                    "source" => "empty",
                    "data"   => []
                ];
            }

            $stmt = $pdo->prepare("
                SELECT skill_name, icon_class, color_class
                FROM skills
                WHERE is_active = 1
                ORDER BY id ASC
            ");
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (!empty($rows)) {
                CacheService::save($this->cacheKey, $rows);
                return [
                    "source" => "db",
                    "data"   => $rows
                ];
            }

            return [
                "source" => "empty",
                "data"   => []
            ];

        } catch (Throwable $e) {
            app_log("SkillModel@all DB error: " . $e->getMessage(), "error");

            return [
                "source" => "error",
                "data"   => []
            ];
        }
    }

    /* ============================================================
     * FALLBACK MODE — IDENTICAL TO HomeModel
     * ============================================================ */
    private function getFallbackMode(): array
    {
        /** A. Cache */
        if ($cache = CacheService::load($this->cacheKey)) {
            return [
                "source" => "cache",
                "data"   => $cache
            ];
        }

        /** B. DB */
        $row = $this->getOnlyDB();
        if ($row["source"] === "db") {
            CacheService::save($this->cacheKey, $row["data"]);
            return $row;
        }

        /** C. JSON DEFAULT (PRIMARY FALLBACK) */
        if ($row["source"] === "empty") {
            if ($this->defaultJson && file_exists($this->defaultJson)) {
                $json = json_decode(file_get_contents($this->defaultJson), true);

                if (!empty($json) && is_array($json)) {
                    return [
                        "source" => "json",
                        "data"   => $json
                    ];
                }
            }
        }

        /** ----------------------------------------------------
         * D. HARD-CODED DEFAULTS
         * ----------------------------------------------------*/
        return [
            "source" => "fallback",
            "data"   => $this->defaults()
        ];
    }

    /**
     * D. Hard-coded fallback data
     */
    private function defaults(): array
    {
        return [
            [
                "is_default"  => true,
                "skill_name"  => "DHTML5",
                "icon_class"  => "fa-brands fa-html5",
                "color_class" => "text-orange-500"
            ],
            [
                "is_default"  => true,
                "skill_name"  => "CSS3",
                "icon_class"  => "fa-brands fa-css3-alt",
                "color_class" => "text-blue-500"
            ],
            [
                "is_default"  => true,
                "skill_name"  => "JavaScript",
                "icon_class"  => "fa-brands fa-js",
                "color_class" => "text-yellow-400"
            ],
            [
                "is_default"  => true,
                "skill_name"  => "PHP",
                "icon_class"  => "fa-brands fa-php",
                "color_class" => "text-indigo-400"
            ],
            [
                "is_default"  => true,
                "skill_name"  => "MySQL",
                "icon_class"  => "fa-solid fa-database",
                "color_class" => "text-teal-400"
            ],
            [
                "is_default"  => true,
                "skill_name"  => "Tailwind CSS",
                "icon_class"  => "fa-solid fa-wind",
                "color_class" => "text-cyan-400"
            ],
            [
                "is_default"  => true,
                "skill_name"  => "Git & GitHub",
                "icon_class"  => "fa-brands fa-git-alt",
                "color_class" => "text-orange-600"
            ],
        ];
    }
}
