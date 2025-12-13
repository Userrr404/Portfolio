<?php
namespace app\Controllers;

use app\Core\Controller;
use app\Models\ContactModel;
use app\Services\CacheService;
use Throwable;

class ContactController extends Controller
{
    /** @var ContactModel Handles DB/JSON/fallback for contact page */
    private ContactModel $contact;

    /** Cache key for the full contact page (only stored when ALL sections are from DB) */
    private string $cacheKey = "contact_page";

    public function __construct()
    {

        $this->contact = new ContactModel();
    }

    /**
     * Loads the full Contact page using the same 4-step architecture per-section:
     *  A. Try full page cache
     *  B. safeLoad() each section (DB → JSON → fallback)
     *  C. Save full-page cache ONLY if ALL sections were from DB
     *  D. Emergency fallback (controller-level)
     *
     * Each section returned in the shape:
     *   [ "from_db" => bool, "data" => [...] ]
     */
    public function index()
    {
        try {
            // 1) Try full page cache first
            if ($cached = CacheService::load($this->cacheKey)) {
                return $this->view("pages/contact", $cached);
            }

            // 2) Load each section safely and independently
            $sections = [
                "hero"    => $this->safeLoad(fn() => $this->contact->getHero(),    "hero"),
                "info"    => $this->safeLoad(fn() => $this->contact->getInfo(),    "info"),
                "socials" => $this->safeLoad(fn() => $this->contact->getSocials(), "socials"),
                "map"     => $this->safeLoad(fn() => $this->contact->getMap(),     "map"),
                "toast"   => $this->safeLoad(fn() => $this->contact->getToast(),   "toast"),
            ];

            // 3) Cache full page ONLY when ALL sections came from DB (prevent caching defaults)
            if ($this->hasRealData($sections)) {
                CacheService::save($this->cacheKey, $sections);
            }

            return $this->view("pages/contact", $sections);

        } catch (Throwable $e) {
            app_log("ContactController@index failed: " . $e->getMessage(), "error");

            // Emergency fallback - return guaranteed non-empty defaults from model
            return $this->view("pages/contact", [
                "hero"    => ["from_db" => false, "data" => $this->contact->fallback("hero")],
                "info"    => ["from_db" => false, "data" => $this->contact->fallback("info")],
                "socials" => ["from_db" => false, "data" => $this->contact->fallback("socials")],
                "map"     => ["from_db" => false, "data" => $this->contact->fallback("map")],
                "toast"   => ["from_db" => false, "data" => $this->contact->fallback("toast")],
            ]);
        }
    }

    /**
     * Safely loads a section using the provided callable (model method).
     *
     * Guarantees:
     *  - If model throws or returns invalid data -> returns fallback
     *  - Distinguishes DB-sourced data vs defaults (prevents caching fallback)
     *  - Normalizes JSON-default wrapped responses
     *
     * Model return shapes handled:
     *  - DB result array -> return ["from_db" => true, "data" => $data]
     *  - JSON-default wrapper -> ["is_default_json"=>true,"data"=>...]
     *  - Hard-coded fallback -> contains ["is_default"=>true]
     */
    private function safeLoad(callable $fn, string $label): array
    {
        try {
            $result = $fn();

            // Unexpected non-array -> fallback
            if (!is_array($result)) {
                return ["from_db" => false, "data" => $this->contact->fallback($label)];
            }

            // JSON defaults wrapper from model: return as fallback (not DB)
            if (isset($result["is_default_json"]) && isset($result["data"])) {
                return ["from_db" => false, "data" => $result["data"]];
            }

            // Hard-coded model fallback (explicit marker)
            if (isset($result["is_default"]) && $result["is_default"] === true) {
                return ["from_db" => false, "data" => $result];
            }

            // If non-empty array with DB-like structure -> treat as DB
            if (!empty($result)) {
                return ["from_db" => true, "data" => $result];
            }

            // Empty -> fallback
            return ["from_db" => false, "data" => $this->contact->fallback($label)];

        } catch (Throwable $e) {
            app_log("ContactController: Failed loading section {$label}: " . $e->getMessage(), "warning");
            return ["from_db" => false, "data" => $this->contact->fallback($label)];
        }
    }

    /**
     * Returns true only when ALL sections were loaded from real DB (from_db === true).
     * This prevents caching pages that are partially or fully fallback.
     */
    private function hasRealData(array $sections): bool
    {
        foreach ($sections as $s) {
            if (!isset($s["from_db"]) || $s["from_db"] !== true) {
                return false;
            }
        }
        return true;
    }
}
