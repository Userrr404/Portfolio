<?php
require_once __DIR__ . '/../config/config.php';
require_once INCLUDES_PATH . 'logger.php';
require_once CORE_PATH . 'Controller.php';
require_once CORE_PATH . 'App.php';

// Controller returns all required data with DB → Cache → Defaults
$data = App::run("ProjectController@index");

$projects   = $data["projects"];
$techList   = $data["techList"];
$page       = $data["page"];
$totalPages = $data["totalPages"];
$total      = $data["total"];
$filters    = $data["filters"];

function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function img_url($path) {
    if (!$path) return IMG_URL . "placeholder-project.png";
    if (preg_match('#^https?://#', $path)) return $path;
    return BASE_URL . ltrim($path, '/');
}

$page_title = "Projects | " . SITE_TITLE;
$custom_js  = [PROJECTS_JS];

require_once LAYOUT_HEAD_FILE;
?>

<section class="relative py-20 sm:py-24 px-4 text-center overflow-hidden">
    <lottie-player
    src="https://assets10.lottiefiles.com/packages/lf20_pwohahvd.json"
    background="transparent" speed="1" loop autoplay
    style="position:absolute; inset:0; opacity:0.15; z-index:-1;">
  </lottie-player>
  <h1 class="font-extrabold text-accent fade-up text-[clamp(2rem,4vw,3.5rem)] mb-4">Featured 
    <span class="text-white">Projects</span>
  </h1>
  <p class="text-gray-300 max-w-2xl mx-auto text-[clamp(0.9rem,1.2vw,1.25rem)] fade-up">A showcase of my web and mobile projects — combining creativity, scalability, and clean code.</p>
</section>

<section class="py-16 sm:py-20 px-4 sm:px-8 lg:px-16 fade-up">
  <div class="3xl:max-w-[1600px] mx-auto max-w-screen-2xl grid xs:grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 3xl:grid-cols-4 gap-8 sm:gap-10">

    <?php if ($total == 0): ?>
      <div class="bg-[#0f1724] p-10 rounded-xl text-center">
        <h2 class="text-2xl font-bold mb-2">No projects found</h2>
        <p class="text-gray-400">Try different filters or check back later.</p>
      </div>
    <?php else: ?>
      <?php foreach ($projects as $p): ?>
        <div class="bg-[#1E293B] rounded-2xl overflow-hidden shadow-lg hover:scale-[1.03] transition-transform duration-500 group">
          <div class="relative aspect-[4/3]">
            <img src="<?= esc(img_url($p['image_path'])) ?>" alt="<?= esc($p['title']) ?>" 
                  class="absolute inset-0 w-full h-full object-cover group-hover:opacity-80 transition-opacity">
            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
              <a href="<?= esc($p['project_link']) ?>"
                  class="bg-accent text-darkbg px-5 py-2 rounded-full font-semibold hover:bg-red-600 transition">
                    View Project
              </a>
            </div>
          </div>
          <div class="p-5 sm:p-6">
            <h3 class="text-xl sm:text-2xl font-bold text-accent mb-2"><?= esc($p['title']) ?></h3>
            <p class="text-gray-300 mb-4 text-sm sm:text-base leading-relaxed"><?= esc($p['description']) ?></p>

            <div class="flex flex-wrap gap-2">
              <?php foreach ($techList[$p['id']] as $t): ?>
                <span class="<?= esc($t['color_class']) ?> px-3 py-1 rounded-full text-xs font-semibold">
                  <?= esc($t['tech_name']) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <div class="mt-10 flex justify-center gap-2">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>"
           class="px-3 py-1 rounded 
                  <?= $i == $page ? 'bg-accent text-darkbg' : 'bg-gray-700 text-gray-200' ?>">
           <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once LAYOUT_FOOT_FILE; ?>
