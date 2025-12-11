<?php
require_once HEADERSERVICE_FILE;

use app\core\DB;
use app\Services\HeaderData;

$db = DB::getInstance()->pdo();

// Pass DB to HeaderData
$data = (new HeaderData($db))->get();
$header     = $data['header'];
$nav_links  = $data['nav'];

// Header asset paths
$header_css = [HEADER_CSS];
$header_js  = [HEADER_JS];

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- HEADER CSS -->
<?php foreach ($header_css as $css): ?>
  <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
<?php endforeach; ?>

<script src="<?= TAILWIND_CONFIG_JS ?>"></script>

<header id="siteHeader" class="text-color font-medium select-none">

  <!-- TOP HEADER BAR -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">

    <!-- LOGO -->
    <a href="<?= HOME_URL ?>" class="flex items-center space-x-2 group">
      <img src="<?= htmlspecialchars($header['logo_path']) ?>"
            class="h-9 w-9 rounded-full transition-transform group-hover:rotate-12 duration-300 shadow-md shadow-[<?= htmlspecialchars($header['accent_color']) ?>55]">
      <span class="logo-text gradient-text tracking-wide duration-300">
        <?= htmlspecialchars($header['site_title']) ?>
      </span>
    </a>

    <!-- DESKTOP NAV -->
    <nav class="hidden md:flex space-x-6 text-clamp">
      <?php foreach ($nav_links as $link): ?>
        <a href="<?= htmlspecialchars($link['url']) ?>"
            class="<?= ($current_page === basename($link['url'])) ? 'text-accent' : 'hover:text-accent' ?>">
          <?= htmlspecialchars($link['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <!-- CTA BUTTON -->
    <a href="<?= htmlspecialchars($header['button_link']) ?>"
        class="hidden sm:inline-block bg-gradient-to-r from-[#d32f2f] via-[#ff5a5a] to-[#ff8c5a] text-darkbg font-bold px-5 py-2 rounded-md btn-glow">
      <?= htmlspecialchars($header['button_text']) ?>
    </a>

    <!-- MOBILE MENU BUTTON -->
    <button id="menuBtn"
            class="md:hidden flex items-center p-2 rounded hover:text-accent transition-transform duration-300 hover:rotate-90">
      <i class="fa-solid fa-bars text-xl"></i>
    </button>

    </div>

    <!-- MOBILE DROPDOWN -->
    <div id="mobileMenu" class="md:hidden hidden bg-[#111] border-t border-[#333] text-white">
      <nav class="flex flex-col p-4 space-y-3 font-medium text-base">
        <?php foreach ($nav_links as $link): ?>
          <a href="<?= htmlspecialchars($link['url']) ?>" class="hover:text-accent">
            <?= htmlspecialchars($link['label']) ?>
          </a>
          <?php endforeach; ?>
      </nav>
    </div>

</header>

<!-- HEADER JS -->
<?php foreach ($header_js as $js): ?>
  <script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; ?>
