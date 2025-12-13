<?php

$project = $data['project'] ?? [];

/**
 * Enterprise-safe helpers
 */
function esc($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$page_title = esc($project['title'] ?? 'Project') . " | Projects";
require_once LAYOUT_HEAD_FILE;
?>

<section class="py-20 max-w-5xl mx-auto px-6">

    <!-- Title -->
    <h1 class="text-4xl font-bold text-accent mb-4">
        <?= esc($project['title'] ?? 'Untitled Project') ?>
    </h1>

    <!-- Description -->
    <p class="text-gray-300 mb-8 leading-relaxed">
        <?= esc($project['full_desc'] ?? $project['description'] ?? '') ?>
    </p>

    <!-- Image -->
    <?php if (!empty($project['image_path'])): ?>
        <img
            src="<?= esc($project['image_path']) ?>"
            alt="<?= esc($project['title'] ?? 'Project image') ?>"
            class="rounded-xl mb-8 w-full max-h-[420px] object-cover"
        >
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-4">

        <?php if (!empty($project['github_url'])): ?>
            <a href="<?= esc($project['github_url']) ?>"
               target="_blank" rel="noopener noreferrer"
               class="px-6 py-2 rounded bg-gray-700 text-white font-semibold hover:bg-gray-600 transition">
               GitHub
            </a>
        <?php endif; ?>

        <?php if (!empty($project['live_url'])): ?>
            <a href="<?= esc($project['live_url']) ?>"
               target="_blank" rel="noopener noreferrer"
               class="px-6 py-2 rounded bg-accent text-darkbg font-semibold hover:bg-red-600 transition">
               Live Demo
            </a>
        <?php endif; ?>

    </div>

</section>

<?php require_once LAYOUT_FOOT_FILE; ?>
