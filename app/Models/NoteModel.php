<?php
namespace app\Models;

use PDO;
use app\Services\CacheService;
use app\Core\DB;
use Throwable;

class NoteModel
{

    private string $cacheKeyNotes      = "notes_list";
    private string $cacheKeyCategories = "note_categories";
    private string $cacheKeyTags       = "note_tags";
    private string $cacheKeyPinned     = "note_pinned";

    private ?string $notesDefaultPath      = null;
    private ?string $categoriesDefaultPath = null;
    private ?string $tagsDefaultPath        = null;
    private ?string $pinnedDefaultPath      = null;

    public function __construct()
    {
        require_once CACHESERVICE_FILE;

        // CONFIG-SAFE path resolution
        $this->notesDefaultPath      = safe_path('NOTES_DEFAULT_FILE');
        $this->categoriesDefaultPath = safe_path('NOTES_CATEGORIES_DEFAULT_FILE');
        $this->tagsDefaultPath       = safe_path('NOTES_TAGS_DEFAULT_FILE');
        $this->pinnedDefaultPath     = safe_path('NOTES_PINNED_DEFAULT_FILE');
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
                SELECT 
                    n.*,
                    c.name AS category_name,
                    c.slug AS category_slug
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
        if ($this->notesDefaultPath && file_exists($this->notesDefaultPath)) {
            $json = json_decode(file_get_contents($this->notesDefaultPath), true);
            if (!empty($json)) return $json;
        }

        // D. Hard fallback
        return $this->defaultNotes();
    }


    private function defaultNotes(): array
    {
        return [
            [
                "is_default"     => true,
                "id"             => 0,
                "title"          => "Welcome Note",
                "slug"           => "welcome-note",
                "description"    => "Your notes page is ready! Add notes from the admin panel.",
                "category_name"  => "General",
                "category_slug"  => "general",
                "created_at"     => date('Y-m-d'),
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
        if ($this->categoriesDefaultPath && file_exists($this->categoriesDefaultPath)) {
            $json = json_decode(file_get_contents($this->categoriesDefaultPath), true);
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
        if ($this->tagsDefaultPath && file_exists($this->tagsDefaultPath)) {
            $json = json_decode(file_get_contents($this->tagsDefaultPath), true);
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

            $stmt = $pdo->prepare("
                SELECT 
                    n.*,
                    c.name AS category_name,
                    c.slug AS category_slug
                FROM notes n
                JOIN note_categories c ON n.category_id = c.id
                WHERE n.is_pinned = 1 AND n.is_active = 1
                ORDER BY n.created_at DESC
                LIMIT 6
            ");
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                CacheService::save($this->cacheKeyPinned, $rows, 3600);
                return $rows;
            }

        } catch (Throwable $e) {
            app_log('NoteModel@getPinnedNotes error: ' . $e->getMessage(), 'error');
        }

        // C. Try default JSON
        if ($this->pinnedDefaultPath && file_exists($this->pinnedDefaultPath)) {
            $json = json_decode(file_get_contents($this->pinnedDefaultPath), true);
            if (!empty($json)) return $json;
        }

        // D. fallback
        return $this->defaultPinnedNotes();
    }

    private function defaultPinnedNotes(): array
    {
        return [
            [
                "is_default"      => true,
                "id"              => 0,
                "title"           => "Welcome to Notes",
                "slug"            => "welcome-note",
                "description"     => "This is your notes system. Once you add pinned notes from the admin panel, they will appear here.",
                "category_name"   => "General",
                "category_slug"   => "general",
                "created_at"      => date('Y-m-d'),
            ]
        ];
    }


    public function fallback(string $type): array
    {
        return match ($type) {
            'notes'        => $this->defaultNotes(),
            'categories'   => $this->defaultCategories(),
            'tags'         => $this->defaultTags(),
            'pinned_notes' => $this->defaultPinnedNotes(),
            default        => [],
        };
    }


    /* ========================= NOTE DETAIL ========================= */

    public function getNoteBySlug(string $slug): ?array
    {
        try {
            $pdo = DB::getInstance()->pdo();

            $stmt = $pdo->prepare("
                SELECT 
                    n.*,
                    c.name AS category_name,
                    c.slug AS category_slug
                FROM notes n
                JOIN note_categories c ON n.category_id = c.id
                WHERE n.slug = :slug AND n.is_active = 1
                LIMIT 1
            ");
            $stmt->execute(['slug' => $slug]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            app_log('getNoteBySlug error: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    public function getTagsByNoteId(int $noteId): array
    {
        try {
            $pdo = DB::getInstance()->pdo();

            $stmt = $pdo->prepare("
                SELECT t.name
                FROM note_tags t
                JOIN note_tag_map ntm ON ntm.tag_id = t.id
                WHERE ntm.note_id = ?
            ");
            $stmt->execute([$noteId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            app_log('getTagsByNoteId error: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    public function getRelatedNotes(int $categoryId, int $excludeId): array
    {
        try {
            $pdo = DB::getInstance()->pdo();

            $stmt = $pdo->prepare("
                SELECT id, title, slug
                FROM notes
                WHERE category_id = ? AND id != ?
                ORDER BY created_at DESC
                LIMIT 4
            ");
            $stmt->execute([$categoryId, $excludeId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            app_log('getRelatedNotes error', 'error');
            return [];
        }
    }
}
