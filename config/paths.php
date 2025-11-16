<?php
/**
 * PATHS.PHP
 * Handles ONLY paths + URL generation.
 * Zero business logic, zero secrets.
 */

// AUTO-DETECT PROTOCOl
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';

// BASE URL GENERATION
$host = $_SERVER['HTTP_HOST'];

// ---------------------------------------------------------
// ROOT DIRECTORY (Absolute server path)
// ---------------------------------------------------------
$root = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/../')) . '/');
define('ROOT_PATH', $root);

// -------------------------------------
// DOCUMENT ROOT (public folder)
// -------------------------------------
$docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');

// ---------------------------------------------------------
// BASE URL (auto-detected, works everywhere)
// ---------------------------------------------------------
$base_url = $protocol . $host . str_replace($docRoot, '', ROOT_PATH);

// Normalize trailing slashes
define('BASE_URL', rtrim($base_url, '/') . '/');

// ====================================================
// SERVER PATH CONSTANTS (ABSOLUTE FILESYSTEM PATHS)
// ====================================================
define('CONFIG_PATH', ROOT_PATH . 'config/');
define('CORE_PATH', ROOT_PATH . 'core/');
define('LOGS_PATH', ROOT_PATH . 'logs/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('VIEWS_PATH', ROOT_PATH . 'views/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');

// Core files
define('CONFIG_FILE', CONFIG_PATH . 'config.php');
define('PATHS_FILE', CONFIG_PATH . 'paths.php');
define('DB_CONNECTION_PATH', CORE_PATH . 'db_connection.php');
define('HEADER_DATA_FILE', CORE_PATH . 'HeaderData.php');
define('FOOTER_DATA_FILE', CORE_PATH . 'FooterData.php');
define('LOG_FILE', LOGS_PATH . 'app.log');

// Layout includes
define('LAYOUT_HEAD_FILE', INCLUDES_PATH . 'layout_head.php');
define('LAYOUT_FOOT_FILE', INCLUDES_PATH . 'layout_foot.php');
define('FOOTER_FILE', INCLUDES_PATH . 'footer.php');
define('HEADER_FILE', INCLUDES_PATH . 'header.php');
define('LOGGER_PATH', INCLUDES_PATH . 'logger.php');


// ====================================================
// PUBLIC URL CONSTANTS (FOR USE IN HTML)
// ====================================================
define('ASSETS_URL', BASE_URL . 'assets/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');
define('IMG_URL', ASSETS_URL . 'images/');
define('ICON_URL', ASSETS_URL . 'icons/');

// ====================================================
// PAGE ROUTES
// ====================================================
define('VIEWS_URL', BASE_URL . 'views/');
define('HOME_URL', VIEWS_URL . 'index.php');
define('ABOUT_URL', VIEWS_URL . 'about.php');
define('PROJECTS_URL', VIEWS_URL . 'projects.php');
define('NOTES_URL', VIEWS_URL . 'notes.php');
define('CONTACT_URL', VIEWS_URL . 'contact.php');

// ---------------------------------------------------------
// SITE GLOBAL CONSTANTS
// ---------------------------------------------------------
define('SITE_TITLE', 'Yogesh Lilake');
define('SITE_LOGO', IMG_URL . 'logo.png');
define('FAVICON_PATH', IMG_URL . 'favicon.png');
define('CTA_TEXT', 'Download CV');
define('CTA_LINK', ASSETS_URL . 'Yogesh_Lilake_Resume.pdf');
define('ACCENT_COLOR', '#ff5a5a');

// =============================
// EXTERNAL LIBRARY URLs
// =============================
define('TAILWIND_CDN', 'https://cdn.tailwindcss.com');
define('AOS_CSS', 'https://unpkg.com/aos@2.3.4/dist/aos.css');
define('AOS_JS', 'https://unpkg.com/aos@2.3.4/dist/aos.js');
define('FONT_AWESOME', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css');
define('LOTTIE_PLAYER', 'https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js');

// ====================================================
// PAGE ROUTES
// ====================================================
// Global asset paths
define('GLOBAL_CSS', CSS_URL . 'global.css');
define('ANIMATIONS_CSS', CSS_URL . 'animations.css');

// Tailwind config paths
define('TAILWIND_CONFIG_JS', JS_URL . 'tailwind-config.js');
define('TAILWIND_CONFIG_GLOBAL', JS_URL . 'tailwind-config-global.js');

// Page-specific asset paths
// CSS paths
define('HEADER_CSS', CSS_URL . 'header.css');
define('FOOTER_CSS', CSS_URL . 'footer.css');
define('INDEX_CSS', CSS_URL . 'index.css');
define('ABOUT_CSS', CSS_URL . 'about.css');
define('PROJECTS_CSS', CSS_URL . 'projects.css');
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
