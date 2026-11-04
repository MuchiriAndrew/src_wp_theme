(() => {
  const tiles = [...document.querySelectorAll('.impact-tile')];
  if (!tiles.length) return;

  const closeTile = (tile) => {
    tile.classList.remove('impact-tile--open');
    tile.querySelector('.impact-tile-trigger')?.setAttribute('aria-expanded', 'false');
    tile.querySelector('.impact-panel')?.setAttribute('inert', '');
  };
  const openTile = (tile) => {
    tile.classList.add('impact-tile--open');
    tile.querySelector('.impact-tile-trigger')?.setAttribute('aria-expanded', 'true');
    tile.querySelector('.impact-panel')?.removeAttribute('inert');
  };

  tiles.forEach((tile) => {
    const trigger = tile.querySelector('.impact-tile-trigger');
    if (!trigger) return;
    tile.addEventListener('pointerenter', (event) => {
      if (event.pointerType === 'mouse') openTile(tile);
    });
    tile.addEventListener('pointerleave', (event) => {
      if (event.pointerType === 'mouse' && !tile.contains(document.activeElement)) closeTile(tile);
    });
    tile.addEventListener('focusin', () => openTile(tile));
    tile.addEventListener('focusout', (event) => {
      if (!tile.contains(event.relatedTarget)) closeTile(tile);
    });
    trigger.addEventListener('click', () => {
      if (window.matchMedia('(hover: none)').matches) {
        tile.classList.contains('impact-tile--open') ? closeTile(tile) : openTile(tile);
      }
    });
  });

  document.addEventListener('pointerdown', (event) => {
    tiles.forEach((tile) => {
      if (!tile.contains(event.target) && window.matchMedia('(hover: none)').matches) closeTile(tile);
    });
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') tiles.forEach(closeTile);
  });

  const counters = [...document.querySelectorAll('[data-impact-count]')];
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const animate = (counter) => {
    const target = Number.parseInt(counter.dataset.impactCount || '0', 10);
    if (reduceMotion) {
      counter.textContent = target.toLocaleString();
      return;
    }
    const start = performance.now();
    const tick = (now) => {
      const progress = Math.min((now - start) / 1400, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      counter.textContent = Math.round(target * eased).toLocaleString();
      if (progress < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };
  const observer = new IntersectionObserver((entries, instance) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        animate(entry.target);
        instance.unobserve(entry.target);
      }
    });
  }, { threshold: 0.25 });
  counters.forEach((counter) => observer.observe(counter));
})();
