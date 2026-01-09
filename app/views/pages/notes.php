<?php
/**
 * NOTES PAGE
 * Safe-mode aware
 */

// Extract safely
$notes        = $data["notes"]        ?? [];
$categories   = $data["categories"]   ?? [];
$tags         = $data["tags"]         ?? [];
$pinned_notes = $data["pinned_notes"] ?? [];


$safeMode = $data['safe_mode'] ?? false;

$page_title = "Notes | " . SITE_TITLE;
$custom_css = [NOTES_CSS];
$custom_js  = $safeMode ? [] : [NOTES_JS];

$extra_js = $safeMode ? [] : [
    GSAP_CDN,
    GSAP_SCROLLTRIGGER_CDN,
];

require_once LAYOUT_HEAD_FILE;
?>

<?php if ($safeMode): ?>
    <div class="bg-yellow-500 text-black text-center py-3 px-4 font-semibold">
        Notes are temporarily in read-only mode.
    </div>
<?php endif; ?>

<!-- HERO -->
<section class="relative min-h-[60vh] flex flex-col justify-center items-center text-center overflow-hidden px-4 sm:px-6">
    <lottie-player
        src="https://assets1.lottiefiles.com/packages/lf20_yr6zz3wv.json"
        background="transparent"
        speed="1"
        class="w-[60vw] sm:w-[40vw] lg:w-[25vw] max-w-[420px] aspect-square"
        loop autoplay>
    </lottie-player>

    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mt-6 text-white px-2 <?= $safeMode ? '' : 'fade-in-up' ?>">
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

                    <?php if (!$safeMode): ?>
                        <a href="<?= url('notes/' . $note['slug']) ?>" class="text-accent hover:underline text-sm font-medium">
                            Read More →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-400">No pinned notes available.</p>
        <?php endif; ?>
    </div>
</section>

<!-- SEARCH + FILTER -->
<?php if (!$safeMode): ?>
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
<?php endif; ?>


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
                    data-cat="<?= safe($note['category_slug']) ?>">

                    <h3 class="text-xl font-semibold mb-2 text-white">
                        <?= safe($note['title']) ?>
                    </h3>

                    <p class="text-gray-400 text-sm mb-4">
                        <?= safe(substr((string)($note['description'] ?? ''), 0, 100)) ?>...
                    </p>

                    <?php if (!$safeMode): ?>
                        <div class="flex justify-between items-center">
                            <a href="<?= url('notes/' . $note['slug']) ?>" class="text-accent hover:underline text-sm font-medium">
                                Read More →
                            </a>

                            <button onclick="toggleSave(this)"
                                    class="text-sm text-gray-400 hover:text-accent flex items-center gap-1 transition">
                                <i class="fa-regular fa-bookmark"></i> Save
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-400 text-center">No notes available.</p>
        <?php endif; ?>
    </div>
</section>

<?php if ($safeMode): ?>
    <!-- SAFE MODE QUICK LINKS (TRUST + NAVIGATION) -->
    <section class="py-12 text-center text-gray-400">
        <p class="mb-4">You can still explore:</p>
        <div class="flex justify-center gap-6 font-medium">
            <a href="<?= url('') ?>" class="hover:text-accent">Home</a>
            <a href="<?= url('about') ?>" class="hover:text-accent">About</a>
            <a href="<?= url('projects') ?>" class="hover:text-accent">Projects</a>
            <a href="<?= url('contact') ?>" class="hover:text-accent">Contact</a>
        </div>
    </section>
<?php endif; ?>

<?php require_once LAYOUT_FOOT_FILE; ?>
