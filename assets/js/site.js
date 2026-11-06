document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('.site-header');
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.primary-nav');
  const closeButton = document.querySelector('.mobile-nav-close');
  const scrim = document.querySelector('.nav-scrim');
  if (!header || !toggle || !nav) return;
  const navHome = nav.parentElement;
  const scrimHome = scrim?.parentElement;
  const isMobileNav = () => window.matchMedia('(max-width: 1023px)').matches;
  const placeNavForViewport = () => {
    if (isMobileNav()) {
      nav.classList.add('is-mobile-portal');
      scrim?.classList.add('is-mobile-portal');
      if (scrim) document.body.append(scrim);
      document.body.append(nav);
    } else if (nav.parentElement !== navHome) {
      nav.classList.remove('is-mobile-portal');
      scrim?.classList.remove('is-mobile-portal');
      if (scrim && scrimHome) scrimHome.append(scrim);
      navHome?.append(nav);
    }
  };
  placeNavForViewport();
  const focusableInNav = () => [...nav.querySelectorAll('a, button')].filter((element) =>
    !element.disabled && !element.closest('.sub-menu[inert]') && element.getClientRects().length
  );
  const closeSubmenus = () => nav.querySelectorAll('.menu-item-has-children').forEach((item) => {
    item.classList.remove('is-submenu-open');
    const button = item.querySelector(':scope > .nav-parent-button');
    const submenu = item.querySelector(':scope > .sub-menu');
    button?.setAttribute('aria-expanded', 'false');
    const indicator = button?.querySelector('.nav-parent-indicator');
    if (indicator) indicator.textContent = '+';
    if (submenu) {
      submenu.setAttribute('aria-hidden', 'true');
      submenu.inert = true;
    }
  });
  const setNavOpen = (open, restoreFocus = true) => {
    const nextState = Boolean(open && isMobileNav());
    header.classList.toggle('nav-open', nextState);
    nav.classList.toggle('is-open', nextState);
    scrim?.classList.toggle('is-open', nextState);
    document.body.classList.toggle('nav-locked', nextState);
    nav.setAttribute('aria-hidden', String(!nextState && isMobileNav()));
    toggle.setAttribute('aria-expanded', String(nextState));
    toggle.setAttribute('aria-label', nextState ? 'Close navigation' : 'Open navigation');
    nav.inert = isMobileNav() && !nextState;
    if (!nextState) closeSubmenus();
    if (nextState && restoreFocus) (closeButton || nav.querySelector('.nav-parent-button, a'))?.focus({ preventScroll: true });
    if (!nextState && restoreFocus) toggle.focus({ preventScroll: true });
  };
  toggle.addEventListener('click', () => setNavOpen(!header.classList.contains('nav-open')));
  closeButton?.addEventListener('click', () => setNavOpen(false));
  scrim?.addEventListener('click', () => setNavOpen(false, false));
  nav.querySelectorAll('.menu-item-has-children').forEach((item) => {
    const button = item.querySelector(':scope > .nav-parent-button');
    const submenu = item.querySelector(':scope > .sub-menu');
    if (!button || !submenu) return;
    const setSubmenu = (open) => {
      item.classList.toggle('is-submenu-open', open);
      button.setAttribute('aria-expanded', String(open));
      const indicator = button.querySelector('.nav-parent-indicator');
      if (indicator) indicator.textContent = isMobileNav() && open ? '−' : '+';
      submenu.setAttribute('aria-hidden', String(!open));
      submenu.inert = !open;
    };
    setSubmenu(false);
    button.addEventListener('click', () => {
      if (!isMobileNav()) return;
      const shouldOpen = button.getAttribute('aria-expanded') !== 'true';
      if (shouldOpen) closeSubmenus();
      setSubmenu(shouldOpen);
    });
    item.addEventListener('mouseenter', () => { if (!isMobileNav()) setSubmenu(true); });
    item.addEventListener('mouseleave', () => { if (!isMobileNav()) setSubmenu(false); });
    item.addEventListener('focusin', () => { if (!isMobileNav()) setSubmenu(true); });
    item.addEventListener('focusout', (event) => {
      if (!isMobileNav() && !item.contains(event.relatedTarget)) setSubmenu(false);
    });
  });
  nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', (event) => {
    const destination = new URL(link.href, window.location.href);
    const samePageAnchor = destination.origin === window.location.origin
      && destination.pathname === window.location.pathname
      && destination.hash.length > 1;
    const target = samePageAnchor ? document.getElementById(decodeURIComponent(destination.hash.slice(1))) : null;
    setNavOpen(false, false);
    if (!target) return;
    event.preventDefault();
    history.pushState(null, '', destination.hash);
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    requestAnimationFrame(() => {
      const top = target.getBoundingClientRect().top + window.scrollY - 112;
      window.scrollTo({ top: Math.max(0, top), behavior: reduceMotion ? 'auto' : 'smooth' });
    });
  }));
  document.addEventListener('pointerdown', (event) => {
    if (header.classList.contains('nav-open') && !header.contains(event.target) && !nav.contains(event.target)) setNavOpen(false, false);
  });
  document.addEventListener('keydown', (event) => {
    if (!header.classList.contains('nav-open')) return;
    if (event.key === 'Escape') {
      event.preventDefault();
      setNavOpen(false);
      return;
    }
    if (event.key !== 'Tab') return;
    const focusable = focusableInNav();
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last?.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first?.focus();
    }
  });
  window.addEventListener('resize', () => {
    if (!isMobileNav()) setNavOpen(false, false);
    placeNavForViewport();
    nav.setAttribute('aria-hidden', String(isMobileNav() && !header.classList.contains('nav-open')));
    nav.inert = isMobileNav() && !header.classList.contains('nav-open');
  }, { passive: true });
  nav.setAttribute('aria-hidden', String(isMobileNav()));
  nav.inert = isMobileNav();
  const updateHeader = () => {
    if (!header) return;
    const hero = document.querySelector('.v3-home-hero, .home-hero, .page-hero');
    const pastHero = hero ? hero.getBoundingClientRect().bottom <= 96 : window.scrollY > 80;
    header.classList.toggle('is-scrolled', pastHero);
  };
  updateHeader(); window.addEventListener('scroll', updateHeader, { passive: true });
  const reveal = new IntersectionObserver((entries, observer) => entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); } }), { threshold: .12 });
  document.querySelectorAll('[data-reveal]').forEach(el => reveal.observe(el));
  document.querySelectorAll('[data-modal-open]').forEach(trigger => trigger.addEventListener('click', () => document.querySelector(trigger.dataset.modalOpen)?.showModal()));
  document.querySelectorAll('dialog .dialog-close').forEach(close => close.addEventListener('click', () => close.closest('dialog')?.close()));
});

document.addEventListener('DOMContentLoaded', () => {
  const carousel = document.querySelector('[data-team-carousel]');
  if (!carousel) return;

  const track = carousel.querySelector('#team-ref-track');
  const cards = [...carousel.querySelectorAll('[data-team-card]')];
  const dots = [...carousel.querySelectorAll('[data-team-dot]')];
  const previous = carousel.querySelector('[data-team-prev]');
  const next = carousel.querySelector('[data-team-next]');
  const visibleDesktop = 4;
  const maximumIndex = Math.max(0, cards.length - visibleDesktop);
  let index = 0;
  let hoveredIndex = null;
  let tappedIndex = null;
  let tapTimeout;

  const render = () => {
    const canPrevious = index > 0;
    const canNext = index < maximumIndex;
    track.style.transform = `translateX(calc(-${index} * (25vw + 0.25px)))`;
    previous.disabled = !canPrevious;
    next.disabled = !canNext;
    cards.forEach((card, cardIndex) => card.classList.toggle('is-active', cardIndex === hoveredIndex || cardIndex === tappedIndex));
    dots.forEach((dot, dotIndex) => {
      const inView = dotIndex >= index && dotIndex < index + visibleDesktop;
      dot.classList.toggle('is-in-view', inView);
      dot.setAttribute('aria-current', inView ? 'true' : 'false');
    });
  };

  const setIndex = (nextIndex) => {
    index = Math.max(0, Math.min(maximumIndex, nextIndex));
    tappedIndex = null;
    window.clearTimeout(tapTimeout);
    render();
  };

  const tapCard = (cardIndex) => {
    tappedIndex = cardIndex;
    window.clearTimeout(tapTimeout);
    tapTimeout = window.setTimeout(() => {
      tappedIndex = null;
      render();
    }, 3000);
    render();
  };

  previous.addEventListener('click', () => setIndex(index - 1));
  next.addEventListener('click', () => setIndex(index + 1));
  dots.forEach((dot) => dot.addEventListener('click', () => setIndex(Number(dot.dataset.teamDot))));
  cards.forEach((card, cardIndex) => {
    card.addEventListener('mouseenter', () => { hoveredIndex = cardIndex; render(); });
    card.addEventListener('mouseleave', () => { hoveredIndex = null; render(); });
    card.addEventListener('click', () => tapCard(cardIndex));
    card.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      tapCard(cardIndex);
    });
  });
  render();
});

document.addEventListener('DOMContentLoaded', () => {
  const sectorButtons = [...document.querySelectorAll('[data-sector-index]')];
  const sectorImage = document.querySelector('#sector-image');
  const sectorNumber = document.querySelector('#sector-number');
  const sectorCaption = document.querySelector('#sector-caption');
  if (!sectorButtons.length || !sectorImage || !sectorNumber || !sectorCaption) return;
  const sectorData = document.querySelector('#sector-data');
  const sectors = sectorData ? JSON.parse(sectorData.textContent) : [];
  let active = sectorButtons.findIndex(button => button.classList.contains('is-active'));
  let paused = false;
  const renderSector = (index) => {
    active = index;
    const sector = sectors[index];
    sectorButtons.forEach((button, i) => button.classList.toggle('is-active', i === index));
    sectorImage.src = sector.image;
    sectorImage.alt = sector.title;
    sectorNumber.textContent = sector.number;
    sectorCaption.innerHTML = `<h3>${sector.title}</h3><p>${sector.description}</p><a class="text-cta" href="${sector.link}">Explore this sector →</a>`;
  };
  const sectorSection = sectorImage.closest('.sector-showcase');
  sectorSection?.addEventListener('mouseenter', () => { paused = true; });
  sectorSection?.addEventListener('mouseleave', () => { paused = false; });
  sectorSection?.addEventListener('focusin', () => { paused = true; });
  sectorSection?.addEventListener('focusout', (event) => { if (!sectorSection.contains(event.relatedTarget)) paused = false; });
  sectorButtons.forEach((button, index) => button.addEventListener('click', () => { paused = true; renderSector(index); }));
  window.setInterval(() => { if (!paused) renderSector((active + 1) % sectors.length); }, 3800);
});

document.addEventListener('DOMContentLoaded', () => {
  const footprint = document.querySelector('[data-footprint]');
  const dataNode = document.querySelector('#footprint-data');
  if (!footprint || !dataNode) return;
  if (dataNode.hasAttribute('data-v3-both-views')) return;

  const countries = JSON.parse(dataNode.textContent);
  const controls = [...footprint.querySelectorAll('[data-country]')];
  const mapPaths = [...footprint.querySelectorAll('.africa-map-svg path')];
  const detail = footprint.querySelector('.footprint-detail');
  const back = footprint.querySelector('[data-footprint-back]');
  const mapArt = footprint.querySelector('.map-art');
  const mapSvg = footprint.querySelector('.africa-map-svg');
  const hoverLabel = footprint.querySelector('.map-hover-label');
  const hoverLabelText = hoverLabel?.querySelector('span');
  const supportedCodes = Object.keys(countries);
  let activeTrigger = null;
  mapPaths.forEach((path) => {
    if (path.id && !path.dataset.id) path.dataset.id = path.id.toUpperCase();
  });

  const positionCountryPins = () => {
    if (!mapArt || !mapSvg || footprint.classList.contains('is-detail')) return;
    const artRect = mapArt.getBoundingClientRect();
    supportedCodes.forEach((code) => {
      const path = footprint.querySelector(`.africa-map-svg path[data-id="${code}"]`);
      const dot = footprint.querySelector(`.map-dot[data-country="${code}"]`);
      if (!path || !dot) return;
      const box = path.getBBox();
      const point = mapSvg.createSVGPoint();
      point.x = box.x + (box.width / 2);
      point.y = box.y + (box.height / 2);
      const matrix = path.getScreenCTM();
      if (!matrix) return;
      const screenPoint = point.matrixTransform(matrix);
      dot.style.left = `${screenPoint.x - artRect.left}px`;
      dot.style.top = `${screenPoint.y - artRect.top}px`;
    });
  };
  const schedulePinPosition = () => window.requestAnimationFrame(positionCountryPins);
  schedulePinPosition();
  if (mapArt) mapArt.addEventListener('transitionend', (event) => {
    if (event.propertyName === 'transform') schedulePinPosition();
  });
  if ('ResizeObserver' in window && mapArt) new ResizeObserver(schedulePinPosition).observe(mapArt);
  else window.addEventListener('resize', schedulePinPosition, { passive: true });

  const pathFor = (code) => footprint.querySelector(`.africa-map-svg path[data-id="${code}"]`);
  const setHover = (code, active) => {
    controls.filter((control) => control.dataset.country === code).forEach((control) => control.classList.toggle('is-hovered', active));
    pathFor(code)?.classList.toggle('is-hovered', active);
    if (hoverLabel && hoverLabelText) {
      hoverLabelText.textContent = active && countries[code] ? countries[code].name : '';
      hoverLabel.classList.toggle('is-visible', active && Boolean(countries[code]));
      hoverLabel.setAttribute('aria-hidden', String(!(active && countries[code])));
    }
  };
  const renderProgrammes = (programmes) => {
    const list = footprint.querySelector('.footprint-programmes');
    list.replaceChildren();
    if (!programmes.length) {
      const empty = document.createElement('p');
      empty.className = 'footprint-empty';
      empty.textContent = 'Programme details will be added soon.';
      list.append(empty);
      return;
    }
    programmes.forEach((programme) => {
      const name = Array.isArray(programme) ? programme[0] : programme.title;
      const partner = Array.isArray(programme) ? programme[1] : programme.partner;
      const url = Array.isArray(programme) ? '' : programme.url;
      const item = document.createElement('a');
      item.className = 'footprint-programme';
      item.href = url || '#';
      item.setAttribute('aria-label', `View ${name}`);
      const title = document.createElement('strong');
      title.textContent = name;
      const meta = document.createElement('span');
      meta.textContent = partner;
      const arrow = document.createElement('i');
      arrow.setAttribute('aria-hidden', 'true');
      arrow.textContent = '↗';
      item.append(title, meta, arrow);
      list.append(item);
    });
  };
  const openCountry = (code, trigger = null, moveFocus = false) => {
    const country = countries[code];
    if (!country) return;
    activeTrigger = trigger || activeTrigger;
    footprint.classList.add('is-detail');
    footprint.style.setProperty('--map-scale', country.scale);
    footprint.style.setProperty('--map-x', country.x);
    footprint.style.setProperty('--map-y', country.y);
    controls.forEach((control) => {
      const selected = control.dataset.country === code;
      control.classList.toggle('is-active', selected);
      control.setAttribute('aria-expanded', String(selected));
      if (selected) control.setAttribute('aria-current', 'true');
      else control.removeAttribute('aria-current');
    });
    mapPaths.forEach((path) => {
      const selected = path.dataset.id === code;
      path.classList.toggle('is-selected', selected);
      if (supportedCodes.includes(path.dataset.id)) {
        path.setAttribute('aria-expanded', String(selected));
        if (selected) path.setAttribute('aria-current', 'true');
        else path.removeAttribute('aria-current');
      }
    });
    footprint.dataset.activeCountry = code;
    detail.setAttribute('aria-hidden', 'false');
    detail.inert = false;
    detail.querySelector('.footprint-count').textContent = country.count;
    detail.querySelector('h3').textContent = country.name;
    detail.querySelector('.footprint-meta').textContent = country.meta;
    renderProgrammes(country.programmes);
    if (window.matchMedia('(max-width: 620px)').matches) {
      const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      footprint.scrollIntoView({ block: 'start', behavior: reduceMotion ? 'auto' : 'smooth' });
    }
    if (moveFocus) back.focus({ preventScroll: true });
  };
  const closeCountry = (restoreFocus = true) => {
    footprint.classList.remove('is-detail');
    detail.setAttribute('aria-hidden', 'true');
    detail.inert = true;
    controls.forEach((control) => control.classList.remove('is-active'));
    controls.forEach((control) => {
      control.removeAttribute('aria-current');
      control.setAttribute('aria-expanded', 'false');
    });
    mapPaths.forEach((path) => {
      path.classList.remove('is-selected');
      if (supportedCodes.includes(path.dataset.id)) {
        path.removeAttribute('aria-current');
        path.setAttribute('aria-expanded', 'false');
      }
    });
    delete footprint.dataset.activeCountry;
    hoverLabel?.classList.remove('is-visible');
    hoverLabel?.setAttribute('aria-hidden', 'true');
    schedulePinPosition();
    if (restoreFocus && activeTrigger?.isConnected) activeTrigger.focus({ preventScroll: true });
    activeTrigger = null;
  };

  controls.forEach((control) => {
    const code = control.dataset.country;
    control.removeAttribute('aria-current');
    control.setAttribute('aria-controls', 'footprint-detail');
    control.setAttribute('aria-expanded', 'false');
    control.addEventListener('mouseenter', () => setHover(code, true));
    control.addEventListener('mouseleave', () => setHover(code, false));
    control.addEventListener('focus', () => setHover(code, true));
    control.addEventListener('blur', () => setHover(code, false));
    control.addEventListener('click', (event) => openCountry(code, control, event.detail === 0));
  });
  mapPaths.forEach((path) => {
    const code = path.dataset.id;
    if (!supportedCodes.includes(code)) return;
    path.setAttribute('role', 'button');
    path.setAttribute('tabindex', '0');
    path.setAttribute('data-country', code);
    path.setAttribute('aria-label', `View ${countries[code].name} programmes`);
    path.setAttribute('aria-controls', 'footprint-detail');
    path.setAttribute('aria-expanded', 'false');
    path.addEventListener('mouseenter', () => setHover(code, true));
    path.addEventListener('mouseleave', () => setHover(code, false));
    path.addEventListener('focus', () => setHover(code, true));
    path.addEventListener('blur', () => setHover(code, false));
    path.addEventListener('click', (event) => openCountry(code, path, event.detail === 0));
    path.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openCountry(code, path, true);
      }
    });
  });
  back.addEventListener('click', () => closeCountry());
  footprint.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && footprint.classList.contains('is-detail')) {
      event.preventDefault();
      closeCountry();
    }
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const section = document.querySelector('.src-v3-programme-detail-hero');
  const ring = section?.querySelector('.src-v3-programmes-ring');
  if (!section || !ring) return;

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let frame = 0;
  const updateRing = () => {
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(() => {
      if (reducedMotion.matches) {
        ring.style.setProperty('--ring-parallax', '0px');
        return;
      }
      const sectionTop = section.getBoundingClientRect().top + window.scrollY;
      ring.style.setProperty('--ring-parallax', `${(window.scrollY - sectionTop) * 0.15}px`);
    });
  };

  updateRing();
  window.addEventListener('scroll', updateRing, { passive: true });
  reducedMotion.addEventListener('change', updateRing);
});

document.addEventListener('DOMContentLoaded', () => {
  const dialog = document.querySelector('#project-dialog');
  const dataNode = document.querySelector('#project-modal-data');
  const triggers = [...document.querySelectorAll('[data-project-index]')];
  if (!dialog || !dataNode || !triggers.length) return;

  const projects = JSON.parse(dataNode.textContent);
  const tabs = [...dialog.querySelectorAll('[data-project-tab]')];
  const panels = [...dialog.querySelectorAll('[data-project-panel]')];
  let activeTrigger = null;

  const activateTab = (name, focus = false) => {
    tabs.forEach((tab) => {
      const selected = tab.dataset.projectTab === name;
      tab.setAttribute('aria-selected', String(selected));
      tab.tabIndex = selected ? 0 : -1;
      if (selected && focus) tab.focus();
    });
    panels.forEach((panel) => {
      const selected = panel.dataset.projectPanel === name;
      panel.classList.toggle('is-active', selected);
      panel.hidden = !selected;
    });
  };
  const populate = (project) => {
    dialog.querySelector('.project-modal-image').src = project.image;
    dialog.querySelector('.project-modal-image').alt = project.title;
    dialog.querySelector('.project-modal-kicker').textContent = `${project.partner} · ${project.funder}`;
    dialog.querySelector('#project-modal-title').textContent = project.title;
    dialog.querySelector('.project-modal-description').textContent = project.description;
    dialog.querySelector('.project-modal-location strong').textContent = project.location;
    dialog.querySelector('[data-project-panel="overview"] h3').textContent = project.title;
    dialog.querySelector('.project-panel-location').textContent = project.location;
    dialog.querySelector('.project-panel-description').textContent = project.description;
    dialog.querySelectorAll('.project-inline-title').forEach((node) => { node.textContent = project.title; });
    const externalLink = dialog.querySelector('.project-external-link');
    if (externalLink) externalLink.href = project.externalUrl || project.url;

    const stats = dialog.querySelector('.project-stats');
    stats.replaceChildren();
    project.stats.forEach(([value, label]) => {
      const item = document.createElement('div');
      item.className = 'project-stat';
      const strong = document.createElement('strong');
      strong.textContent = value;
      const span = document.createElement('span');
      span.textContent = label;
      item.append(strong, span);
      stats.append(item);
    });

    const renderProjectEntries = (impactList, entries, linked) => {
      if (!impactList) return;
      impactList.replaceChildren();
      entries.forEach(([title, copy, url]) => {
      const item = document.createElement(linked && url ? 'a' : 'div');
      item.className = 'project-impact-item';
      if (linked && url) {
        item.href = url;
        item.setAttribute('aria-label', `View ${title}`);
      }
      const body = document.createElement('div');
      const strong = document.createElement('strong');
      strong.textContent = title;
      const paragraph = document.createElement('p');
      paragraph.textContent = copy;
      body.append(strong, paragraph);
      item.append(body);
      if (linked && url) {
        const arrow = document.createElement('span');
        arrow.className = 'project-programme-arrow';
        arrow.setAttribute('aria-hidden', 'true');
        arrow.textContent = '↗';
        item.append(arrow);
      }
      impactList.append(item);
      });
    };
    renderProjectEntries(dialog.querySelector('.project-impact-list'), project.impacts || [], false);
    renderProjectEntries(dialog.querySelector('.project-programme-links'), project.programmes || [], true);
  };
  const openProject = (trigger) => {
    const project = projects[Number(trigger.dataset.projectIndex)];
    if (!project) return;
    activeTrigger = trigger;
    populate(project);
    activateTab('overview');
    dialog.showModal();
    dialog.querySelector('.dialog-close').focus();
  };

  triggers.forEach((trigger) => trigger.addEventListener('click', () => openProject(trigger)));
  dialog.querySelector('.dialog-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
  dialog.addEventListener('close', () => activeTrigger?.focus({ preventScroll: true }));
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => activateTab(tab.dataset.projectTab));
    tab.addEventListener('keydown', (event) => {
      if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(event.key)) return;
      event.preventDefault();
      const direction = event.key === 'ArrowLeft' || event.key === 'ArrowUp' ? -1 : 1;
      const next = tabs[(index + direction + tabs.length) % tabs.length];
      activateTab(next.dataset.projectTab, true);
    });
  });
});
