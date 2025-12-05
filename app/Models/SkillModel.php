<?php

class SkillModel {

    private string $cacheKey = "skills";
    private string $defaultJson = HOME_SKILLS_DEFAULT_FILE;

    public function all()
    {
        /** ----------------------------------------------------
         * A. TRY CACHE
         * ----------------------------------------------------*/
        if ($cache = CacheService::load($this->cacheKey)) {
            return $cache;
        }

        /** ----------------------------------------------------
         * B. TRY DATABASE
         * ----------------------------------------------------*/
        try {
            $pdo = DB::getInstance()->pdo();

            $stmt = $pdo->prepare("
                SELECT skill_name, icon_class, color_class
                FROM skills
                WHERE is_active = 1
                ORDER BY id ASC
            ");
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Only if DB returned real data
            if (!empty($rows)) {
                CacheService::save($this->cacheKey, $rows);
                return $rows;
            }

        } catch (Throwable $e) {
            app_log("SkillModel DB error: " . $e->getMessage(), "error");
        }

        /** ----------------------------------------------------
         * C. TRY DEFAULT JSON FILE
         * ----------------------------------------------------*/
        if (file_exists($this->defaultJson)) {
            $json = json_decode(file_get_contents($this->defaultJson), true);

            if (!empty($json) && is_array($json)) {
                return $json;
            }
        }

        /** ----------------------------------------------------
         * D. HARD-CODED DEFAULTS
         * ----------------------------------------------------*/
        return $this->defaults();
    }

    /**
     * D. Hard-coded fallback data
     */
    private function defaults(): array
    {
        return [
            [
                "is_default"  => true,
                "skill_name"  => "HTML5",
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
