<?php
namespace app\Controllers;

use app\Core\Controller;
use app\Models\ContactModel;
use app\Models\ContactMessageModel;   // ✅ REQUIRED
use app\Services\CacheService;
use app\Services\MailService;
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

    /* ===========================
       PAGE RENDER
    =========================== */
    public function index()
    {
        try {
            // 1) Try full page cache first
            if ($cached = CacheService::load($this->cacheKey)) {
                return $this->view("pages/contact", $cached);
            }

            // 2) Load each section safely and independently
            $sections = [
                "hero"    => $this->safeLoad(fn() => $this->contact->getHero(), "hero"),
                "info"    => $this->safeLoad(fn() => $this->contact->getInfo(), "info"),
                "socials" => $this->safeLoad(fn() => $this->contact->getSocials(), "socials"),
                "map"     => $this->safeLoad(fn() => $this->contact->getMap(), "map"),
                "toast"   => $this->safeLoad(fn() => $this->contact->getToast(), "toast"),
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

    /* ===========================
       API: SEND MESSAGE
    =========================== */
    public function sendMessage()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $data = [
                'name'    => trim($_POST['name'] ?? ''),
                'email'   => trim($_POST['email'] ?? ''),
                'message' => trim($_POST['message'] ?? ''),
                'hp'      => trim($_POST['hp_name'] ?? ''),
                'token'   => trim($_POST['recaptcha_token'] ?? ''),
                'ip'      => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ];

            // Honeypot
            if ($data['hp'] !== '') {
                app_log("Contact spam blocked", "warning", ["ip" => $data['ip']]);
                return $this->jsonError("Spam detected");
            }

            // Validation
            if ($data['name'] === '' || $data['email'] === '' || $data['message'] === '') {
                return $this->jsonError("Please fill all fields ❗");
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->jsonError("Invalid email address ❗");
            }

            $model = new ContactMessageModel();

            // Rate limit
            $model->checkRateLimit($data['ip']);

            // reCAPTCHA
            $model->verifyRecaptcha($data['token'], $data['ip']);

            // Store message
            $messageId = $model->storeMessage($data);

            // Send email
            MailService::sendContactMail($data);

            // Mark success
            $model->markEmailSuccess($messageId);

            app_log("Contact message sent", "info", [
                "email" => $data['email'],
                "ip"    => $data['ip']
            ]);

            return $this->jsonSuccess("Message sent successfully 🎉");

        } catch (Throwable $e) {

            app_log("Contact send failed", "error", [
                "exception" => $e->getMessage(),
                "ip"        => $_SERVER['REMOTE_ADDR'] ?? ''
            ]);

            // Known, user-safe cases
            if (str_contains($e->getMessage(), 'Too fast')) {
                return $this->jsonError("You're sending messages too quickly. Please wait a bit.");
            }

            if (str_contains($e->getMessage(), 'reCAPTCHA')) {
                return $this->jsonError("Verification failed. Please try again.");
            }

            // Unknown / system error
            return $this->jsonError(
                "We couldn’t send your message right now. Please try again later."
            );
        }
    }

    private function jsonSuccess(string $msg)
    {
        echo json_encode(["status" => "success", "message" => $msg]);
        exit;
    }

    private function jsonError(string $msg)
    {
        echo json_encode(["status" => "error", "message" => $msg]);
        exit;
    }
}
