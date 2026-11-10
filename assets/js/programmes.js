(() => {
  const nav = document.querySelector('.src-programme-filters');
  const results = document.querySelector('.src-programme-results');
  if (!nav || !results) return;

  const statusButtons = [...nav.querySelectorAll('[data-programme-status]')];
  const themeButtons = [...nav.querySelectorAll('[data-programme-theme]')];
  const signatureButton = nav.querySelector('[data-programme-signature]');
  document.querySelectorAll('[data-scroll-programmes]').forEach((button) => {
    button.addEventListener('click', () => {
      document.getElementById('current')?.scrollIntoView({ behavior: 'smooth' });
    });
  });
  const sections = [...results.querySelectorAll('[data-programme-section]')];
  const cards = [...results.querySelectorAll('[data-programme-card]')];
  const emptyState = results.querySelector('.src-programme-empty');
  const scrollRow = nav.querySelector('.src-programme-filter-inner');
  const validStatuses = new Set(['all', 'current', 'upcoming', 'past']);
  const query = new URLSearchParams(window.location.search);

  const normalizeTheme = (value) => (value || '')
    .toLocaleLowerCase()
    .replace(/&/g, '')
    .replace(/[’']/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '');

  const requestedStatus = query.get('status') || 'all';
  let selectedStatus = validStatuses.has(requestedStatus) ? requestedStatus : 'all';
  const requestedTheme = query.get('theme') || '';
  const selectedThemeButton = themeButtons.find((button) => (
    button.dataset.programmeTheme === requestedTheme
    || normalizeTheme(button.dataset.programmeThemeName) === normalizeTheme(requestedTheme)
  ));
  let selectedTheme = selectedThemeButton?.dataset.programmeTheme || '';
  let signatureOnly = false;

  const updateStatusSelection = () => {
    statusButtons.forEach((button) => {
      const active = button.dataset.programmeStatus === selectedStatus;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', String(active));
    });
  };

  const updateUrl = () => {
    const params = new URLSearchParams();
    if (selectedStatus !== 'all') params.set('status', selectedStatus);
    if (selectedTheme) {
      const themeButton = themeButtons.find((button) => button.dataset.programmeTheme === selectedTheme);
      if (themeButton?.dataset.programmeThemeName) params.set('theme', themeButton.dataset.programmeThemeName);
    }
    const search = params.toString();
    window.history.replaceState(null, '', `${window.location.pathname}${search ? `?${search}` : ''}`);
  };

  const updateScrollFades = () => {
    if (!scrollRow) return;
    const canScrollLeft = scrollRow.scrollLeft > 1;
    const canScrollRight = scrollRow.scrollLeft + scrollRow.clientWidth < scrollRow.scrollWidth - 1;
    nav.classList.toggle('has-scroll-left', canScrollLeft);
    nav.classList.toggle('has-scroll-right', canScrollRight);
  };

  const applyFilters = (writeUrl = false) => {
    let visibleCount = 0;

    cards.forEach((card) => {
      const matchesStatus = selectedStatus === 'all' || card.dataset.programmeStatus === selectedStatus;
      const matchesTheme = !selectedTheme || card.dataset.programmeTheme === selectedTheme;
      const matchesSignature = !signatureOnly || card.dataset.programmeSignature === '1';
      card.hidden = !(matchesStatus && matchesTheme && matchesSignature);
      if (!card.hidden) visibleCount += 1;
    });

    sections.forEach((section) => {
      section.hidden = !section.querySelector('[data-programme-card]:not([hidden])');
    });

    if (emptyState) emptyState.hidden = visibleCount > 0;

    themeButtons.forEach((button) => {
      const active = button.dataset.programmeTheme === selectedTheme;
      button.classList.toggle('is-active', active);
    });
    if (signatureButton) {
      signatureButton.classList.toggle('is-active', signatureOnly);
      signatureButton.setAttribute('aria-pressed', String(signatureOnly));
    }

    updateStatusSelection();
    updateScrollFades();
    if (writeUrl) updateUrl();
  };

  statusButtons.forEach((button) => {
    button.addEventListener('click', () => {
      selectedStatus = button.dataset.programmeStatus || 'all';
      applyFilters(true);
    });
  });

  themeButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const theme = button.dataset.programmeTheme || '';
      selectedTheme = selectedTheme === theme ? '' : theme;
      applyFilters(true);
    });
  });

  signatureButton?.addEventListener('click', () => {
    signatureOnly = !signatureOnly;
    applyFilters(false);
  });

  const updateDocked = () => {
    const dockTop = document.body.classList.contains('admin-bar') ? 120 : 88;
    nav.classList.toggle('sticky-subnav--docked', nav.getBoundingClientRect().top <= dockTop + 1);
  };
  window.addEventListener('scroll', () => {
    updateDocked();
  }, { passive: true });
  window.addEventListener('resize', () => {
    updateDocked();
    updateScrollFades();
  });
  scrollRow?.addEventListener('scroll', updateScrollFades, { passive: true });
  if (typeof ResizeObserver !== 'undefined' && scrollRow) {
    new ResizeObserver(updateScrollFades).observe(scrollRow);
  }

  applyFilters(false);
  updateDocked();
})();
