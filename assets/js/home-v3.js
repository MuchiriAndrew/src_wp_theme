(() => {
  const programmeRing = document.querySelector('.home-programmes-ring');
  if (programmeRing) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let frame = 0;
    const updateRing = () => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => {
        if (reducedMotion.matches) {
          programmeRing.style.setProperty('--ring-parallax', '0px');
          return;
        }
        const sectionTop = programmeRing.parentElement.getBoundingClientRect().top + window.scrollY;
        programmeRing.style.setProperty('--ring-parallax', `${(window.scrollY - sectionTop) * 0.15}px`);
      });
    };
    updateRing();
    window.addEventListener('scroll', updateRing, { passive: true });
    reducedMotion.addEventListener('change', updateRing);
  }
})();

(() => {
  const tiles = [...document.querySelectorAll('.impact-tile')];

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

  const storyCarousel = document.querySelector('[data-testimonial-carousel]');
  if (storyCarousel) {
    const slides = [...storyCarousel.querySelectorAll('[data-testimonial-slide]')];
    const dots = [...storyCarousel.querySelectorAll('[data-testimonial-dot]')];
    let active = 0;
    let startX = null;
    const showStory = (index) => {
      if (!slides.length) return;
      active = (index + slides.length) % slides.length;
      slides.forEach((slide, i) => {
        slide.querySelector('.home-testimonial-player iframe')?.remove();
        const videoButton = slide.querySelector('[data-testimonial-video]');
        if (videoButton) videoButton.hidden = false;
        slide.hidden = i !== active;
        slide.classList.toggle('is-active', i === active);
      });
      dots.forEach((dot, i) => dot.setAttribute('aria-current', i === active ? 'true' : 'false'));
    };
    storyCarousel.querySelector('[data-testimonial-prev]')?.addEventListener('click', () => showStory(active - 1));
    storyCarousel.querySelector('[data-testimonial-next]')?.addEventListener('click', () => showStory(active + 1));
    dots.forEach((dot) => dot.addEventListener('click', () => showStory(Number(dot.dataset.testimonialDot))));
    storyCarousel.addEventListener('click', (event) => {
      const button = event.target.closest('[data-testimonial-video]');
      if (!button) return;
      let videoId = '';
      try {
        const videoUrl = new URL(button.dataset.testimonialVideo, window.location.href);
        if (videoUrl.hostname === 'youtu.be') videoId = videoUrl.pathname.slice(1).split('/')[0];
        if (videoUrl.hostname.endsWith('youtube.com')) {
          videoId = videoUrl.searchParams.get('v') || videoUrl.pathname.match(/\/(?:embed|shorts)\/([^/?]+)/)?.[1] || '';
        }
      } catch (_) {}
      if (!videoId) return;
      const player = button.closest('.home-testimonial-player');
      if (!player) return;
      const frame = document.createElement('iframe');
      frame.className = 'home-testimonial-iframe';
      frame.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(videoId)}?autoplay=1&rel=0&modestbranding=1&playsinline=1`;
      frame.title = `${button.getAttribute('aria-label')?.replace(/^Play /, '').replace(/'s story$/, '') || 'Entrepreneur'} story`;
      frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      frame.referrerPolicy = 'strict-origin-when-cross-origin';
      frame.allowFullscreen = true;
      button.hidden = true;
      player.append(frame);
    });
    storyCarousel.addEventListener('touchstart', (event) => { startX = event.touches[0]?.clientX ?? null; }, { passive: true });
    storyCarousel.addEventListener('touchend', (event) => {
      if (startX === null) return;
      const delta = event.changedTouches[0].clientX - startX;
      if (Math.abs(delta) > 50) showStory(active + (delta < 0 ? 1 : -1));
      startX = null;
    }, { passive: true });
  }

  const testimonialRing = document.querySelector('.home-testimonial-ring');
  if (testimonialRing) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let frame = 0;
    const updateRing = () => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => {
        if (reducedMotion.matches) {
          testimonialRing.style.setProperty('--ring-parallax', '0px');
          return;
        }
        const sectionTop = testimonialRing.parentElement.getBoundingClientRect().top + window.scrollY;
        testimonialRing.style.setProperty('--ring-parallax', `${(window.scrollY - sectionTop) * 0.15}px`);
      });
    };
    updateRing();
    window.addEventListener('scroll', updateRing, { passive: true });
    reducedMotion.addEventListener('change', updateRing);
  }

  const newsSection = document.querySelector('.home-news-ref');
  if (newsSection) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let frame = 0;
    const updateNewsRing = () => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => {
        const offset = reducedMotion.matches
          ? 0
          : (window.scrollY - (newsSection.getBoundingClientRect().top + window.scrollY)) * 0.15;
        newsSection.style.setProperty('--news-ring-parallax', `${offset}px`);
      });
    };
    updateNewsRing();
    window.addEventListener('scroll', updateNewsRing, { passive: true });
    reducedMotion.addEventListener('change', updateNewsRing);
  }
})();

(() => {
  const countdowns = [...document.querySelectorAll('.v3-countdown[data-deadline]')];
  if (!countdowns.length) return;
  const labels = ['Days', 'Hrs', 'Min', 'Sec'];
  const updateCountdowns = () => countdowns.forEach((countdown) => {
    const rawDeadline = countdown.dataset.deadline || '';
    const deadline = rawDeadline ? new Date(/^\d{4}-\d{2}-\d{2}$/.test(rawDeadline) ? `${rawDeadline}T00:00:00` : rawDeadline).getTime() : 0;
    const distance = deadline ? Math.max(0, deadline - Date.now()) : 0;
    const values = [
      Math.floor(distance / 86400000),
      Math.floor(distance / 3600000) % 24,
      Math.floor(distance / 60000) % 60,
      Math.floor(distance / 1000) % 60,
    ];
    [...countdown.children].forEach((unit, index) => {
      const number = unit.querySelector('b');
      const label = unit.querySelector('small');
      if (number) number.textContent = String(values[index] ?? 0).padStart(2, '0');
      if (label) label.textContent = labels[index] ?? '';
    });
    countdown.classList.toggle('is-expired', !!deadline && deadline <= Date.now());
    countdown.setAttribute('aria-label', deadline && deadline <= Date.now() ? 'Cohort start date has arrived' : 'Cohort countdown');
  });
  updateCountdowns();
  window.setInterval(updateCountdowns, 1000);
})();

(() => {
  const ring = document.querySelector('.v3-impact-ring');
  if (!ring) return;
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let frame = 0;
  const updateRing = () => {
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(() => {
      const offset = reducedMotion.matches ? 0 : (window.scrollY - (ring.parentElement.getBoundingClientRect().top + window.scrollY)) * .15;
      ring.style.setProperty('--impact-ring-parallax', `${offset}px`);
    });
  };
  updateRing();
  window.addEventListener('scroll', updateRing, { passive: true });
  reducedMotion.addEventListener('change', updateRing);
})();

(() => {
  const ring = document.querySelector('.stay-connected-ring');
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
      ring.style.setProperty('--ring-parallax', `${(window.scrollY - sectionTop) * .15}px`);
    });
  };
  updateRing();
  window.addEventListener('scroll', updateRing, { passive: true });
  reducedMotion.addEventListener('change', updateRing);
})();
