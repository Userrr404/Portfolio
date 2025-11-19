if (typeof gsap === "undefined") {
  console.error("GSAP not loaded — check extra_js includes");
}

 
 
 // GSAP Animations
    gsap.registerPlugin(ScrollTrigger);
    gsap.utils.toArray('.fade-in-up').forEach((el, i) => {
      gsap.from(el, {
        opacity: 0,
        y: 50,
        duration: 1,
        delay: i * 0.15,
        scrollTrigger: {
          trigger: el,
          start: "top 85%",
        }
      });
    });
    gsap.utils.toArray('.note-card').forEach((card, i) => {
      gsap.to(card, {
        opacity: 1,
        y: 0,
        delay: i * 0.15,
        duration: 0.8,
        scrollTrigger: {
          trigger: card,
          start: "top 90%",
          toggleActions: "play none none reverse",
        }
      });
    });

    // Search + Filter
    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('filterSelect');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.note-card');
    function filterNotes() {
      const term = searchInput.value.toLowerCase();
      const category = filterSelect.value;
      cards.forEach(card => {
        const matchesTerm = card.textContent.toLowerCase().includes(term);
        const matchesCat = category === 'all' || card.dataset.cat === category;
        card.style.display = (matchesTerm && matchesCat) ? 'block' : 'none';
      });
    }
    searchInput.addEventListener('input', filterNotes);
    filterSelect.addEventListener('change', filterNotes);
    filterBtns.forEach(btn => btn.addEventListener('click', () => {
      filterSelect.value = btn.dataset.cat;
      filterNotes();
    }));

    // Save Button
    function toggleSave(btn) {
      btn.classList.toggle('text-accent');
      btn.querySelector('i').classList.toggle('fa-solid');
    }

    // Reading Progress
    let readCount = 0;
    document.querySelectorAll('.note-card a').forEach(a => {
      a.addEventListener('click', () => {
        readCount++;
        document.getElementById('readCount').textContent = readCount;
      });
    });