<?php
namespace app\CacheValidators;

class FooterCacheValidator implements CacheValidatorInterface
{
    public function supports(string $key): bool
    {
        return in_array($key, [
            'v1_footer_settings',
            'v1_footer_quick_links',
            'v1_footer_social_links'
        ]);
    }

    public function validate(array $payload): ?string
    {
        /* =====================================================
           FOOTER SETTINGS
        ===================================================== */
        if ($this->isFooterSettings($payload)) {

            $required = [
                'brand_name',
                'footer_description',
                'developer_name',
                'accent_color'
            ];

            foreach ($required as $key) {
                if (!array_key_exists($key, $payload)) {
                    return "DC-04 Footer settings schema missing field '{$key}'";
                }
            }

            if (trim($payload['brand_name']) === '') {
                return "DC-05 Footer settings semantic violation (empty brand_name)";
            }

            return null;
        }

        /* =====================================================
           FOOTER QUICK LINKS
        ===================================================== */
        if ($this->isQuickLinks($payload)) {

            if (empty($payload)) {
                return "DC-05 Footer quick links semantic violation (empty list)";
            }

            foreach ($payload as $index => $item) {
                if (!isset($item['label'], $item['url'])) {
                    return "DC-04 Footer quick links schema corruption at index {$index}";
                }

                if (trim($item['label']) === '' || trim($item['url']) === '') {
                    return "DC-05 Footer quick links semantic violation at index {$index}";
                }
            }

            return null;
        }

        /* =====================================================
           FOOTER SOCIAL LINKS
        ===================================================== */
        if ($this->isSocialLinks($payload)) {

            foreach ($payload as $index => $item) {
                if (!isset($item['platform'], $item['url'], $item['icon_class'])) {
                    return "DC-04 Footer social links schema corruption at index {$index}";
                }

                if (
                    trim($item['platform']) === '' ||
                    trim($item['url']) === '' ||
                    trim($item['icon_class']) === ''
                ) {
                    return "DC-05 Footer social links semantic violation at index {$index}";
                }
            }

            return null;
        }

        /* =====================================================
           UNKNOWN SHAPE (DEFENSIVE)
        ===================================================== */
        return "DC-04 Footer cache payload shape unrecognized";
    }

    /* =====================================================
       TYPE DETECTORS (STRICT)
    ===================================================== */

    private function isFooterSettings(array $payload): bool
    {
        return isset($payload['brand_name']);
    }

    private function isQuickLinks(array $payload): bool
    {
        return isset($payload[0]['label']);
    }

    private function isSocialLinks(array $payload): bool
    {
        return isset($payload[0]['platform']);
    }
}
