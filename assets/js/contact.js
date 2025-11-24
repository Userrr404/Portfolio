if (typeof AOS === "undefined") {
  console.error("AOS not loaded — check extra_js includes");
}
  
    AOS.init({ once: true, duration: 800, easing: 'ease-in-out' });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === 'true') {
      const toast = document.getElementById('toast');
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 4000);
    }