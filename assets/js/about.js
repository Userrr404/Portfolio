// Scroll Animations 
 const animatedElements = document.querySelectorAll('.fade-up, .slide-left, .slide-right');
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('active');
      });
    }, { threshold: 0.2 });
    animatedElements.forEach(el => observer.observe(el));