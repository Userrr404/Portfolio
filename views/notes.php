<?php
require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . 'logger.php';
require_once CORE_PATH . 'Controller.php';
require_once CORE_PATH . 'App.php';

// Controller returns all required data with DB → Cache → Defaults
$data = App::run("NotesController@index");

$notes        = $data["notes"]["data"] ?? [];
$categories   = $data["categories"]["data"] ?? [];
$tags         = $data["tags"]["data"] ?? [];
$pinned_notes = $data["pinned_notes"]["data"] ?? [];

$page_title = "Notes | Yogesh Lilake";
$custom_css = [NOTES_CSS];
$custom_js  = [NOTES_JS];

$extra_js = [
    "https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js",
    "https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js",
];

/* ===================================================
   Helper Functions (NULL SAFE + HTML SAFE)
   =================================================== */

function safe($value, string $default = '')
{
    if (!isset($value) || $value === '' || $value === false || $value === null) {
        return $default;
    }

    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function safeStr($value, int $length = 120, string $default = ''): string
{
    if (!isset($value) || $value === '' || $value === false || $value === null) {
        return $default;
    }

    $value = (string)$value; // Convert to safe string
    return htmlspecialchars(substr($value, 0, $length), ENT_QUOTES, 'UTF-8');
}

require_once LAYOUT_HEAD_FILE;
?>


<!-- HERO -->
<section class="relative min-h-[60vh] flex flex-col justify-center items-center text-center overflow-hidden px-4 sm:px-6">
  <lottie-player
    src="https://assets1.lottiefiles.com/packages/lf20_yr6zz3wv.json"
    background="transparent" speed="1"
    class="w-[60vw] sm:w-[40vw] lg:w-[25vw] max-w-[420px] aspect-square"
    loop autoplay>
  </lottie-player>

  <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mt-6 text-white fade-in-up px-2">
      Tech Notes & Insights
  </h1>
  <p class="text-gray-400 mt-3 max-w-2xl mx-auto fade-in-up delay-200 text-sm sm:text-base leading-relaxed">
      A living library of engineering notes, debugging logs, architecture ideas & insights.
  </p>
</section>

<!-- PINNED NOTES -->
<section class="px-4 sm:px-6 md:px-16 mt-12 fade-in-up delay-300">
  <h2 class="text-2xl md:text-3xl font-bold mb-4 text-white">📌 Pinned Notes</h2>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
    <?php if (!empty($pinned_notes)): ?>
        <?php foreach ($pinned_notes as $note): ?>
            <div class="bg-card p-6 rounded-2xl border border-gray-700 hover:shadow-xl transition-all duration-500">
                <h3 class="text-lg md:text-xl font-semibold text-white mb-2">
                    <?= safe($note['title']) ?>
                </h3>

                <p class="text-gray-400 text-sm mb-3">
                    <?= safe(substr((string)($note['description'] ?? ''), 0, 100)) ?>...
                </p>

                <a href="<?= safe($note['link'] ?? '') ?>" class="text-accent hover:underline text-sm font-medium">
                    Read More →
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
      <p class="text-gray-400">No pinned notes yet.</p>
    <?php endif; ?>
  </div>
</section>

<!-- SEARCH + FILTER -->
<section class="px-4 sm:px-6 md:px-16 mt-12 fade-in-up delay-400">
  <div class="flex flex-col md:flex-row justify-between items-center gap-4">
    <input id="searchInput"
        type="text"
        placeholder="🔍 Search notes..."
        class="w-full md:w-1/2 bg-card border border-gray-700 px-4 py-3 rounded-lg 
        focus:ring-2 focus:ring-accent outline-none transition-all duration-300">
    
    <select id="filterSelect"
        class="bg-card border border-gray-700 px-4 py-3 rounded-lg focus:ring-2 focus:ring-accent">
      <option value="all">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= safe($cat['slug']) ?>">
            <?= safe($cat['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
</section>

<!-- MAIN LAYOUT -->
<section class="flex flex-col lg:flex-row gap-10 px-4 sm:px-6 md:px-16 py-12 fade-in-up delay-500">

  <!-- SIDEBAR -->
  <aside class="hidden lg:block w-1/4 bg-card p-6 rounded-2xl border border-gray-700 sticky top-24 h-fit self-start">
    <h2 class="text-lg font-semibold mb-4 text-white">Categories</h2>
    <ul class="space-y-2">
      <li><button class="filter-btn text-gray-300 hover:text-accent transition" data-cat="all">All</button></li>

      <?php foreach ($categories as $cat): ?>
        <li>
          <button class="filter-btn text-gray-300 hover:text-accent transition"
              data-cat="<?= safe($cat['slug']) ?>">
              <?= safe($cat['name']) ?>
          </button>
        </li>
      <?php endforeach; ?>
    </ul>

    <div class="mt-8">
      <h3 class="text-lg font-semibold mb-3 text-white">Popular Tags</h3>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($tags as $tag): ?>
          <span class="bg-gray-700 text-sm px-3 py-1 rounded-full text-gray-200">
              #<?= safe($tag['name']) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>

  <!-- NOTES GRID -->
  <div class="flex-grow grid sm:grid-cols-2 xl:grid-cols-3 gap-6 md:gap-8" id="notesGrid">
    <?php if (!empty($notes)): ?>
        <?php foreach ($notes as $note): ?>
            <div class="note-card bg-card p-6 rounded-2xl border border-gray-700 hover:shadow-xl transition-all duration-700"
                 data-cat="<?= safe($note['slug']) ?>">

                <h3 class="text-xl font-semibold mb-2 text-white">
                    <?= safe($note['title']) ?>
                </h3>

                <p class="text-gray-400 text-sm mb-4">
                    <?= safe(substr((string)($note['description'] ?? ''), 0, 100)) ?>...
                </p>

                <div class="flex justify-between items-center">
                  <a href="<?= safe($note['link']) ?>" class="text-accent hover:underline text-sm font-medium">
                      Read More →
                  </a>

                  <button onclick="toggleSave(this)"
                      class="text-sm text-gray-400 hover:text-accent flex items-center gap-1 transition">
                      <i class="fa-regular fa-bookmark"></i> Save
                  </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
      <p class="text-gray-400">No notes found.</p>
    <?php endif; ?>
  </div>
</section>

<div id="progress" class="text-gray-400 mt-6 text-center text-sm mb-10 px-4">
  You’ve read <span id="readCount">0</span> / <?= count($notes) ?> notes ✅
</div>

<?php require_once LAYOUT_FOOT_FILE; ?>
