// ===== Intersection Observer & Scroll Animations ===== //

    const animated = document.querySelectorAll('.fade-up');
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    animated.forEach(el => observer.observe(el));
  


  

// ===== Disable Lottie on Mobile for Performance ===== //

    if (window.innerWidth < 640) {
      document.querySelectorAll('lottie-player').forEach(el => el.remove());
    }
  