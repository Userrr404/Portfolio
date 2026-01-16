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
    public function getAllNotes(): array
    {
        // A. Try cache
        if ($cache = CacheService::load($this->cacheKeyNotes)) {
            return [
                "source" => "cache",
                "data"   => $cache
            ];
        }

        // B. Try DB
        try {
            $pdo = DB::getInstance()->pdo();

            if (!$pdo) {
                app_log("DC-03: NoteModel@getAllNotes DB unavailable", "error");
                throw new \RuntimeException("DB unavailable");
            }

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

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ? : [];

            if (!empty($rows)) {
                CacheService::save($this->cacheKeyNotes, $rows, 3600);
                return [
                    "source" => "db",
                    "data"   => $rows
                ];
            }

        } catch (Throwable $e) {
            app_log("NoteModel@getAllNotes error: " . $e->getMessage(), "error");
            //  treat as empty → JSON allowed
        }

        // C. Try default JSON
        if ($this->notesDefaultPath && file_exists($this->notesDefaultPath)) {
            $json = json_decode(file_get_contents($this->notesDefaultPath), true);
            if (!empty($json)) {
                return [
                    "source" => "json",
                    "data"   => $json
                ];
            }
        }

        // D. Hard fallback
        app_log("NoteModel getAllNotes FALLBACK HIT", "debug");
        return [
            "source" => "fallback",
            "data"   => $this->defaultNotes()
        ];
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

    /* ============================================================
     * 🔒 SOURCE-LOCKED RELATIONS
     * ============================================================ */

    public function getCategoriesBySource(string $source): array
    {
        return $this->bySource(
            $source,
            $this->cacheKeyCategories,
            "SELECT * FROM note_categories ORDER BY name ASC",
            $this->categoriesDefaultPath,
            $this->defaultCategories(),
            "Categories"
        );
    }

    public function getTagsBySource(string $source): array
    {
        return $this->bySource(
            $source,
            $this->cacheKeyTags,
            "SELECT * FROM note_tags ORDER BY name ASC",
            $this->tagsDefaultPath,
            $this->defaultTags(),
            "Tags"
        );
    }

    public function getPinnedBySource(string $source): array
    {
        return $this->bySource(
            $source,
            $this->cacheKeyPinned,
            "SELECT * FROM notes WHERE is_pinned = 1 AND is_active = 1 LIMIT 6",
            $this->pinnedDefaultPath,
            $this->defaultPinnedNotes(),
            "Pinned"
        );
    }

    private function bySource(
        string $source,
        string $cacheKey,
        string $sql,
        ?string $jsonPath,
        array $fallback,
        string $label
    ): array {
        if ($source === "db") {
            if ($cache = CacheService::load($cacheKey)) {
                return [
                    "source" => "db",
                    "data"   => $cache
                ];
            }

            try {
                $pdo = DB::getInstance()->pdo();

                if (!$pdo) {
                    app_log("DC-03: NoteModel {$label} DB unavailable", "error");
                    throw new \RuntimeException("DB unavailable");
                }

                $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ? : [];

                if (!empty($rows)) {
                    CacheService::save($cacheKey, $rows, 3600);
                    return [
                        "source" => "db",
                        "data"   => $rows
                    ];
                }
            } catch (Throwable $e) {
                app_log("NoteModel $label DB ERROR: " . $e->getMessage(), "error");
            }
        }

        if ($source === "json" && $jsonPath && file_exists($jsonPath)) {
            $json = json_decode(file_get_contents($jsonPath), true);

            if (!empty($json)) {
                return [
                    "source" => "json",
                    "data"   => $json
                ];
            }
        }

        return [
            "source" => "fallback",
            "data"   => $fallback
        ];
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

    private function defaultTags(): array
    {
        return [
            ["is_default" => true, "name" => "general"]
        ];
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

            if (!$pdo) {
                app_log("DC-03: getNoteBySlug blocked (DB down)", "error");
                return null; // HARD FAIL
            }

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

            if (!$pdo) {
                app_log("DC-03: getTagsByNoteId blocked (DB down)", "error");
                return null; // HARD FAIL
            }

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

            if (!$pdo) {
                app_log("DC-03: getRelatedNotes blocked (DB down)", "error");
                return null; // HARD FAIL
            }

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
