<?php
/**
 * config.example.php
 * SAFE for GitHub — contains NO real credentials.
 * Copy this file to config.php and fill values through .env or hosting panel.
 */

// -------------------------
// APP ENVIRONMENT
// -------------------------
define('APP_ENV', 'local'); // or "production"

// -------------------------
// DATABASE
// -------------------------
define('DB_HOST', 'your_database_host_here');
define('DB_NAME', 'your_database_name_here');
define('DB_USER', 'your_database_user_here');
define('DB_PASS', 'your_database_password_here');

// -------------------------
// EMAIL / SMTP
// -------------------------
define('EMAIL_HOST', 'smtp.example.com');
define('EMAIL_USER', 'your_email_here');
define('EMAIL_PASS', 'your_email_password_here');
define('EMAIL_PORT', 587);

// -------------------------
// API KEYS
// -------------------------
define('GOOGLE_MAPS_API_KEY', 'your_maps_api_key_here');
define('RECAPTCHA_SITE_KEY', 'your_recaptcha_site_key_here');
define('RECAPTCHA_SECRET_KEY', 'your_recaptcha_secret_here');

// -------------------------
// APP SETTINGS
// -------------------------
define('SITE_NAME', 'Your Portfolio');
define('ITEMS_PER_PAGE', 10);

// -------------------------
// UPLOAD SETTINGS
// -------------------------
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
?>
