<?php

class NotesController extends Controller
{
    private NoteModel $notes;

    public function __construct()
    {
        require_once ROOT_PATH . "app/Models/NoteModel.php";
        require_once ROOT_PATH . "app/Services/CacheService.php";

        $this->notes = new NoteModel();
    }

    public function index()
    {
        try {
            // 1. Try reading cache
            $cached = CacheService::load("notes_page");
            if (!empty($cached) && $this->isValidDbData($cached)) {
                return $cached;
            }

            // 2. Fetch DB data (model handles fallback)
            $data = [
                "notes"        => $this->notes->getAllNotes(),
                "categories"   => $this->notes->getCategories(),
                "tags"         => $this->notes->getTags(),
                "pinned_notes" => $this->notes->getPinnedNotes(),
            ];

            // 3. Only store REAL DB data — not defaults
            if ($this->isValidDbData($data)) {
                CacheService::save("notes_page", $data, 3600); // 1 hour
            }

            return $data;

        } catch (Throwable $e) {

            app_log("NotesController@index failed: " . $e->getMessage(), "error");

            return [
                "notes"        => [],
                "categories"   => [],
                "tags"         => [],
                "pinned_notes" => [],
            ];
        }
    }


    // Valid DB data must NOT be empty AND must NOT be default fallback
    private function isValidDbData(array $data): bool
{
    foreach ($data as $section) {
        if (empty($section)) continue;

        // If the section has default flag → skip
        if (isset($section[0]['is_default']) && $section[0]['is_default'] === true) {
            continue;
        }

        // If any section has real DB data → valid
        return true;
    }
    return false;
}

}
