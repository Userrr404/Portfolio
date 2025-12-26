<?php
require_once FOOTERSERVICE_FILE; ;

use app\core\DB;
use app\Services\FooterData;

$db = DB::getInstance()->pdo();

$data          = (new FooterData($db))->get();
$footer        = $data['footer'];
$footer_links  = $data['links'];
$social_links  = $data['social'];

$year = date("Y");

// Footer asset files
$footer_css = [FOOTER_CSS];
$footer_js  = [FOOTER_JS];

// Detect project base URL (ex: /Portfolio/public)
$BASE_URL = dirname($_SERVER['SCRIPT_NAME']);
if ($BASE_URL === "/") $BASE_URL = "";

// Detect FULL request URI (ex: /Portfolio/public/about)
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path from URI → gives clean route (/about)
if ($BASE_URL !== "" && strpos($currentUri, $BASE_URL) === 0) {
    $currentUri = substr($currentUri, strlen($BASE_URL));
}
?>

<!-- FOOTER CSS -->
<?php foreach ($footer_css as $css): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
<?php endforeach; ?>

<footer class="text-color font-medium mt-24 border-t border-[#ffffff08] relative">

    <!-- Background Elements -->
    <div class="footer-bg"></div>
    <div class="orb"></div><div class="orb"></div><div class="orb"></div>
    <div class="footer-glow"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16 
                grid gap-10 sm:grid-cols-2 lg:grid-cols-3 place-items-start">

        <!-- BRAND -->
        <div class="space-y-3 animate-[fade-up_0.6s_ease-out]">
            <h2 class="text-white text-2xl sm:text-3xl font-bold tracking-wide brand-hover">
                <?= htmlspecialchars($footer['brand_name']) ?>
            </h2>
            <p class="text-gray-400 text-sm sm:text-base leading-relaxed">
                <?= htmlspecialchars($footer['footer_description']) ?>
            </p>
        </div>

        <!-- QUICK LINKS -->
        <div class="space-y-2 animate-[fade-up_0.8s_ease-out]">
            <h3 class="text-white font-semibold mb-3 text-lg border-b border-accent/40 w-fit pb-1">
                Quick Links
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-1 gap-x-6 sm:gap-x-0 gap-y-1">
                <?php foreach ($footer_links as $link): ?>
                    <?php
                        // Normalize DB URL (ex: "about" → "/about")
                        $linkUrl = "/" . trim($link['url'], "/");

                        // Final clickable URL with base path
                        $finalUrl = $BASE_URL . $linkUrl;
                    ?>
                    <a href="<?= $finalUrl ?>"
                       class="hover:text-accent transition-colors duration-300">
                        <?= htmlspecialchars($link['label']) ?>
                    </a>            
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SOCIAL LINKS -->
        <div class="animate-[fade-up_1s_ease-out]">
            <h3 class="text-white font-semibold mb-3 text-lg border-b border-accent/40 w-fit pb-1">
                Connect
            </h3>

            <div class="flex flex-wrap gap-5 mt-3">
                <?php foreach ($social_links as $s): ?>
                    <a href="<?= htmlspecialchars($s['url']) ?>" 
                       target="_blank"
                       class="text-gray-400 hover:text-accent text-2xl sm:text-xl 
                              social-icon transition-all duration-300 transform hover:scale-110"
                       aria-label="<?= htmlspecialchars($s['platform']) ?>">
                        <i class="fab <?= htmlspecialchars($s['icon_class']) ?>"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <div class="border-t border-[#222] relative z-10"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 
                flex flex-col md:flex-row justify-between items-center text-sm 
                sm:text-base text-gray-400 text-center gap-2">

        <p class="animate-[fade-up_1.2s_ease-out]">
            © <?= $year ?> <?= htmlspecialchars($footer['brand_name']) ?>. All Rights Reserved.
        </p>

        <p class="animate-[fade-up_1.4s_ease-out]">
            Designed & Developed by 
            <span class="name-glow text-accent">
                <?= htmlspecialchars($footer['developer_name']) ?>
            </span>.
        </p>
    </div>

</footer>

<!-- FOOTER JS -->
<?php foreach ($footer_js as $js): ?>
    <script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; ?>
