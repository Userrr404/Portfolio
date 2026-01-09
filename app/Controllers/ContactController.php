<?php
namespace app\Controllers;

use app\Core\Controller;
use app\Models\ContactModel;
use app\Models\ContactMessageModel;
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
                $cached["safe_mode"] = false;
                return $this->view("pages/contact", $cached);
            }

            // 2) Load each section safely and independently
            $data = [
                "safe_mode" => false,

                "hero"    => $this->wrap($this->contact->getHero()),
                "info"    => $this->wrap($this->contact->getInfo()),
                "socials" => $this->wrap($this->contact->getSocials()),
                "map"     => $this->wrap($this->contact->getMap()),
                "toast"   => $this->wrap($this->contact->getToast()),
            ];

            // 3) Cache full page ONLY when ALL sections came from DB (prevent caching defaults)
            if ($this->hasRealData($data)) {
                CacheService::save($this->cacheKey, $data);
            }

            return $this->view("pages/contact", $data);

        } catch (Throwable $e) {
            app_log("SAFE MODE — ContactController@index: ".$e->getMessage(), "critical");

            // Emergency fallback - return guaranteed non-empty defaults from model
            return $this->view("pages/contact", [
                "safe_mode" => true,
                "hero"      => ["from_db" => false, "data" => $this->contact->defaultHero()],
                "info"      => ["from_db" => false, "data" => []],
                "socials"   => ["from_db" => false, "data" => []],
                "map"       => ["from_db" => false, "data" => []],
                "toast"     => ["from_db" => false, "data" => []],
            ]);
        }
    }


    /* =======================
     * SAME AS HomeController
     * ======================= */

    private function wrap(array $payload): array
    {
        return [
            "from_db" => $payload["source"] === "db",
            "data"    => $payload["data"]
        ];
    }

    /**
     * Returns true only when ALL sections were loaded from real DB (from_db === true).
     * This prevents caching pages that are partially or fully fallback.
     */
    private function hasRealData(array $data): bool
    {
        foreach ($data as $k => $section) {
            if ($k === "safe_mode") continue;
            if (($section["from_db"] ?? false) !== true) {
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
