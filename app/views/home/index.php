<?php
/**
 * HOME PAGE VIEW
 * Receives $data from HomeController
 */

// Extract formatted sections (controller guarantees structure)
$home     = $data["home"]["data"] ?? [];
$about    = $data["about"]["data"] ?? [];
$skills   = $data["skills"]["data"] ?? [];
$projects = $data["projects"]["data"] ?? [];
$contact  = $data["contact"]["data"] ?? [];

// Safe helpers
function safe($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, 'UTF-8');
}

function field($array, $key, $default = "") {
    return isset($array[$key]) ? safe($array[$key]) : $default;
}

// Page metadata
$page_title = "Yogesh Portfolio | Full Stack Developer";
$custom_css = [INDEX_CSS];
$custom_js  = [INDEX_JS];

require_once LAYOUT_HEAD_FILE;
?>

<!-- HERO SECTION -->
<section class="text-center py-24 sm:py-28 relative overflow-hidden parallax-bg"
    style="background-image:url('<?= field($home, 'background_image') ?>')">
    
    <lottie-player 
        src="<?= field($home, 'background_lottie') ?>"
        background="transparent"
        speed="1" loop autoplay
        style="position:absolute; inset:0; opacity:0.15; z-index:-1;">
    </lottie-player>

    <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold mb-6 text-accent reveal">
        <?= $home['hero_heading'] ?>
    </h1>

    <p class="text-base sm:text-lg md:text-xl text-gray-300 mb-8 reveal max-w-2xl mx-auto">
        <?= field($home, 'hero_subheading') ?>
    </p>

    <p class="text-gray-400 max-w-2xl mx-auto mb-10 reveal">
        <?= $home['hero_description'] ?? '' ?>
    </p>

    <div class="flex flex-wrap justify-center gap-4 reveal">
        <a href="<?= field($home, 'cta_primary_link') ?>"
            class="bg-accent text-darkbg px-6 py-3 rounded-full font-semibold hover:bg-red-600 transition">
            <?= field($home, 'cta_primary_text') ?>
        </a>

        <a href="<?= DOWNLOAD_CV_URL ?>"
            class="btn border border-accent text-accent px-6 py-3 rounded-full font-semibold hover:bg-accent hover:text-darkbg transition">
            <?= field($home, 'cta_secondary_text') ?>
        </a>
    </div>
</section>

<!-- ABOUT -->
<section class="py-20 px-4 sm:px-10 lg:px-20 text-center bg-[#111827] reveal-left">
    <h2 class="text-3xl sm:text-4xl font-bold mb-8 text-accent">
        <?= safe($about['title']) ?>
    </h2>

    <p class="max-w-3xl mx-auto text-gray-300 leading-relaxed text-base sm:text-lg lg:text-xl">
        <?= $about['content'] ?>
    </p>
</section>

<!-- SKILLS SECTION -->
<section class="py-20 px-4 sm:px-10 lg:px-20 text-center reveal-right">
    <div class="max-w-6xl mx-auto">
        <h3 class="text-3xl sm:text-4xl font-bold mb-10 text-accent">Skills</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4 sm:gap-6">

            <?php if (empty($skills)): ?>
                <p>No skills found.</p>
            <?php else: ?>
                <?php foreach ($skills as $skill): ?>
                    <div class="p-4 bg-[#1E293B] rounded-xl hover:bg-accent hover:text-darkbg transition transform hover:scale-105">
                        <i class="<?= safe($skill['icon_class'] ?? '') ?> <?= safe($skill['color_class'] ?? '') ?> 
                           text-3xl sm:text-4xl md:text-5xl mb-3"></i>

                        <h4 class="font-semibold"><?= field($skill, 'skill_name') ?></h4>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
</section>

<!-- PROJECTS -->
<section class="py-20 px-4 sm:px-10 lg:px-20 bg-[#111827] reveal-left">
    <h2 class="text-3xl sm:text-4xl font-bold mb-12 text-center text-accent">Featured Projects</h2>

    <div class="grid gap-8 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 max-w-screen-xl mx-auto">

        <?php if (empty($projects)): ?>
            <p>No featured projects found.</p>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="bg-[#1E293B] rounded-xl overflow-hidden shadow-lg hover:scale-105 transition">

                    <img src="<?= field($project, 'image_path') ?>" 
                         alt="<?= field($project, 'title') ?>" 
                         class="aspect-video w-full object-cover">

                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2"><?= field($project, 'title') ?></h3>
                        <p class="text-gray-400 text-sm mb-4"><?= field($project, 'description') ?></p>

                        <a href="<?= field($project, 'project_link') ?>" class="text-accent hover:underline">
                            View →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="text-center mt-10">
        <a href="/projects" 
           class="border border-accent text-accent px-6 py-3 rounded-full hover:bg-accent hover:text-darkbg transition">
            See All Projects
        </a>
    </div>
</section>

<!-- CONTACT -->
<section class="py-20 px-4 sm:px-10 lg:px-20 text-center reveal-right">
    <h3 class="text-3xl sm:text-4xl font-bold mb-8 text-accent">
        <?= field($contact, 'title') ?>
    </h3>

    <p class="text-gray-300 mb-10 max-w-xl sm:max-w-2xl mx-auto text-base sm:text-lg">
        <?= field($contact, 'subtitle') ?>
    </p>

    <a href="<?= field($contact, 'button_link') ?>" 
       class="bg-accent text-darkbg px-6 py-3 rounded-full font-semibold hover:bg-red-600 transition">
       <?= field($contact, 'button_text') ?>
    </a>
</section>

<?php require_once LAYOUT_FOOT_FILE; ?>
