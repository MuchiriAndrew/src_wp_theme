document.addEventListener('DOMContentLoaded', () => {
  const ring = document.querySelector('.contact-v3-ring');
  if (!ring) return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let frame = 0;
  const updateRing = () => {
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(() => {
      if (reducedMotion.matches) {
        ring.style.setProperty('--ring-parallax', '0px');
        return;
      }
      const sectionTop = ring.parentElement.getBoundingClientRect().top + window.scrollY;
      ring.style.setProperty('--ring-parallax', `${(window.scrollY - sectionTop) * 0.15}px`);
    });
  };

  updateRing();
  window.addEventListener('scroll', updateRing, { passive: true });
  reducedMotion.addEventListener('change', updateRing);
});
