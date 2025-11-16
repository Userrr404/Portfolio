<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../app/Models/AboutModel.php';
require_once __DIR__ . '/../app/Services/CacheService.php';

$model = new AboutModel();
$cache = new CacheService();

// ==========================
// LOAD FROM CACHE OR DB
// ==========================
$data = $cache->load('about');

$page_title = "About | " . SITE_TITLE;
$custom_css = [ABOUT_CSS];
$custom_js  = [ABOUT_JS];

if (!$data) {
    $data = [
        "hero"       => $model->getHero(),
        "content"    => $model->getContent(),
        "skills"     => $model->getSkills(),
        "experience" => $model->getExperience(),
        "education"  => $model->getEducation(),
        "stats"      => $model->getStats(),
    ];

    $cache->save('about', $data);
}

require_once LAYOUT_HEAD_FILE;
?>

<!-- HERO -->
<section class="relative py-20 sm:py-24 lg:py-28 overflow-hidden text-center fade-up">
    <?php if (empty($hero['animation_url'])): ?>
        <lottie-player
            src="https://assets10.lottiefiles.com/packages/lf20_jcikwtux.json"
            background="transparent"
            speed="1"
            loop autoplay
            style="position:absolute; inset:0; opacity:<?= $hero['background_opacity'] ?? 0.15 ?>; z-index:-1;">
        </lottie-player>
    <?php else: ?>
        <lottie-player
            src="<?= htmlspecialchars($hero['animation_url']) ?>"
            background="transparent"
            speed="1"
            loop autoplay
            style="position:absolute; inset:0; opacity:<?= $hero['background_opacity'] ?? 0.15 ?>; z-index:-1;">
        </lottie-player>
    <?php endif; ?>

    <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold mb-4 text-accent fade-up">
        <?= htmlspecialchars($hero['title'] ?? "About") ?> <span class="text-white">Me</span>
    </h1>

    <p class="text-gray-300 max-w-2xl mx-auto text-base sm:text-lg md:text-xl fade-up">
        <?= htmlspecialchars($hero['subtitle'] ?? 'Full Stack & Android Developer passionate about modern digital experiences.') ?>
    </p>
</section>

<!-- ABOUT SECTION -->
<section class="py-16 sm:py-20 lg:py-28 px-4 sm:px-8 lg:px-16 flex flex-col-reverse md:flex-row items-center gap-10 md:gap-16 slide-right max-w-screen-xl mx-auto">
    <div class="flex-1 text-center md:text-left max-w-xl">
        <h2 class="ttext-3xl sm:text-4xl lg:text-5xl font-bold mb-6 text-accent">
            <?= htmlspecialchars($content["greeting_title"] ?? "Hi, I'm " . SITE_TITLE . " 👋") ?>
        </h2>


        <?php if (!empty($content['main_description'])): ?>
            <p class="text-gray-300 mb-5 leading-relaxed text-sm sm:text-base md:text-lg">
                <?= nl2br(htmlspecialchars($content['main_description'])) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($content['secondary_description'])): ?>
            <p class="text-gray-300 mb-6 leading-relaxed text-sm sm:text-base md:text-lg">
                <?= nl2br(htmlspecialchars($content['secondary_description'])) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($content["cta_link"])): ?>
        <a href="<?= htmlspecialchars($content["cta_link"]) ?>"
           class="inline-block bg-accent hover:bg-red-600 text-darkbg px-8 py-3 rounded-full font-semibold transition text-sm sm:text-base">
            <?= htmlspecialchars($content["cta_text"] ?? "Download CV") ?>
        </a>
        <?php endif; ?>
    </div>

    <div class="flex-1 flex justify-center">
        <img src="<?= htmlspecialchars($content["profile_image"] ?? IMG_URL."profile.jpg") ?>"
            alt="<?= SITE_TITLE ?>"
            class="w-52 sm:w-64 md:w-80 aspect-square object-cover rounded-3xl shadow-xl border-4 border-accent/40 hover:scale-105 transition-transform duration-700 ease-out float-glow">
    </div>
</section>

<!-- SKILLS -->
<section class="py-16 sm:py-20 bg-[#111827] text-center slide-left">
    <h3 class="text-3xl sm:text-4xl font-bold mb-12 text-accent">Technical Skills</h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-6 sm:gap-8 max-w-screen-xl mx-auto px-4">
        <?php if(empty($skills)): ?>
            <p>No skills found.</p>
        <?php else: ?>
            <?php foreach ($skills as $skill): ?>
                <div class="p-6 bg-[#1E293B] rounded-xl hover:bg-accent hover:text-darkbg transition transform hover:scale-105">
                    <i class="<?= htmlspecialchars($skill['icon_class']) ?> <?= htmlspecialchars($skill['color_class']) ?> text-5xl mb-3"></i>
                    <h4 class="font-semibold"><?= htmlspecialchars($skill['skill_name']) ?></h4>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- EXPERIENCE -->
<section class="py-16 sm:py-20 px-4 sm:px-8 lg:px-16 slide-right">
    <h3 class="text-3xl sm:text-4xl font-bold text-accent text-center mb-10">Experience</h3>
    <div class="grid gap-6 sm:gap-8 md:grid-cols-3 max-w-screen-xl mx-auto">
        <?php if(empty($experience)): ?>
                <p>No experience found.</p>
        <?php else: ?>
            <?php foreach ($experience as $exp): ?>
                <div class="bg-[#1E293B] rounded-xl p-8 min-h-[220px] flex flex-col justify-center hover:scale-105 transition text-center md:text-left">
                    <h4 class="text-xl font-semibold mb-3 text-accent"><?= htmlspecialchars($exp["title"]) ?></h4>
                    <p class="text-gray-300"><?= htmlspecialchars($exp["description"]) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- EDUCATION -->
<section class="py-16 sm:py-20 px-4 sm:px-8 lg:px-16 bg-[#0F172A] slide-left">
    <h3 class="text-3xl sm:text-4xl font-bold text-accent text-center mb-12">Education</h3>
    <div class="max-w-screen-md mx-auto space-y-8 relative before:absolute before:left-4 md:before:left-1/2 before:top-0 before:w-1 before:h-full before:bg-accent/40 before:rounded-full">
        <?php if(empty($education)): ?>
            <p> No education found.</p>
        <?php else: ?>
            <?php foreach ($education as $index => $edu): ?>
                <?php $side = ($index % 2 == 0) ? 'md:pl-10 md:text-left slide-right' : 'md:pr-10 md:text-right slide-left'; ?>
                <div class="relative pl-12 md:pl-0 <?= $side ?>">
                    <div class="bg-[#1E293B] rounded-xl p-6 md:p-8 hover:scale-[1.02] transition transform">
                        <h4 class="text-xl sm:text-2xl font-semibold text-accent mb-1"><?= htmlspecialchars($edu["degree"]) ?></h4>
                        <p class="text-gray-300 font-medium mb-1"><?= htmlspecialchars($edu["institution"]) ?></p>
                        <p class="text-gray-400 text-sm mb-2"><?= htmlspecialchars($edu["period"]) ?></p>
                        <p class="text-gray-400 leading-relaxed text-sm sm:text-base"><?= htmlspecialchars($edu["description"]) ?></p>
                    </div>
                    <span class="absolute left-[0.75rem] md:left-1/2 -translate-x-1/2 top-6 w-4 h-4 bg-accent rounded-full border-4 border-darkbg"></span>
                <div>     
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- STATS -->
<section class="bg-gradient-to-r from-red-700 to-rose-600 py-16 sm:py-20 text-center text-white slide-right">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-10 max-w-screen-xl mx-auto px-4">
        <?php if(empty($stats)): ?>
            <p> No stats found.</p>
        <?php else: ?>
            <?php foreach ($stats as $st): ?>
                <div>
                    <p class="text-4xl sm:text-5xl font-extrabold mb-2"><?= htmlspecialchars($st["value"]) ?></p>
                    <p class="text-gray-200 font-medium text-sm sm:text-base"><?= htmlspecialchars($st["label"]) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once LAYOUT_FOOT_FILE; ?>
