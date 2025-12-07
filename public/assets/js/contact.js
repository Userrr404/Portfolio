// AOS Init
if (typeof AOS !== "undefined") {
  AOS.init({ once: true, duration: 800 });
}

// Toast helper
function showToast(msg, type = "success") {
  const toast = document.getElementById("toast");

  toast.style.background = type === "success" ? "#10b981" : "#ef4444";
  toast.textContent = msg;

  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 4000);
}

// AJAX form submission
document.getElementById("contact-form").addEventListener("submit", async function (e) {
  e.preventDefault();

  const btn = document.getElementById("sendBtn");
  btn.disabled = true;
  btn.querySelector(".btn-text").classList.add("hidden");
  btn.querySelector(".btn-loading").classList.remove("hidden");

  const formData = new FormData(this);

  try {
    const response = await fetch("send_message.php", {
      method: "POST",
      body: formData
    });

    const result = await response.json();

    if (result.status === "success") {
      showToast(result.message, "success");
      this.reset();
    } else {
      showToast(result.message, "error");
    }

  } catch (err) {
    showToast("Unexpected error ❌", "error");
  }

  btn.disabled = false;
  btn.querySelector(".btn-text").classList.remove("hidden");
  btn.querySelector(".btn-loading").classList.add("hidden");
});
