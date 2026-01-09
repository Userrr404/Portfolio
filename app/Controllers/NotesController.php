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

            /* ---------------------------------------------------
             * 2. PRIMARY ENTITY (NOTES)
             * --------------------------------------------------- */
            $notes = $this->wrap($this->notes->getAllNotes());

            // cache is DB-derived
            if ($notes["source"] === "cache") {
                $notes["source"] = "db";
            }

            /* ---------------------------------------------------
             * 3. 🔒 SOURCE LOCK — RELATIONS FOLLOW NOTES SOURCE
             * --------------------------------------------------- */
            $categories = $this->notes->getCategoriesBySource($notes["source"]);
            $tags       = $this->notes->getTagsBySource($notes["source"]);
            $pinned     = $this->notes->getPinnedBySource($notes["source"]);

            app_log(
                "NotesController source locked: ".
                "notes={$notes["source"]}, ".
                "categories={$categories["source"]}, ".
                "tags={$tags["source"]}, ".
                "pinned={$pinned["source"]}",
                "debug"
            );

            // 2. Fetch DB data (model handles fallback)
            $final = [
                "safe_mode"    => false,
                "notes"        => $notes["data"],
                "categories"   => $categories["data"],
                "tags"         => $tags["data"],
                "pinned_notes" => $pinned["data"],
            ];

            /* ---------------------------------------------------
             * 5. CACHE ONLY WHEN ALL ARE DB
             * --------------------------------------------------- */
            if (
                $notes["source"] === "db" &&
                $categories["source"] === "db" &&
                $tags["source"] === "db" &&
                $pinned["source"] === "db"
            ) {
                CacheService::save($this->cacheKey, $final, 3600);
            }

            return $this->view("pages/notes", $final);

        } catch (Throwable $e) {

            app_log("SAFE MODE ACTIVITED — NotesController@index", "critical" . $e->getMessage());

            return $this->view("pages/notes", [
                "safe_mode"     => true,
                "notes"        => [],
                "categories"   => [],
                "tags"         => [],
                "pinned_notes" => [],
            ]);
        }
    }

    /* ============================================================
     * safeLoad(): EXACT behaviour like AboutController
     * ============================================================ */
    private function wrap(array $payload): array
    {
        return [
            "source" => $payload["source"] ?? "fallback",
            "data"   => $payload["data"] ?? []
        ];
    }

    // Valid DB data must NOT be empty AND must NOT be default fallback
    private function hasRealData(array $data): bool
    {
        foreach ($data as $key => $section) {
            if ($key === "safe_mode") continue;
            if (($section["from_db"] ?? false) !== true) {
                return false;
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
