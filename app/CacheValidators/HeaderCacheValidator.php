<?php
namespace app\CacheValidators;

class HeaderCacheValidator implements CacheValidatorInterface
{
    public function supports(string $key): bool
    {
        return in_array($key, [
            'v1_header_settings',
            'v1_header_navigation'
        ]);
    }

    public function validate(array $payload): ?string
    {
        /* ================= HEADER SETTINGS ================= */
        if (isset($payload['site_title'])) {

            if (!isset(
                $payload['logo_path'],
                $payload['button_text'],
                $payload['button_link'],
                $payload['is_active']
            )) {
                return "DC-04 Header settings schema missing required fields";
            }

            if ((int)$payload['is_active'] !== 1) {
                return "DC-05 Header settings semantic violation (inactive config cached)";
            }

            return null; // valid
        }

        /* ================= HEADER NAVIGATION ================= */
        foreach ($payload as $index => $item) {
            if (!isset($item['label'], $item['url'])) {
                return "DC-04 Header navigation schema corruption at index {$index}";
            }
        }

        return null; // valid
    }
}