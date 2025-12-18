// Initialize AOS if present
if (typeof AOS !== "undefined") {
  AOS.init({ once: true, duration: 800 });
}

// Toast helper
function showToast(msg, type = "success") {
  const toast = document.getElementById("toast");
  if (!toast) return;
  toast.style.background = type === "success" ? "#10b981" : "#ef4444";
  toast.textContent = msg;

  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 4000);
}

async function getRecaptchaToken() {
  // If grecaptcha is available and site key set, execute v3
  if (typeof grecaptcha !== "undefined" && typeof RECAPTCHA_SITE_KEY !== "undefined") {
    try {
      // RECAPTCHA_SITE_KEY will be injected by the view (via script include)
      return await grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'contact_submit' });
    } catch (err) {
      console.warn("reCAPTCHA execute failed", err);
      return '';
    }
  }
  return '';
}

document.getElementById('contact-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('sendBtn');
  btn.disabled = true;
  btn.querySelector('.btn-text').classList.add('hidden');
  btn.querySelector('.btn-loading').classList.remove('hidden');

  const fd = new FormData(this);

  try {
    const resp = await fetch('/Portfolio/public/contact/send', {
      method: 'POST',
      body: fd
    });

    const data = await resp.json();
    if (data.status === 'success') {
      showToast(data.message, 'success');
      this.reset();
    } else {
      showToast(data.message || 'Error', 'error');
    }

  } catch (err) {
    console.error(err);
    showToast('Unexpected error. Try again later.', 'error');
  } finally {
    btn.disabled = false;
    btn.querySelector('.btn-text').classList.remove('hidden');
    btn.querySelector('.btn-loading').classList.add('hidden');
  }
});
