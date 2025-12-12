<?php
/**
 * PATHS.PHP
 * Absolute filesystem paths + URL paths.
 * Works on localhost + InfinityFree perfectly.
 */

if (defined('PATHS_ALREADY_LOADED')) return;
define('PATHS_ALREADY_LOADED', true);

/* ---------------------------------------------
   1. ROOT PATH (ABSOLUTE SERVER FILESYSTEM PATH)
---------------------------------------------- */
define('ROOT_PATH', str_replace('\\', '/', realpath(__DIR__ . '/../')) . '/');

/* ---------------------------------------------
   2. PUBLIC PATH (PUBLIC DOCUMENT ROOT)
---------------------------------------------- */
define('PUBLIC_PATH', ROOT_PATH . 'public/');

/* ---------------------------------------------
   BOOT FILE (GLOBAL PAGE INITIALIZER)
---------------------------------------------- */
define('BOOTSTRAP_FILE', PUBLIC_PATH . 'bootstrap.php');


/* ---------------------------------------------
   3. APP PATHS
---------------------------------------------- */
define('APP_PATH', ROOT_PATH . 'app/');

define('CORE_PATH', APP_PATH . 'Core/');
define('CONTROLLERS_PATH', APP_PATH . 'Controllers/');
define('MODELS_PATH', APP_PATH . 'Models/');
define('RESOURCES_PATH', APP_PATH . 'resources/');
define('VIEWS_PATH', APP_PATH . 'views/');
define('SERVICES_PATH', APP_PATH . 'Services/');
define('HELPERS_PATH', APP_PATH . 'Helpers/');



define('APP_FILE', CORE_PATH . 'app.php');
define('CONTROLLER_FILE', CORE_PATH . 'Controller.php');
define('DB_CONNECTION_FILE', CORE_PATH . 'DB.php');
define('ERROR_HANDLER_FILE', CORE_PATH . 'ErrorHandler.php');


define('HOME_CONTROLLER_FILE', CONTROLLERS_PATH . 'HomeController.php');
define('ABOUT_CONTROLLER_FILE', CONTROLLERS_PATH . 'AboutController.php');
define('PROJECT_CONTROLLER_FILE', CONTROLLERS_PATH . 'ProjectController.php');
define('NOTE_CONTROLLER_FILE', CONTROLLERS_PATH . 'NotesController.php');
define('CONTACT_CONTROLLER_FILE', CONTROLLERS_PATH . 'ContactController.php');


define('HOME_MODEL_FILE', MODELS_PATH . 'HomeModel.php');
define('ABOUT_MODEL_FILE', MODELS_PATH . 'AboutModel.php');
define('SKILL_MODEL_FILE', MODELS_PATH . 'SkillModel.php');
define('PROJECT_MODEL_FILE', MODELS_PATH . 'ProjectModel.php');
define('CONTACT_MODEL_FILE', MODELS_PATH . 'ContactModel.php');
define('NOTE_MODEL_FILE', MODELS_PATH . 'NoteModel.php');

/* RESOURCES SUBFOLDERS */
define('DEFAULTS_PATH', RESOURCES_PATH . 'defaults/');

/* RESOURCES DEFAULTS SUBFOLDERS & EACH FOLDER FILES*/
define('HEADER_DEFAULTS_PATH', DEFAULTS_PATH . 'header/');
define('HEADER_DEFAULT_FILE', HEADER_DEFAULTS_PATH . 'header.json');
define('NAV_DEFAULT_FILE', HEADER_DEFAULTS_PATH . 'navigation.json');

define('FOOTER_DEFAULTS_PATH', DEFAULTS_PATH . 'footer/');
define('FOOTER_DEFAULT_FILE', FOOTER_DEFAULTS_PATH . 'footer.json');
define('FOOTER_LINKS_DEFAULT_FILE', FOOTER_DEFAULTS_PATH . 'links.json');
define('FOOTER_SOCIAL_DEFAULT_FILE', FOOTER_DEFAULTS_PATH . 'social.json');

define('HOME_DEFAULTS_PATH', DEFAULTS_PATH . 'home/');
define('HOME_DEFAULT_FILE', HOME_DEFAULTS_PATH . 'home.json');
define('HOME_CONTACT_DEFAULT_FILE', HOME_DEFAULTS_PATH . 'contact.json');
define('HOME_ABOUT_DEFAULT_FILE', HOME_DEFAULTS_PATH . 'about.json');
define('HOME_PROJECTS_DEFAULT_FILE', HOME_DEFAULTS_PATH . 'projects.json');
define('HOME_SKILLS_DEFAULT_FILE', HOME_DEFAULTS_PATH . 'skills.json');

define('ABOUT_DEFAULTS_PATH', DEFAULTS_PATH . 'about/');
define('ABOUT_HERO_DEFAULT_FILE', ABOUT_DEFAULTS_PATH . 'hero.json');
define('ABOUT_CONTENT_DEFAULT_FILE', ABOUT_DEFAULTS_PATH . 'content.json');
define('ABOUT_EDUCATION_DEFAULT_FILE', ABOUT_DEFAULTS_PATH . 'education.json');
define('ABOUT_EXPERIENCE_DEFAULT_FILE', ABOUT_DEFAULTS_PATH . 'experience.json');
define('ABOUT_SKILLS_DEFAULT_FILE', ABOUT_DEFAULTS_PATH . 'skills.json');
define('ABOUT_STATS_DEFAULT_FILE', ABOUT_DEFAULTS_PATH . 'stats.json');

define('PROJECTS_DEFAULTS_PATH', DEFAULTS_PATH . 'projects/');
define('PROJECTS_DEFAULT_FILE', PROJECTS_DEFAULTS_PATH . 'projects.json');
define('PROJECTS_FEATURED_DEFAULT_FILE', PROJECTS_DEFAULTS_PATH . 'featured.json');
define('PROJECTS_TECHLIST_DEFAULT_FILE', PROJECTS_DEFAULTS_PATH . 'tech_list.json');

define('NOTES_DEFAULTS_PATH', DEFAULTS_PATH . 'notes/');
define('NOTES_DEFAULT_FILE', NOTES_DEFAULTS_PATH . 'notes.json');
define('NOTES_CATEGORIES_DEFAULT_FILE', NOTES_DEFAULTS_PATH . 'categories.json');
define('NOTES_PINNED_DEFAULT_FILE', NOTES_DEFAULTS_PATH . 'pinned.json');
define('NOTES_TAGS_DEFAULT_FILE', NOTES_DEFAULTS_PATH . 'tags.json');

define('CONTACT_DEFAULTS_PATH', DEFAULTS_PATH . 'contact/');
define('CONTACT_HERO_DEFAULT_FILE', CONTACT_DEFAULTS_PATH . 'contact_hero.json');
define('CONTACT_INFO_DEFAULT_FILE', CONTACT_DEFAULTS_PATH . 'contact_info.json');
define('CONTACT_MAP_DEFAULT_FILE', CONTACT_DEFAULTS_PATH . 'contact_map.json');
define('CONTACT_SOCIALS_DEFAULT_FILE', CONTACT_DEFAULTS_PATH . 'contact_socials.json');
define('CONTACT_TOAST_DEFAULT_FILE', CONTACT_DEFAULTS_PATH . 'contact_toast.json');



define('CACHESERVICE_FILE', SERVICES_PATH . 'CacheService.php');
define('FOOTERSERVICE_FILE', SERVICES_PATH . 'FooterService.php');
define('HEADERSERVICE_FILE', SERVICES_PATH . 'HeaderService.php');
define('MAILSERVICE_FILE', SERVICES_PATH . 'MailService.php');

/* VIEWS SUBFOLDERS & EACH FOLDER FILES */
define('LAYOUTS_PATH', VIEWS_PATH . 'layouts/');
define('LAYOUT_HEAD_FILE', LAYOUTS_PATH . 'layout_head.php');
define('LAYOUT_FOOT_FILE', LAYOUTS_PATH . 'layout_foot.php');

define('COMPONENTS_PATH', VIEWS_PATH . 'components/');
define('COMPONENT_HEADER_FILE', COMPONENTS_PATH . 'header.php');
define('COMPONENT_FOOTER_FILE', COMPONENTS_PATH . 'footer.php');

define('PAGES_PATH', VIEWS_PATH . 'pages/');
define('ABOUT_VIEW_FILE', PAGES_PATH . 'about.php');
define('PROJECT_VIEW_FILE', PAGES_PATH . 'projects.php');
define('NOTES_VIEW_FILE', PAGES_PATH . 'notes.php');
define('CONTACT_VIEW_FILE', PAGES_PATH . 'contact.php');

define('HOME_PATH', VIEWS_PATH . 'home/');
define('HOME_VIEW_FILE', HOME_PATH . 'index.php');


define('LOGGER_FILE', HELPERS_PATH . 'logger.php');
define('HELPERS_FILE', HELPERS_PATH . 'helpers.php');
define('SANITIZER_FILE', HELPERS_PATH . 'sanitizer.php');
define('VIEW_HELPERS_FILE', HELPERS_PATH . 'view_helpers.php');


/* ---------------------------------------------
   4. CONFIG FOLDER
---------------------------------------------- */
define('CONFIG_PATH', ROOT_PATH . 'config/');

define('CONFIG_FILE', CONFIG_PATH . 'config.php');

/* ---------------------------------------------
   5. INCLUDES FOLDER
---------------------------------------------- */
define('INCLUDES_PATH', ROOT_PATH . 'includes/');

/* ---------------------------------------------
   6. ROUTES FOLDER
---------------------------------------------- */
define('ROUTES_PATH', ROOT_PATH . 'routes/');
define('WEB_FILE', ROUTES_PATH . 'web.php');

/* ---------------------------------------------
   7. LOGS FOLDER
---------------------------------------------- */
define('LOGS_PATH', ROOT_PATH . 'logs/');
define('APP_LOG_FILE', LOGS_PATH . 'app.log');
define('CV_LOG_FILE', LOGS_PATH . 'cv.log');

define('STORAGE_PATH', ROOT_PATH . 'storage/');

define('CACHE_PATH', STORAGE_PATH . 'cache/');

/* ---------------------------------------------
   8. ASSETS (FILESYSTEM PATHS)
---------------------------------------------- */
define('ASSETS_PATH', PUBLIC_PATH . 'assets/');
define('CSS_PATH', ASSETS_PATH . 'css/');
define('JS_PATH', ASSETS_PATH . 'js/');
define('IMG_PATH', ASSETS_PATH . 'images/');
define('RESUME_PATH', ASSETS_PATH . 'resume/');


/* ---------------------------------------------
   9. URL BASE DETECTION (WORKS LOCAL + LIVE)
---------------------------------------------- */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? 'https://' : 'http://';

$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

/* LOCALHOST CASE (custom folder name allowed) */
if ($host === 'localhost') {
    // Change "Portfolio" to your actual localhost folder name
    define('BASE_URL', $protocol . $host . '/Portfolio/public/');
} else {
    // InfinityFree & live domain use root public folder
    define('BASE_URL', $protocol . $host . '/');
}

/* ---------------------------------------------
   10. ASSETS (URL PATHS)
---------------------------------------------- */
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMG_URL', ASSETS_URL . 'images/');
define('PROJECT_URL', ASSETS_URL . 'projects/');

define('DOWNLOAD_CV_URL', BASE_URL . 'downloadcv');
define('DOWNLOADS_PATH', BASE_URL . 'downloads');
define('RESUME_URL', DOWNLOADS_PATH . 'Yogesh_Lilake_Resume.pdf');

/* ---------------------------------------------
   11. PAGE ROUTES (URL)
---------------------------------------------- */
define('HOME_URL', BASE_URL);
define('ABOUT_URL', BASE_URL . 'about.php');
define('PROJECTS_URL', BASE_URL . 'projects.php');
define('NOTES_URL', BASE_URL . 'notes.php');
define('CONTACT_URL', BASE_URL . 'contact.php');


define('DOWNLOADS_CV_URL', BASE_URL . 'download-cv.php');


/* ---------------------------------------------
   13. OTHER CONSTANTS
---------------------------------------------- */
define('SITE_TITLE', 'Yogesh Lilake');
define('SITE_LOGO', IMG_URL . 'logo.png');
define('FAVICON_PATH', IMG_URL . 'favicon.png');
define('CTA_TEXT', 'Hire Me');
define('CTA_LINK', '/contact');
define('ACCENT_COLOR', '#ff5a5a');

// =============================
// PAGE ROUTES WITHOUT BASE URL (for DB storage)
// =============================
define("HOME_URL_NO_BASE", '/');
define("ABOUT_URL_NO_BASE", '/about');
define("PROJECTS_URL_NO_BASE", '/projects');
define("NOTES_URL_NO_BASE", '/notes');
define("CONTACT_URL_NO_BASE", '/contact');

// =============================
// EXTERNAL LIBRARY URLs (Tailwind, Font Awesome, Lottie)
// =============================
define('TAILWIND_CDN', 'https://cdn.tailwindcss.com');
define('AOS_CSS', 'https://unpkg.com/aos@2.3.4/dist/aos.css');
define('AOS_JS', 'https://unpkg.com/aos@2.3.4/dist/aos.js');
define('FONT_AWESOME', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');
define('LOTTIE_PLAYER', 'https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js');
define('GSAP_CDN', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js');
define('GSAP_SCROLLTRIGGER_CDN', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js');

// ====================================================
// PAGE ROUTES
// ====================================================
// Global asset paths
define('GLOBAL_CSS', CSS_URL . 'global.css');
define('ANIMATIONS_CSS', CSS_URL . 'animations.css');
define('TOAST_CSS', CSS_URL . 'toast.css');

// Tailwind config paths
define('TAILWIND_CONFIG_JS', JS_URL . 'tailwind-config.js');
define('TAILWIND_CONFIG_GLOBAL', JS_URL . 'tailwind-config-global.js');
define('TOAST_JS', JS_URL . 'toast.js');

// Page-specific asset paths
// CSS paths
define('HEADER_CSS', CSS_URL . 'header.css');
define('FOOTER_CSS', CSS_URL . 'footer.css');
define('INDEX_CSS', CSS_URL . 'index.css');
define('ABOUT_CSS', CSS_URL . 'about.css');
define('NOTES_CSS', CSS_URL . 'notes.css');
define('CONTACT_CSS', CSS_URL . 'contact.css');

// Footer asset paths
define('HEADER_JS', JS_URL . 'header.js');
define('FOOTER_JS', JS_URL . 'footer.js');
define('SCROLL_PROGRESS_JS', JS_URL . 'scroll-progress.js');
define('INDEX_JS', JS_URL . 'index.js');
define('ABOUT_JS', JS_URL . 'about.js');
define('PROJECTS_JS', JS_URL . 'projects.js');
define('NOTES_JS', JS_URL . 'notes.js');
define('CONTACT_JS', JS_URL . 'contact.js');

?>
