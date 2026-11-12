document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('.leadership-v3');
  if (!root) return;

  const subnav = root.querySelector('[data-leadership-subnav]');
  const row = subnav?.querySelector('.sticky-subnav-row');
  const links = [...root.querySelectorAll('[data-team-section-link]')];
  const sections = [...root.querySelectorAll('[data-team-section]')];
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  const updateSubnav = () => {
    if (!subnav) return;
    const docked = subnav.getBoundingClientRect().top <= 88;
    subnav.classList.toggle('sticky-subnav--docked', docked);
    document.documentElement.classList.toggle('subnav-is-docked', docked);
    if (row) {
      row.parentElement.classList.toggle('sticky-subnav-scroll--left', row.scrollLeft > 1);
      row.parentElement.classList.toggle('sticky-subnav-scroll--right', row.scrollLeft + row.clientWidth < row.scrollWidth - 1);
    }

    let current = sections[0]?.id;
    sections.forEach((section) => {
      if (section.getBoundingClientRect().top <= 170) current = section.id;
    });
    links.forEach((link) => {
      const active = link.hash === `#${current}`;
      link.classList.toggle('sticky-subnav-item--active', active);
      if (active) link.setAttribute('aria-current', 'location');
      else link.removeAttribute('aria-current');
    });
  };

  updateSubnav();
  window.addEventListener('scroll', updateSubnav, { passive: true });
  window.addEventListener('resize', updateSubnav, { passive: true });
  row?.addEventListener('scroll', updateSubnav, { passive: true });
  if (row && 'ResizeObserver' in window) new ResizeObserver(updateSubnav).observe(row);

  const ring = root.querySelector('.contact-v3-ring');
  if (ring) {
    let frame = 0;
    const updateRing = () => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => {
        if (reduceMotion.matches) {
          ring.style.setProperty('--ring-parallax', '0px');
          return;
        }
        const sectionTop = ring.parentElement.getBoundingClientRect().top + window.scrollY;
        ring.style.setProperty('--ring-parallax', `${(window.scrollY - sectionTop) * 0.15}px`);
      });
    };
    updateRing();
    window.addEventListener('scroll', updateRing, { passive: true });
    reduceMotion.addEventListener('change', updateRing);
  }

  root.querySelectorAll('[data-team-county-filter]').forEach((button) => {
    button.addEventListener('click', () => {
      const selected = button.dataset.teamCountyFilter;
      root.querySelectorAll('[data-team-county-filter]').forEach((filter) => {
        const active = filter === button;
        filter.classList.toggle('is-active', active);
        filter.setAttribute('aria-pressed', String(active));
      });
      root.querySelectorAll('[data-team-programme-grid] [data-team-card]').forEach((card) => {
        card.hidden = selected !== 'All' && card.dataset.county !== selected;
      });
    });
  });

  const initials = (name) => name.trim().split(/\s+/).slice(0, 2).map((part) => [...part][0] || '').join('');
  const patternMarkup = (tone) => {
    const tones = {
      red: ['#B3262C', '#C53B41', '#9F2026'],
      grey: ['#8A8C8F', '#9C9EA1', '#76787B'],
      yellow: ['#F2A33A', '#F6B65C', '#D99025'],
    };
    const colors = tones[tone] || tones.red;
    return `<svg viewBox="0 0 100 125" preserveAspectRatio="none" class="leadership-person-pattern" aria-hidden="true"><rect width="100" height="125" fill="${colors[0]}"/><g opacity=".72"><path d="M0 0h28L14 24Z" fill="${colors[1]}"/><path d="M28 0h28L42 24Z" fill="${colors[2]}"/><path d="M0 24h28L14 48Z" fill="${colors[2]}"/><path d="M28 24h28L42 48Z" fill="${colors[1]}"/><path d="M56 0h22L67 19Z" fill="${colors[1]}"/><path d="M78 0h22L89 19Z" fill="${colors[2]}"/><path d="M56 19h22L67 38Z" fill="${colors[2]}"/><path d="M78 19h22L89 38Z" fill="${colors[1]}"/><path d="M67 38h16L75 52Z" fill="${colors[1]}"/><path d="M83 38h17L91.5 52Z" fill="${colors[2]}"/><path d="M76 52h12L82 63Z" fill="${colors[2]}"/><path d="M88 52h12L94 63Z" fill="${colors[1]}"/></g></svg>`;
  };

  let activeModal = null;
  let modalOpener = null;
  const closeModal = () => {
    if (!activeModal) return;
    const previousOverflow = activeModal.dataset.previousOverflow || '';
    const previousRootOverflow = activeModal.dataset.previousRootOverflow || '';
    activeModal.remove();
    document.removeEventListener('keydown', handleModalKeydown);
    activeModal = null;
    document.body.style.overflow = previousOverflow;
    document.documentElement.style.overflow = previousRootOverflow;
    modalOpener?.focus();
    modalOpener = null;
  };

  const openModal = (card, trigger) => {
    if (!card.dataset.bio) return;
    closeModal();
    modalOpener = trigger;
    const modal = document.createElement('div');
    modal.className = 'leadership-bio-modal-backdrop';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-label', `${card.dataset.name} biography`);
    modal.tabIndex = -1;
    modal.dataset.previousOverflow = document.body.style.overflow;
    modal.dataset.previousRootOverflow = document.documentElement.style.overflow;

    const shell = document.createElement('div');
    shell.className = 'leadership-bio-modal-shell';
    const photoPanel = document.createElement('div');
    photoPanel.className = `leadership-bio-modal-photo-panel leadership-person-card--${card.dataset.tone}`;
    photoPanel.innerHTML = patternMarkup(card.dataset.tone);
    const portraitOutline = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    portraitOutline.setAttribute('viewBox', '0 0 100 100');
    portraitOutline.setAttribute('preserveAspectRatio', 'none');
    portraitOutline.setAttribute('aria-hidden', 'true');
    portraitOutline.classList.add('leadership-portrait-outline');
    const outlinePath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    outlinePath.setAttribute('d', 'M18 58V16H82V46Q82 58 70 58Z');
    outlinePath.setAttribute('fill', 'none');
    outlinePath.setAttribute('stroke', 'rgba(255,255,255,.7)');
    outlinePath.setAttribute('stroke-width', '1.5');
    outlinePath.setAttribute('vector-effect', 'non-scaling-stroke');
    portraitOutline.append(outlinePath);
    photoPanel.append(portraitOutline);
    if (card.dataset.image) {
      const photo = document.createElement('img');
      photo.className = 'leadership-bio-modal-photo';
      photo.src = card.dataset.image;
      photo.alt = card.dataset.name;
      photoPanel.append(photo);
    } else {
      const nameMark = document.createElement('span');
      nameMark.className = 'leadership-modal-initials';
      nameMark.textContent = initials(card.dataset.name);
      photoPanel.append(nameMark);
    }

    const content = document.createElement('div');
    content.className = 'leadership-bio-modal-content';
    const header = document.createElement('header');
    header.className = 'leadership-bio-modal-header';
    const headingGroup = document.createElement('div');
    const role = document.createElement('div');
    role.className = 'leadership-modal-role';
    role.textContent = card.dataset.role;
    const heading = document.createElement('h2');
    heading.textContent = card.dataset.name;
    headingGroup.append(role, heading);
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'leadership-bio-modal-close';
    close.setAttribute('aria-label', 'Close biography');
    close.textContent = '×';
    header.append(headingGroup, close);

    const scrollWrap = document.createElement('div');
    scrollWrap.className = 'leadership-bio-modal-scroll-wrap';
    const scroll = document.createElement('div');
    scroll.className = 'leadership-bio-modal-scroll';
    scroll.tabIndex = 0;
    scroll.setAttribute('role', 'region');
    scroll.setAttribute('aria-label', `${card.dataset.name} full biography`);
    const bioContent = document.createElement('div');
    bioContent.className = 'leadership-modal-bio-copy';
    card.dataset.bio.split(/\n\s*\n/).filter(Boolean).forEach((paragraphText) => {
      const paragraph = document.createElement('p');
      paragraph.textContent = paragraphText.trim();
      bioContent.append(paragraph);
    });
    scroll.append(bioContent);
    scrollWrap.append(scroll);

    const footer = document.createElement('footer');
    footer.className = 'leadership-bio-modal-footer';
    if (card.dataset.linkedin) {
      const linkedin = document.createElement('a');
      linkedin.className = 'leadership-bio-modal-link';
      linkedin.href = card.dataset.linkedin;
      linkedin.target = '_blank';
      linkedin.rel = 'noreferrer';
      linkedin.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286ZM5.337 7.433a2.062 2.062 0 1 1 0-4.123 2.062 2.062 0 0 1 0 4.123ZM7.119 20.452H3.555V9h3.564v11.452Z"/></svg> LinkedIn';
      footer.append(linkedin);
    }

    content.append(header, scrollWrap, footer);
    shell.append(photoPanel, content);
    modal.append(shell);
    modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
    close.addEventListener('click', closeModal);
    document.addEventListener('keydown', handleModalKeydown);
    document.body.append(modal);
    document.body.style.overflow = 'hidden';
    document.documentElement.style.overflow = 'hidden';
    activeModal = modal;
    close.focus();
  };

  function handleModalKeydown(event) {
    if (event.key === 'Escape' && activeModal) {
      event.preventDefault();
      closeModal();
    } else if (event.key === 'Tab' && activeModal) {
      const focusable = [...activeModal.querySelectorAll('button, a[href], [tabindex="0"]')];
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  }

  root.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-open-team-bio]');
    if (trigger) {
      event.preventDefault();
      event.stopPropagation();
      openModal(trigger.closest('[data-team-card]'), trigger);
      return;
    }
    if (event.target.closest('[data-team-linkedin]')) return;
    const card = event.target.closest('[data-team-card]');
    if (card && !window.matchMedia('(hover: hover)').matches && card.dataset.bioShort) {
      const open = card.getAttribute('aria-expanded') !== 'true';
      card.classList.toggle('leadership-person-card--open', open);
      card.setAttribute('aria-expanded', String(open));
    }
  });

  root.addEventListener('keydown', (event) => {
    const card = event.target.closest('[data-team-card]');
    if (!card || !card.dataset.bioShort || !['Enter', ' '].includes(event.key)) return;
    if (event.target.closest('button,a')) return;
    event.preventDefault();
    const open = card.getAttribute('aria-expanded') !== 'true';
    card.classList.toggle('leadership-person-card--open', open);
    card.setAttribute('aria-expanded', String(open));
  });

  root.querySelectorAll('.leadership-v3-hero-primary').forEach((link) => {
    link.addEventListener('click', (event) => {
      const target = document.getElementById('team-sections');
      if (!target) return;
      event.preventDefault();
      target.scrollIntoView({ behavior: reduceMotion.matches ? 'auto' : 'smooth' });
    });
  });
});
