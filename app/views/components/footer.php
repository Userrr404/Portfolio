<?php
/**
 * FOOTER COMPONENT
 * Safe-mode aware, zero-crash
 */

$safeMode = $data['safe_mode'] ?? false;

$footer        = [];
$footer_links  = [];
$social_links  = [];

$year = date("Y");

if (!$safeMode) {
    try {
        require_once FOOTERSERVICE_FILE;

        /*
        use app\core\DB;
        use app\Services\FooterData;

        $db = DB::getInstance()->pdo();

        $data = (new FooterData($db))->get();
        */
        // ✅ Use fully qualified class names (NO use statements in views)
        $db = \app\Core\DB::getInstance()->pdo();
        $footerData = (new \app\Services\FooterData($db))->get();

        $footer       = is_array($footerData['footer'] ?? null) ? $footerData['footer'] : [];
        $footer_links = is_array($footerData['links']  ?? null) ? $footerData['links']  : [];
        $social_links = is_array($footerData['social'] ?? null) ? $footerData['social'] : [];

    } catch (Throwable $e) {
        app_log("Footer fallback triggered: " . $e->getMessage(), "error");
        $safeMode = true;
    }
}

// Footer asset files
$footer_css = [FOOTER_CSS];
$footer_js  = [FOOTER_JS];

if ($safeMode):
?>
<footer class="mt-24 py-12 text-center text-gray-400 border-t border-[#ffffff08]">
    <p>© <?= $year ?> Yogesh Lilake. All Rights Reserved.</p>
    <p class="mt-2 text-sm">Some features are temporarily unavailable.</p>
</footer>
<?php return; endif; ?>

<!-- FOOTER CSS -->
<?php foreach ($footer_css as $css): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
<?php endforeach; ?>

<footer class="text-color font-medium mt-24 border-t border-[#ffffff08] relative">

    <!-- Background Elements (THEME PRESERVED) -->
    <div class="footer-bg"></div>
    <div class="orb"></div><div class="orb"></div><div class="orb"></div>
    <div class="footer-glow"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 
                grid gap-10 sm:grid-cols-2 lg:grid-cols-3 place-items-start">

        <!-- BRAND -->
        <div class="space-y-3 animate-[fade-up_0.6s_ease-out]">
            <h2 class="text-white text-2xl sm:text-3xl font-bold tracking-wide brand-hover">
                <?= htmlspecialchars($footer['brand_name'] ?? SITE_TITLE) ?>
            </h2>
            <p class="text-gray-400 text-sm sm:text-base leading-relaxed">
                <?= htmlspecialchars($footer['footer_description'] ?? '') ?>
            </p>
        </div>

        <!-- QUICK LINKS -->
        <div class="space-y-2 animate-[fade-up_0.8s_ease-out]">
            <h3 class="text-white font-semibold mb-3 text-lg border-b border-accent/40 w-fit pb-1">
                Quick Links
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-1 gap-x-6 sm:gap-x-0 gap-y-1">
                <?php if (is_array($footer_links)): ?>
                    <?php foreach ($footer_links as $link): ?>
                        <a href="<?= url($link['url'] ?? '') ?>"
                           class="hover:text-accent transition-colors duration-300">
                            <?= htmlspecialchars($link['label'] ?? '') ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- SOCIAL LINKS -->
        <div class="animate-[fade-up_1s_ease-out]">
            <h3 class="text-white font-semibold mb-3 text-lg border-b border-accent/40 w-fit pb-1">
                Connect
            </h3>

            <div class="flex flex-wrap gap-5 mt-3">
                <?php if (is_array($social_links)): ?>
                    <?php foreach ($social_links as $s): ?>
                        <a href="<?= htmlspecialchars($s['url'] ?? '#') ?>"
                           target="_blank"
                           class="text-gray-400 hover:text-accent text-2xl sm:text-xl 
                                  social-icon transition-all duration-300 transform hover:scale-110"
                           aria-label="<?= htmlspecialchars($s['platform'] ?? 'social') ?>">
                            <i class="fab <?= htmlspecialchars($s['icon_class'] ?? '') ?>"></i>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="border-t border-[#222] relative z-10"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 
                flex flex-col md:flex-row justify-between items-center text-sm 
                sm:text-base text-gray-400 text-center gap-2">

        <p class="animate-[fade-up_1.2s_ease-out]">
            © <?= $year ?> <?= htmlspecialchars($footer['brand_name'] ?? SITE_TITLE) ?>. All Rights Reserved.
        </p>

        <p class="animate-[fade-up_1.4s_ease-out]">
            Designed & Developed by 
            <span class="name-glow text-accent">
                <?= htmlspecialchars($footer['developer_name'] ?? 'Developer') ?>
            </span>.
        </p>
    </div>

</footer>

<!-- FOOTER JS -->
<?php foreach ($footer_js as $js): ?>
    <script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; ?>
