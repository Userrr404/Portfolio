<?php

class AboutModel {

    private $cacheKey = "about";

    public function __construct() {
        require_once ROOT_PATH . "app/Services/CacheService.php";
    }

    public function get()
    {
        if ($cache = CacheService::load($this->cacheKey)) return $cache;

        $row = safe_fetch(safe_query("SELECT * FROM about_section WHERE is_active = 1 LIMIT 1"));

        if (!$row) $row = [
            "title" => "About Me",
            "content" => "Hi, I'm Yogesh. I build optimized, scalable and user-friendly applications."
        ];

        CacheService::save($this->cacheKey, $row);

        return $row;
    }

    /**
     * =========================================
     * 2. For ABOUT PAGE (about.php)
     * Full dynamic sections
     * =========================================
     */

    public function getHero() {
        return safe_fetch(
            safe_query("SELECT * FROM about_hero WHERE is_active = 1 LIMIT 1")
        ) ?: [];
    }

    public function getContent() {
        return safe_fetch(
            safe_query("SELECT * FROM about_content WHERE is_active = 1 LIMIT 1")
        ) ?: [];
    }

    public function getSkills() {
        return safe_query("SELECT * FROM about_skills WHERE is_active = 1 ORDER BY sort_order ASC")
               ->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function getExperience() {
        return safe_query("SELECT * FROM about_experience WHERE is_active = 1 ORDER BY sort_order ASC")
               ->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function getEducation() {
        return safe_query("SELECT * FROM about_education WHERE is_active = 1 ORDER BY sort_order ASC")
               ->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    public function getStats() {
        return safe_query("SELECT * FROM about_stats WHERE is_active = 1 ORDER BY sort_order ASC")
               ->fetch_all(MYSQLI_ASSOC) ?: [];
    }
}
