<?php

class NoteModel
{
    private string $cacheKeyNotes = "notes_list";
    private string $cacheKeyCategories = "note_categories";
    private string $cacheKeyTags = "note_tags";
    private string $cacheKeyPinned = "note_pinned";

    public function __construct()
    {
        require_once ROOT_PATH . "app/Services/CacheService.php";
    }

    /* NOTES */
    public function getAllNotes()
    {
        if ($cache = CacheService::load($this->cacheKeyNotes)) {
            return $cache;
        }

        try {
            $pdo = DB::getInstance()->pdo();

            $stmt = $pdo->prepare("
                SELECT n.*, c.slug, c.name AS category_name
                FROM notes1 n
                JOIN note_categories1 c ON n.category_id = c.id
                WHERE n.is_active = 1
                ORDER BY n.created_at DESC
            ");
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Save ONLY real DB data
            if (!empty($rows)) {
                CacheService::save($this->cacheKeyNotes, $rows, 3600);
                return $rows;
            }

            return $this->defaultNotes();

        } catch (Throwable $e) {
            app_log("NoteModel@getAllNotes error: " . $e->getMessage(), "error");
            return $this->defaultNotes();
        }
    }

    public function defaultNotes(): array
    {
        return [
            [
                "is_default" => true,
                "title" => "Welcome Note",
                "description" => "Your notes page is ready! Add notes from the admin.",
                "slug" => "general",
                "link" => "#"
            ]
        ];
    }

    /* CATEGORIES */
    public function getCategories()
    {
        if ($cache = CacheService::load($this->cacheKeyCategories)) {
            return $cache;
        }

        try {
            $pdo = DB::getInstance()->pdo();
            $stmt = $pdo->query("SELECT * FROM note_categories1 ORDER BY name ASC");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                CacheService::save($this->cacheKeyCategories, $rows, 3600);
                return $rows;
            }

            return $this->defaultCategories();

        } catch (Throwable $e) {
            app_log("NoteModel@getCategories error: " . $e->getMessage(), "error");
            return $this->defaultCategories();
        }
    }

    public function defaultCategories(): array
    {
        return [
            ["is_default" => true, "name" => "General", "slug" => "general"]
        ];
    }

    /* TAGS */
    public function getTags()
    {
        if ($cache = CacheService::load($this->cacheKeyTags)) {
            return $cache;
        }

        try {
            $pdo = DB::getInstance()->pdo();
            $results = $pdo->query("SELECT * FROM note_tags1 ORDER BY name ASC")
                           ->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($results)) {
                CacheService::save($this->cacheKeyTags, $results, 3600);
                return $results;
            }

            return [];

        } catch (Throwable $e) {
            app_log("NoteModel@getTags error: " . $e->getMessage(), "error");
            return [];
        }
    }

    /* PINNED NOTES */
    public function getPinnedNotes()
    {
        if ($cache = CacheService::load($this->cacheKeyPinned)) {
            return $cache;
        }

        try {
            $pdo = DB::getInstance()->pdo();

            $sql = "
                SELECT n.*, c.slug, c.name AS category_name
                FROM notes1 n
                JOIN note_categories1 c ON n.category_id = c.id
                WHERE n.is_pinned = 1
                ORDER BY n.created_at DESC
                LIMIT 6
            ";

            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                CacheService::save($this->cacheKeyPinned, $rows, 3600);
                return $rows;
            }

            return [];

        } catch (Throwable $e) {
            app_log("NoteModel@getPinnedNotes error: " . $e->getMessage(), "error");
            return [];
        }
    }
}
