document.addEventListener('DOMContentLoaded', () => {
  const footprint = document.querySelector('[data-footprint]');
  const dataNode = document.querySelector('#footprint-data[data-v3-both-views]');
  if (!footprint || !dataNode) return;

  const views = JSON.parse(dataNode.textContent || '{}');
  const viewButtons = [...footprint.querySelectorAll('[data-map-view]')];
  const mapPanels = [...footprint.querySelectorAll('[data-map-panel]')];
  const legends = [...footprint.querySelectorAll('[data-legend-view]')];
  const details = footprint.querySelector('#footprint-detail');
  const back = details?.querySelector('[data-footprint-back]');
  const allControls = [...footprint.querySelectorAll('[data-country]')];
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let view = 'africa';
  let activeTrigger = null;
  let previousOverflow = '';

  const currentData = () => views[view] || {};
  const currentPanel = () => footprint.querySelector(`[data-map-panel="${view}"]`);
  const currentArt = () => currentPanel()?.classList.contains('map-art') ? currentPanel() : null;
  const currentSvg = () => currentArt()?.querySelector('svg');
  const currentPaths = () => [...(currentSvg()?.querySelectorAll('path') || [])];
  const visibleControls = () => allControls.filter((control) => control.closest('[data-legend-view]')?.dataset.legendView === view || control.closest('[data-map-panel]')?.dataset.mapPanel === view);
  const activePathCodes = () => new Set(Object.keys(currentData()));
  const pathFor = (code) => currentPaths().find((path) => (path.dataset.id || path.id || '').toUpperCase() === code);
  const schedulePinPosition = () => requestAnimationFrame(positionPins);

  currentPaths().forEach((path) => {
    if (path.id && !path.dataset.id) path.dataset.id = path.id.toUpperCase();
  });

  function positionPins() {
    const art = currentArt();
    const svg = currentSvg();
    if (!art || !svg) return;
    const artRect = art.getBoundingClientRect();
    Object.keys(currentData()).forEach((code) => {
      const path = pathFor(code);
      const dot = footprint.querySelector(`[data-map-panel="${view}"] .map-dot[data-country="${code}"]`);
      if (!path || !dot) return;
      const box = path.getBBox();
      const point = svg.createSVGPoint();
      point.x = box.x + box.width / 2;
      point.y = box.y + box.height / 2;
      const matrix = path.getScreenCTM();
      if (!matrix) return;
      const screen = point.matrixTransform(matrix);
      dot.style.left = `${screen.x - artRect.left}px`;
      dot.style.top = `${screen.y - artRect.top}px`;
    });
  }

  function setView(nextView) {
    if (!views[nextView] || nextView === view) return;
    closeDetail(false);
    mapPanels.forEach((panel) => {
      panel.style.setProperty('--map-scale', '1');
      panel.style.transformOrigin = 'center';
    });
    view = nextView;
    footprint.dataset.activeView = view;
    mapPanels.forEach((panel) => { panel.hidden = panel.dataset.mapPanel !== view; });
    legends.forEach((legend) => { legend.hidden = legend.dataset.legendView !== view; });
    viewButtons.forEach((button) => {
      const selected = button.dataset.mapView === view;
      button.setAttribute('aria-selected', String(selected));
      button.tabIndex = selected ? 0 : -1;
    });
    currentPaths().forEach((path) => { if (path.id) path.dataset.id = path.id.toUpperCase(); });
    bindPaths();
    schedulePinPosition();
  }

  function setHover(code, active) {
    allControls.filter((control) => control.dataset.country === code).forEach((control) => control.classList.toggle('is-hovered', active));
    pathFor(code)?.classList.toggle('is-hovered', active);
    const label = currentArt()?.querySelector('.map-hover-label');
    const text = label?.querySelector('span');
    if (label && text) {
      text.textContent = active && currentData()[code] ? currentData()[code].name : '';
      label.classList.toggle('is-visible', active && Boolean(currentData()[code]));
      label.setAttribute('aria-hidden', String(!(active && currentData()[code])));
      const dot = currentArt()?.querySelector(`.map-dot[data-country="${code}"]`);
      if (dot) {
        label.style.left = dot.style.left;
        label.style.top = dot.style.top;
      }
    }
  }

  function renderProgrammes(programmes) {
    const list = details?.querySelector('.footprint-programmes');
    if (!list) return;
    list.replaceChildren();
    if (!programmes?.length) {
      const empty = document.createElement('p');
      empty.className = 'footprint-empty';
      empty.textContent = 'Programme details will be added soon.';
      list.append(empty);
      return;
    }
    programmes.forEach((programme) => {
      const card = document.createElement('a');
      card.className = 'footprint-programme-card';
      card.href = programme.url || '#';
      card.setAttribute('aria-label', `View ${programme.title}`);
      const image = document.createElement('img');
      image.src = programme.image || `${window.location.origin}/wp-content/themes/smartregionalconsultants/assets/hero-man-with-camels-poster.png`;
      image.alt = '';
      image.loading = 'lazy';
      const copy = document.createElement('span');
      copy.className = 'footprint-programme-copy';
      const status = document.createElement('span');
      status.className = `footprint-programme-status is-${programme.status || 'past'}`;
      status.textContent = (programme.status || 'past').replaceAll('-', ' ');
      const title = document.createElement('strong');
      title.textContent = programme.title;
      const partner = document.createElement('span');
      partner.className = 'footprint-programme-partner';
      partner.textContent = programme.partner || 'SRC';
      const dates = document.createElement('span');
      dates.className = 'footprint-programme-dates';
      dates.textContent = programme.dates || '';
      const action = document.createElement('span');
      action.className = 'footprint-programme-action';
      action.textContent = 'View programme →';
      copy.append(status, title, partner, dates, action);
      card.append(image, copy);
      list.append(card);
    });
  }

  function openCountry(code, trigger, moveFocus = false) {
    const country = currentData()[code];
    if (!country || !details) return;
    activeTrigger = trigger || activeTrigger;
    footprint.classList.add('is-detail');
    footprint.dataset.activeCountry = code;
    allControls.filter((control) => control.dataset.country === code).forEach((control) => {
      control.classList.add('is-active');
      control.setAttribute('aria-expanded', 'true');
      control.setAttribute('aria-current', 'true');
    });
    const selectedPath = pathFor(code);
    selectedPath?.classList.add('is-selected');
    selectedPath?.setAttribute('aria-expanded', 'true');
    selectedPath?.setAttribute('aria-current', 'true');

    details.querySelector('.footprint-count').textContent = view === 'africa' ? 'Country' : 'County';
    details.querySelector('h3').textContent = country.name || 'SRC programmes';
    details.querySelector('.footprint-meta').textContent = country.meta || '';
    const hero = details.querySelector('.footprint-dialog-image');
    hero.src = 'https://images.unsplash.com/photo-1593114638431-5805138c696e?w=1200&h=400&fit=crop&auto=format&q=82';
    hero.alt = '';
    const art = currentArt();
    const pathRect = selectedPath?.getBoundingClientRect();
    const artRect = art?.getBoundingClientRect();
    if (art && pathRect && artRect && pathRect.width && pathRect.height) {
      const maxScale = view === 'kenya' && ['COUNTY-NAIROBI', 'COUNTY-MOMBASA'].includes(code) ? 8 : 5.5;
      const scale = Math.max(1, Math.min((artRect.width * 0.48) / pathRect.width, (artRect.height * 0.48) / pathRect.height, maxScale));
      art.style.setProperty('--map-scale', String(scale));
      art.style.transformOrigin = `${pathRect.left + pathRect.width / 2 - artRect.left}px ${pathRect.top + pathRect.height / 2 - artRect.top}px`;
    }
    renderProgrammes(country.programmes || []);
    details.setAttribute('aria-label', `${country.name || 'Country'} programmes`);
    details.setAttribute('aria-hidden', 'false');
    details.inert = false;
    previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    document.body.classList.add('src-footprint-open');
    if (moveFocus) back?.focus({ preventScroll: true });
  }

  function closeDetail(restoreFocus = true) {
    if (!details || details.getAttribute('aria-hidden') === 'true') return;
    details.setAttribute('aria-hidden', 'true');
    details.inert = true;
    footprint.classList.remove('is-detail');
    currentArt()?.style.setProperty('--map-scale', '1');
    if (currentArt()) currentArt().style.transformOrigin = 'center';
    delete footprint.dataset.activeCountry;
    allControls.forEach((control) => {
      control.classList.remove('is-active');
      control.removeAttribute('aria-current');
      control.setAttribute('aria-expanded', 'false');
    });
    currentPaths().forEach((path) => {
      path.classList.remove('is-selected');
      if (path.getAttribute('role') === 'button') {
        path.setAttribute('aria-expanded', 'false');
        path.removeAttribute('aria-current');
      }
    });
    document.body.style.overflow = previousOverflow;
    document.body.classList.remove('src-footprint-open');
    if (restoreFocus && activeTrigger?.isConnected) activeTrigger.focus({ preventScroll: true });
    activeTrigger = null;
  }

  function bindPaths() {
    const codes = activePathCodes();
    currentPaths().forEach((path) => {
      const code = (path.dataset.id || path.id || '').toUpperCase();
      path.dataset.id = code;
      if (!codes.has(code)) return;
      if (path.dataset.footprintBound === 'true') return;
      path.dataset.footprintBound = 'true';
      path.setAttribute('role', 'button');
      path.setAttribute('tabindex', '0');
      path.setAttribute('aria-label', `View ${currentData()[code].name} programmes`);
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
    visibleControls().forEach((control) => {
      const code = control.dataset.country;
      if (!codes.has(code)) return;
      if (control.dataset.footprintBound === 'true') return;
      control.dataset.footprintBound = 'true';
      control.setAttribute('aria-controls', 'footprint-detail');
      control.setAttribute('aria-expanded', 'false');
      control.addEventListener('mouseenter', () => setHover(code, true));
      control.addEventListener('mouseleave', () => setHover(code, false));
      control.addEventListener('focus', () => setHover(code, true));
      control.addEventListener('blur', () => setHover(code, false));
      control.addEventListener('click', (event) => openCountry(code, control, event.detail === 0));
    });
  }

  viewButtons.forEach((button) => button.addEventListener('click', () => setView(button.dataset.mapView)));
  back?.addEventListener('click', () => closeDetail());
  details?.addEventListener('click', (event) => { if (event.target === details) closeDetail(false); });
  footprint.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && footprint.classList.contains('is-detail')) {
      event.preventDefault();
      closeDetail();
      return;
    }
    if (event.key !== 'Tab' || !details || details.getAttribute('aria-hidden') !== 'false') return;
    const focusable = [...details.querySelectorAll('a[href], button:not([disabled])')];
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  });
  window.addEventListener('resize', schedulePinPosition, { passive: true });
  currentPaths().forEach((path) => { if (path.id) path.dataset.id = path.id.toUpperCase(); });
  bindPaths();
  schedulePinPosition();
});
