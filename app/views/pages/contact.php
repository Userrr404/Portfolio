<?php

// Normalize sections (guaranteed keys due to controller emergency fallback)
$heroSection    = $data['hero']['data']    ?? [];
$infoSection    = $data['info']['data']    ?? [];
$socialsSection = $data['socials']['data'] ?? [];
$mapSection     = $data['map']['data']     ?? [];
$toastMessage = null;  // JS will handle toast from API responses

$page_title = "Contact | Yogesh Lilake";
$custom_css = [CONTACT_CSS];
$custom_js  = [CONTACT_JS];
$extra_css  = [AOS_CSS];

// If RECAPTCHA_SITE_KEY is set, include the recaptcha script
$extra_js   = [AOS_JS];
if (defined('RECAPTCHA_SITE_KEY') && RECAPTCHA_SITE_KEY) {
    $extra_js[] = "https://www.google.com/recaptcha/api.js?render=" . RECAPTCHA_SITE_KEY;
}

require_once LAYOUT_HEAD_FILE;
?>

<!-- Hero Section -->
<section class="text-center py-16 md:py-24 px-4 sm:px-8" data-aos="fade-up" data-aos-duration="1000">
  <h1 class="text-3xl sm:text-5xl lg:text-6xl font-bold mb-6 text-white leading-snug">
    <?= htmlspecialchars($heroSection['heading'] ?? "Let’s Build Something Great Together 🚀") ?>
  </h1>

  <p class="text-base sm:text-lg lg:text-xl text-gray-400 max-w-2xl mx-auto">
    <?= htmlspecialchars($heroSection['subheading'] ?? "Whether it's collaboration or learning — I'm open!") ?>
  </p>

  <?php if (!empty($heroSection['hero_lottie_url'])): ?>
    <div class="mt-10 flex justify-center" data-aos="zoom-in" data-aos-delay="200">
      <?php $asset = $heroSection['hero_lottie_url']; ?>
      <?php if (str_starts_with($asset, 'http') || str_starts_with($asset, 'https')): ?>
        <lottie-player src="<?= htmlspecialchars($asset) ?>" background="transparent" speed="1" style="width: clamp(160px, 35vw, 260px); height: auto;" loop autoplay></lottie-player>
      <?php else: ?>
        <!-- Local asset fallback (file path provided) -->
        <img src="<?= htmlspecialchars($asset) ?>" alt="hero asset" style="width: clamp(160px, 35vw, 260px); height: auto;" />
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Contact Section -->
<section class="relative bg-card py-14 sm:py-16 px-4 sm:px-10 lg:px-20 rounded-t-3xl shadow-inner overflow-hidden">
  <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-16">

    <!-- Left: Contact Form -->
    <div class="bg-darkbg p-6 sm:p-8 rounded-3xl shadow-lg border border-gray-700" data-aos="fade-right">
      <h2 class="text-2xl sm:text-3xl font-semibold mb-6 text-accent">Send a Message</h2>

      <form id="contact-form" class="space-y-5" novalidate>
        <div>
          <label class="block text-sm mb-2">Your Name</label>
          <input type="text" name="name" required class="w-full px-4 py-3 rounded-lg" autocomplete="name">
        </div>

        <div>
          <label class="block text-sm mb-2">Email Address</label>
          <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg" autocomplete="email">
        </div>

        <div>
          <label class="block text-sm mb-2">Message</label>
          <textarea name="message" rows="5" required class="w-full px-4 py-3 rounded-lg resize-none"></textarea>
        </div>

        <!-- Honeypot field (hidden to users, visible to bots) -->
        <div style="display:none !important; visibility:hidden;">
          <label>Leave this field empty</label>
          <input type="text" name="hp_name" value="" autocomplete="off">
        </div>

        <!-- reCAPTCHA token holder (filled by JS if available) -->
        <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">

        <button id="sendBtn" type="submit" class="w-full bg-accent hover:bg-red-500 text-white font-semibold py-3 rounded-lg transition duration-300 hover:scale-105">
          <span class="btn-text">Send Message</span>
          <span class="btn-loading hidden">Sending...</span>
          <i class="fa-solid fa-paper-plane ml-2"></i>
        </button>
      </form>
    </div>

    <!-- Right: Contact Info -->
    <div class="text-gray-300 space-y-6" data-aos="fade-left" data-aos-delay="150">
      <h3 class="text-2xl sm:text-3xl font-semibold text-white">Let’s Talk!</h3>

      <p class="text-gray-400"><?= htmlspecialchars($heroSection['subheading'] ?? '') ?></p>

      <div class="space-y-3">
        <?php if (!empty($infoSection) && is_array($infoSection)): ?>
          <?php foreach ($infoSection as $item): ?>
            <p>
              <i class="<?= htmlspecialchars($item['icon_class'] ?? '') ?> text-accent mr-2"></i>
              <?= htmlspecialchars($item['label'] ?? '') ?>:
              <span><?= htmlspecialchars($item['value'] ?? '') ?></span>
            </p>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No contact info found.</p>
        <?php endif; ?>
      </div>

      <div class="flex gap-5 pt-4">
        <?php if (!empty($socialsSection) && is_array($socialsSection)): ?>
          <?php foreach ($socialsSection as $s): ?>
            <a href="<?= htmlspecialchars($s['url'] ?? '#') ?>" target="_blank" class="hover:text-accent text-2xl">
              <i class="<?= htmlspecialchars($s['icon_class'] ?? '') ?>"></i>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <!-- Map -->
  <?php if (!empty($mapSection['map_embed_url'])): ?>
    <div class="mt-16 rounded-2xl overflow-hidden shadow-lg aspect-[16/9]" data-aos="fade-up" data-aos-delay="200">
      <iframe src="<?= htmlspecialchars($mapSection['map_embed_url']) ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
  <?php endif; ?>

</section>

<!-- Toast -->
<div id="toast"></div>

<?php require_once LAYOUT_FOOT_FILE; ?>
