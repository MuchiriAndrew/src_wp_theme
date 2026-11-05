document.addEventListener('DOMContentLoaded', () => {
  const page = document.querySelector('[data-what-we-do]');
  if (!page) return;

  const section = page.querySelector('[data-pillar-section]');
  const stage = page.querySelector('[data-pillar-stage]');
  const desktopPanels = [...page.querySelectorAll('[data-pillar-panel]')];
  const mobileCards = [...page.querySelectorAll('[data-pillar-mobile-card]')];
  const cabinets = [...page.querySelectorAll('[data-pillar-cabinet]')];
  let returnFocus = null;

  const setDesktopActive = (key) => desktopPanels.forEach((panel) => {
    const active = panel.dataset.pillarPanel === key;
    panel.classList.toggle('is-active', active);
    panel.setAttribute('aria-expanded', String(active));
  });

  const buildMobileDetails = (card) => {
    const details = document.createElement('div');
    const tagline = document.createElement('em');
    tagline.textContent = `“${card.dataset.tagline || ''}”`;
    details.append(tagline);

    const description = document.createElement('p');
    description.textContent = card.dataset.description || '';
    details.append(description);

    const audience = document.createElement('div');
    audience.className = 'pillar-audience';
    const audienceLabel = document.createElement('span');
    audienceLabel.textContent = "Who it's for";
    const audienceValue = document.createElement('strong');
    audienceValue.textContent = card.dataset.audience || '';
    audience.append(audienceLabel, audienceValue);

    const explore = document.createElement('button');
    explore.type = 'button';
    explore.className = 'pillar-learn';
    explore.dataset.pillarOpen = card.dataset.pillarMobileCard;
    explore.innerHTML = 'Explore programmes <span aria-hidden="true">→</span>';
    details.append(audience, explore);
    return details;
  };

  const setMobileActive = (key) => mobileCards.forEach((card) => {
    const active = card.dataset.pillarMobileCard === key;
    card.classList.toggle('is-active', active);
    const trigger = card.querySelector('[data-pillar-mobile-trigger]');
    trigger?.setAttribute('aria-expanded', String(active));
    card.querySelector(':scope > div')?.remove();
    if (active) card.append(buildMobileDetails(card));
  });

  const openCabinet = (key, trigger) => {
    const cabinet = cabinets.find((item) => item.dataset.pillarCabinet === key);
    if (!cabinet || !stage) return;
    returnFocus = trigger || document.activeElement;
    desktopPanels.forEach((panel) => { panel.closest('.pillar-accordion-desktop').hidden = true; });
    page.querySelector('.pillar-accordion-mobile').hidden = true;
    stage.classList.add('pillar-stage--cabinet');
    cabinet.hidden = false;
    const scrollBehavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
    requestAnimationFrame(() => {
      section.scrollIntoView({ behavior: scrollBehavior, block: 'start' });
      cabinet.querySelector('[data-pillar-close]')?.focus({ preventScroll: true });
    });
  };

  const closeCabinet = (cabinet) => {
    if (!cabinet) return;
    cabinet.hidden = true;
    stage?.classList.remove('pillar-stage--cabinet');
    const desktop = page.querySelector('.pillar-accordion-desktop');
    const mobile = page.querySelector('.pillar-accordion-mobile');
    if (desktop) desktop.hidden = false;
    if (mobile) mobile.hidden = false;
    returnFocus?.focus?.({ preventScroll: true });
    returnFocus = null;
  };

  const activateTab = (cabinet, key) => {
    cabinet.querySelectorAll('[data-pillar-tab]').forEach((tab) => {
      const active = tab.dataset.pillarTab === key;
      tab.classList.toggle('is-active', active);
      tab.setAttribute('aria-pressed', String(active));
    });
    cabinet.querySelectorAll('[data-pillar-content]').forEach((content) => {
      content.hidden = content.dataset.pillarContent !== key;
    });
  };

  desktopPanels.forEach((panel) => {
    panel.addEventListener('click', (event) => {
      if (event.target.closest('[data-pillar-open]')) return;
      setDesktopActive(panel.dataset.pillarPanel);
    });
    panel.addEventListener('keydown', (event) => {
      if (event.target !== panel || !['Enter', ' '].includes(event.key)) return;
      event.preventDefault();
      setDesktopActive(panel.dataset.pillarPanel);
    });
  });

  mobileCards.forEach((card) => {
    card.dataset.tagline = card.querySelector('em')?.textContent.replace(/[“”]/g, '') || card.dataset.tagline || '';
    card.dataset.description = card.querySelector(':scope > div > p')?.textContent || '';
    card.dataset.audience = card.querySelector('.pillar-audience strong')?.textContent || '';
    card.querySelector('[data-pillar-mobile-trigger]')?.addEventListener('click', () => setMobileActive(card.dataset.pillarMobileCard));
  });

  page.addEventListener('click', (event) => {
    const open = event.target.closest('[data-pillar-open]');
    if (open) {
      event.preventDefault();
      event.stopPropagation();
      openCabinet(open.dataset.pillarOpen, open);
      return;
    }
    const close = event.target.closest('[data-pillar-close]');
    if (close) { closeCabinet(close.closest('[data-pillar-cabinet]')); return; }
    const tab = event.target.closest('[data-pillar-tab]');
    if (tab) { activateTab(tab.closest('[data-pillar-cabinet]'), tab.dataset.pillarTab); return; }
    const select = event.target.closest('[data-pillar-select-programme]');
    if (select) activateTab(select.closest('[data-pillar-cabinet]'), select.dataset.pillarSelectProgramme);
  });

  page.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const cabinet = cabinets.find((item) => !item.hidden);
    if (cabinet) closeCabinet(cabinet);
  });
});
