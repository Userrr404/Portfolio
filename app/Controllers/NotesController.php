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
        require_once NOTE_MODEL_FILE;
        require_once CACHESERVICE_FILE;

        $this->notes = new NoteModel();
    }

    public function index()
    {
        try {
            // 1. Try reading cache
            if ($cached = CacheService::load($this->cacheKey)) {
                return $this->view("pages/notes", $cached);
            }

            // 2. Fetch DB data (model handles fallback)
            $data = [
                "notes"        => $this->safeLoad(fn() => $this->notes->getAllNotes()),
                "categories"   => $this->safeLoad(fn() => $this->notes->getCategories()),
                "tags"         => $this->safeLoad(fn() => $this->notes->getTags()),
                "pinned_notes" => $this->safeLoad(fn() => $this->notes->getPinnedNotes()),
            ];

            // 3. Only store REAL DB data — not defaults
            if ($this->hasRealData($data)) {
                CacheService::save("notes_page", $data, 3600); // 1 hour
            }

            return $this->view("pages/notes", $data);

        } catch (Throwable $e) {

            app_log("NotesController@index failed: " . $e->getMessage(), "error");

            return $this->view("pages/notes", [
                "notes"        => ["data" => []],
                "categories"   => ["data" => []],
                "tags"         => ["data" => []],
                "pinned_notes" => ["data" => []],
            ]);
        }
    }

    /* ============================================================
     * safeLoad(): EXACT behaviour like AboutController
     * ============================================================ */
    private function safeLoad(callable $fn): array
    {
        try {
            $data = $fn();

            // Null, string, bool = invalid
            if (!is_array($data)) {
                return ["from_db" => false, "data" => []];
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
            return ["from_db" => false, "data" => []];

        } catch (Throwable $e) {
            app_log("NotesController safeLoad failed: " . $e->getMessage(), "warning");
            return ["from_db" => false, "data" => []];
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
        return false;
    }

}
