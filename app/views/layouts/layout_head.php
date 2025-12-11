<?php
/**
 * GLOBAL <head> + dynamic asset injection
 */

require_once dirname(__DIR__, 3) . '/config/paths.php';
require_once CONFIG_FILE;

// Page dynamic values
$page_title = $page_title ?? SITE_TITLE;
$custom_css = $custom_css ?? [];
$extra_css  = $extra_css ?? [];
$custom_js  = $custom_js ?? [];
$extra_js   = $extra_js ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- FAVICON -->
    <link rel="icon" type="image/png" href="<?= FAVICON_PATH ?>">

    <!-- GLOBAL CSS + ANIMATION CSS-->
    <link rel="stylesheet" href="<?= GLOBAL_CSS ?>">
    <link rel="stylesheet" href="<?= ANIMATIONS_CSS ?>">

    <!-- TOAST GLOBAL CSS -->
    <link rel="stylesheet" href="<?= TOAST_CSS ?>">

    <!-- Page-level CSS -->
    <?php foreach ($custom_css as $css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php endforeach; ?>

    <!-- Extra CSS -->
    <?php foreach ($extra_css as $css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php endforeach; ?>

    <!-- Tailwind + Config -->
    <script src="<?= TAILWIND_CDN ?>"></script>
    <script src="<?= TAILWIND_CONFIG_GLOBAL ?>"></script>

    <!-- Icons + Animations -->
    <script src="<?= LOTTIE_PLAYER ?>"></script>
    <link rel="stylesheet" href="<?= FONT_AWESOME ?>">

    <!-- Extra JS -->
    <?php foreach ($extra_js as $js): ?>
        <script src="<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>

    <!-- reCAPTCHA v3 Script (only if site key exists) -->
    <?php if (RECAPTCHA_SITE_KEY): ?>
        <script src="https://www.google.com/recaptcha/api.js?render=<?= RECAPTCHA_SITE_KEY ?>"></script>
        <script>
            const RECAPTCHA_SITE_KEY = "<?= RECAPTCHA_SITE_KEY ?>";
        </script>
    <?php endif; ?>

</head>

<body class="bg-darkbg text-color font-sans scroll-smooth min-h-screen flex flex-col">

    <!-- TOAST BOX CONTAINER -->
    <div id="toastBox"></div>

    <div id="scroll-progress"></div>

    <!-- AUTO HEADER -->
    <?php include COMPONENT_HEADER_FILE; ?>

    <main class="flex-grow">
