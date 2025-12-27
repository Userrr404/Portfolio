<?php
namespace app\Controllers;

use app\Core\Controller;
use app\Models\NoteModel;
use app\Services\CacheService;
use Throwable;

class NotesController extends Controller
{
    private NoteModel $notes;
    private string $cacheKey = "notes_page";

    public function __construct()
    {

        $this->notes = new NoteModel();
    }

    public function index()
    {
        try {
            // 1. Try reading cache
            if ($cached = CacheService::load($this->cacheKey)) {
                $cached['safe_mode'] = false;
                return $this->view("pages/notes", $cached);
            }

            // 2. Fetch DB data (model handles fallback)
            $data = [
                "safe_mode"    => false,
                "notes"        => $this->safeLoad(fn() => $this->notes->getAllNotes(), "notes"),
                "categories"   => $this->safeLoad(fn() => $this->notes->getCategories(), "categories"),
                "tags"         => $this->safeLoad(fn() => $this->notes->getTags(), "tags"),
                "pinned_notes" => $this->safeLoad(fn() => $this->notes->getPinnedNotes(), "pinned_notes"),
            ];

            // 3. Only store REAL DB data — not defaults
            if ($this->hasRealData($data)) {
                CacheService::save("notes_page", $data, 3600); // 1 hour
            }

            return $this->view("pages/notes", $data);

        } catch (Throwable $e) {

            app_log("SAFE MODE ACTIVITED — NotesController@index", "critical" . $e->getMessage());

            return $this->view("pages/notes", [
                "safe_mode"     => true,
                "notes"         => ["from_db" => false, "data" => []],
                "categories"    => ["from_db" => false, "data" => []],
                "tags"          => ["from_db" => false, "data" => []],
                "pinned_notes"  => ["from_db" => false, "data" => []],
            ]);
        }
    }

    /* ============================================================
     * safeLoad(): EXACT behaviour like AboutController
     * ============================================================ */
    private function safeLoad(callable $fn, string $label): array
    {
        try {
            $data = $fn();

            // Null, string, bool = invalid
            if (!is_array($data)) {
                return ["from_db" => false, "data" => $this->notes->fallback($label)];
            }

            // DB real rows contain ID
            if (!empty($data) && isset($data[0]['id'])) {
                return ["from_db" => true, "data" => $data];
            }

            // Default hard-coded fallback contains is_default
            if (!empty($data) && isset($data[0]['is_default']) && $data[0]['is_default'] === true) {
                return ["from_db" => false, "data" => $data];
            }

            // JSON defaults → NO id → treat as fallback
            if (!empty($data) && !isset($data[0]['id'])) {
                return ["from_db" => false, "data" => $data];
            }

            // Empty array → fallback mode
            return ["from_db" => false, "data" => $this->notes->fallback($label)];

        } catch (Throwable $e) {
            app_log("NotesController safeLoad failed: {$label}" . $e->getMessage(), "warning");
            return ["from_db" => false, "data" => $this->notes->fallback($label)];
        }
    }

    // Valid DB data must NOT be empty AND must NOT be default fallback
    private function hasRealData(array $sections): bool
    {
        foreach ($sections as $section) {
            if (!isset($section["from_db"]) || $section["from_db"] !== true) {
                return false; // If any section NOT DB → do not cache
            }
        }
        return true;
    }

    public function show(string $slug)
    {
        try {
            $cacheKey = "note_detail_" . md5($slug);

            if ($cached = CacheService::load($cacheKey)) {
                return $this->view("pages/note-detail", $cached);
            }

            $note = $this->notes->getNoteBySlug($slug);

            if (!$note) {
                http_response_code(404);
                echo "<h1>404 - Notes not found</h1>";
                exit;
            }

            $data = [
                "note"       => $note,
                "tags"       => $this->notes->getTagsByNoteId($note['id']),
                "related"    => $this->notes->getRelatedNotes($note['category_id'], $note['id']),
            ];

            CacheService::save($cacheKey, $data, 3600);

            return $this->view("pages/note-detail", $data);

        } catch (Throwable $e) {
            app_log("NotesController@show failed: " . $e->getMessage(), "error");
            http_response_code(500);
            echo "<h1>500 - Internal Server Error</h1><p>Sorry, something went wrong.</p>";
            exit;
        }
    }
}
