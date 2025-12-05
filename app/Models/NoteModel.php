<?php

class NoteModel
{

    private string $cacheKeyNotes      = "notes_list";
    private string $cacheKeyCategories = "note_categories";
    private string $cacheKeyTags       = "note_tags";
    private string $cacheKeyPinned     = "note_pinned";

    public function __construct()
    {
        require_once CACHESERVICE_FILE;
    }


    /* ============================================================
       A. Try Cache → B. Try DB → C. Default JSON → D. Hard-coded
       ============================================================ */
    /* NOTES */
    public function getAllNotes()
    {
        // A. Try cache
        if ($cache = CacheService::load($this->cacheKeyNotes)) {
            return $cache;
        }

        // B. Try DB
        try {
            $pdo = DB::getInstance()->pdo();

            $stmt = $pdo->prepare("
                SELECT n.*, c.slug, c.name AS category_name
                FROM notes n
                JOIN note_categories c ON n.category_id = c.id
                WHERE n.is_active = 1
                ORDER BY n.created_at DESC
            ");
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                CacheService::save($this->cacheKeyNotes, $rows, 3600);
                return $rows;
            }

        } catch (Throwable $e) {
            app_log("NoteModel@getAllNotes error: " . $e->getMessage(), "error");
        }

        // C. Try default JSON
        if (file_exists(NOTES_DEFAULT_FILE)) {
            $json = json_decode(file_get_contents(NOTES_DEFAULT_FILE), true);
            if (!empty($json)) return $json;
        }

        // D. Hard fallback
        return $this->defaultNotes();
    }


    private function defaultNotes(): array
    {
        return [
            [
                "is_default"   => true,
                "title"        => "Welcome Note",
                "description"  => "Your notes page is ready! Add notes from the admin.",
                "slug"         => "general",
                "link"         => "#"
            ]
        ];
    }




    /* CATEGORIES */
    public function getCategories()
    {
        // A. Try cache
        if ($cache = CacheService::load($this->cacheKeyCategories)) {
            return $cache;
        }

        // B. Try DB
        try {
            $pdo = DB::getInstance()->pdo();

            $rows = $pdo->query("SELECT * FROM note_categories ORDER BY name ASC")
                        ->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                CacheService::save($this->cacheKeyCategories, $rows, 3600);
                return $rows;
            }

        } catch (Throwable $e) {
            app_log("NoteModel@getCategories error: " . $e->getMessage(), "error");
        }

        // C. JSON defaults
        if (file_exists(NOTES_CATEGORIES_DEFAULT_FILE)) {
            $json = json_decode(file_get_contents(NOTES_CATEGORIES_DEFAULT_FILE), true);
            if (!empty($json)) return $json;
        }

        // D. fallback
        return $this->defaultCategories();
    }


    private function defaultCategories(): array
    {
        return [
            [
                "is_default" => true,
                "name"       => "D General",
                "slug"       => "general"
            ]
        ];
    }




    /* TAGS */
    public function getTags()
    {
        // A. Try cache
        if ($cache = CacheService::load($this->cacheKeyTags)) {
            return $cache;
        }

        // B. Try DB
        try {
            $pdo = DB::getInstance()->pdo();

            $rows = $pdo->query("SELECT * FROM note_tags ORDER BY name ASC")
                        ->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                CacheService::save($this->cacheKeyTags, $rows, 3600);
                return $rows;
            }

        } catch (Throwable $e) {
            app_log("NoteModel@getTags error: " . $e->getMessage(), "error");
        }

        // C. Try default JSON
        if (file_exists(NOTES_TAGS_DEFAULT_FILE)) {
            $json = json_decode(file_get_contents(NOTES_TAGS_DEFAULT_FILE), true);
            if (!empty($json)) return $json;
        }

        // D. fallback
        return $this->defaultTags();
    }


    private function defaultTags(): array
    {
        return [
            ["is_default" => true, "name" => "general"]
        ];
    }




    /* PINNED NOTES */
    public function getPinnedNotes()
    {
        // A. Try cache
        if ($cache = CacheService::load($this->cacheKeyPinned)) {
            return $cache;
        }

        // B. Try DB
        try {
            $pdo = DB::getInstance()->pdo();

            $sql = "
                SELECT n.*, c.slug, c.name AS category_name
                FROM notes n
                JOIN note_categories c ON n.category_id = c.id
                WHERE n.is_pinned = 1
                ORDER BY n.created_at DESC
                LIMIT 6
            ";

            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                CacheService::save($this->cacheKeyPinned, $rows, 3600);
                return $rows;
            }

        } catch (Throwable $e) {
            app_log("NoteModel@getPinnedNotes error: " . $e->getMessage(), "error");
        }

        // C. Try default JSON
        if (file_exists(NOTES_PINNED_DEFAULT_FILE)) {
            $json = json_decode(file_get_contents(NOTES_PINNED_DEFAULT_FILE), true);
            if (!empty($json)) return $json;
        }

        // D. fallback
        return [];
    }
}
