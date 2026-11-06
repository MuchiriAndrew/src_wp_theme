document.addEventListener('DOMContentLoaded', () => {
  const dialog = document.querySelector('#project-folder');
  const dataNode = document.querySelector('#project-modal-data');
  const triggers = [...document.querySelectorAll('[data-project-index]')];
  const area = document.querySelector('#portfolio .projects-stage');
  if (!dialog || !dataNode || !triggers.length || !area) return;

  const projects = JSON.parse(dataNode.textContent || '[]');
  const index = dialog.querySelector('.project-index');
  const detail = dialog.querySelector('.programme-folder-layer');
  const detailStage = dialog.querySelector('.project-stage');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let activeTrigger = null;
  let activeProject = null;
  let closeTimer = 0;
  let folderTimer = 0;
  let touchStartY = null;
  let pageScrollBeforeOpen = 0;
  let originBounds = null;
  let activeFolderIndex = null;

  const fill = (node, value) => { if (node) node.textContent = value || ''; };
  const folderHeight = () => Math.max(720, Math.min(window.innerHeight, 900));

  function setBounds(bounds) {
    if (!bounds) return;
    dialog.style.left = `${bounds.left}px`;
    dialog.style.top = `${bounds.top}px`;
    dialog.style.width = `${bounds.width}px`;
    dialog.style.height = `${bounds.height}px`;
  }

  function restorePageScroll() {
    const root = document.documentElement;
    const body = document.body;
    const rootBehavior = root.style.scrollBehavior;
    const bodyBehavior = body.style.scrollBehavior;
    root.style.scrollBehavior = 'auto';
    body.style.scrollBehavior = 'auto';
    window.scrollTo(0, pageScrollBeforeOpen);
    requestAnimationFrame(() => {
      root.style.scrollBehavior = rootBehavior;
      body.style.scrollBehavior = bodyBehavior;
    });
  }

  function getOpenBounds() {
    const rect = area.getBoundingClientRect();
    return {
      left: rect.left + window.scrollX + rect.width / 2 - window.innerWidth / 2,
      top: rect.top + window.scrollY,
      width: window.innerWidth,
      height: folderHeight(),
    };
  }

  function alignFolderHorizontally() {
    if (!dialog.open || window.innerWidth < 1024 || dialog.classList.contains('project-folder--closing')) return;
    const bounds = getOpenBounds();
    dialog.style.left = `${bounds.left}px`;
  }

  function updateDividerButtons(selectedIndex) {
    [...index.querySelectorAll('[data-folder-index]')].forEach((button) => {
      const position = Number(button.dataset.folderIndex);
      const selected = position === selectedIndex;
      const programme = activeProject.folders[position];
      button.classList.toggle('project-divider--active', selected);
      button.setAttribute('aria-pressed', String(selected));
      button.setAttribute('aria-label', `${selected ? 'Close' : 'Open'} ${programme.title}`);
    });
  }

  function renderProgramme(programme, position) {
    activeFolderIndex = position;
    updateDividerButtons(position);
    detail.style.setProperty('--programme-color', ['#8E1B21', '#A8232B', '#C8303A'][position % 3]);
    detail.style.setProperty('--folder-image', `url("${activeProject.image}")`);
    fill(detail.querySelector('.programme-folder-eyebrow'), `${activeProject.title} · Programme`);
    fill(detail.querySelector('.programme-folder-copy h3'), programme.title);
    fill(
      detail.querySelector('.programme-status-chip'),
      `${(programme.status || 'past').toUpperCase()} · ${(programme.fee || 'Free / not stated').replace(/[\[\]]/g, '')}`,
    );
    fill(detail.querySelector('.programme-folder-left > p'), programme.description || programme.outcomes);

    const facts = detail.querySelector('.programme-facts');
    facts.replaceChildren();
    const iconMarkup = [
      '<svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="7"></circle><path d="M10 6v4l3 2"></path></svg>',
      '<svg viewBox="0 0 20 20" aria-hidden="true"><rect x="3" y="5" width="14" height="12" rx="1"></rect><path d="M6 3v4M14 3v4M3 9h14"></path></svg>',
      '<svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="7" r="3"></circle><path d="M4 17c.6-3.1 2.6-5 6-5s5.4 1.9 6 5"></path></svg>',
    ];
    [['Duration', programme.duration], ['Dates', programme.dates], ['Reach', programme.reach]].forEach(([label, value], factIndex) => {
      const row = document.createElement('div');
      row.innerHTML = '<span></span><strong></strong>';
      row.querySelector('span').textContent = label;
      row.querySelector('strong').textContent = value || 'Details coming soon';
      const icon = document.createElement('span');
      icon.className = 'programme-fact-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.innerHTML = iconMarkup[factIndex];
      row.prepend(icon);
      facts.append(row);
    });

    const target = programme.slug === 'road-to-leadership'
      ? `${window.location.origin}/programmes/?status=upcoming`
      : (programme.url || '#');
    const primary = detail.querySelector('.programme-action-primary');
    const secondary = detail.querySelector('.programme-action-secondary');
    if (primary) {
      primary.href = target;
      fill(primary, `${programme.slug === 'road-to-markets' ? 'Register interest' : 'View programme'} →`);
    }
    if (secondary) {
      secondary.href = target;
      fill(secondary, 'Learn more →');
    }
  }

  function animateProgrammeOpen() {
    detail.classList.remove('programme-folder-layer--closing');
    detail.classList.add('programme-folder-layer--opening');
    requestAnimationFrame(() => requestAnimationFrame(() => {
      detail.classList.remove('programme-folder-layer--opening');
      detail.classList.add('programme-folder-layer--open');
    }));
  }

  function showProgramme(programme, position) {
    if (!programme || !detail) return;
    if (!detail.hidden && activeFolderIndex === position) {
      closeProgrammeFolder();
      return;
    }

    if (!detail.hidden) {
      window.clearTimeout(folderTimer);
      detail.classList.remove('programme-folder-layer--open', 'programme-folder-layer--opening');
      detail.classList.add('programme-folder-layer--closing');
      folderTimer = window.setTimeout(() => {
        renderProgramme(programme, position);
        animateProgrammeOpen();
      }, reduceMotion ? 1 : 400);
      return;
    }

    window.clearTimeout(folderTimer);
    detail.hidden = false;
    detailStage?.classList.add('project-stage--folder-open');
    if (window.innerWidth >= 1024 && !dialog.classList.contains('project-folder--programme-open')) {
      dialog.classList.add('project-folder--programme-open');
      const bounds = getOpenBounds();
      window.scrollTo({ top: bounds.top, behavior: reduceMotion ? 'auto' : 'smooth' });
    }
    renderProgramme(programme, position);
    detail.classList.remove('programme-folder-layer--open', 'programme-folder-layer--closing');
    animateProgrammeOpen();
  }

  function renderProject(project) {
    activeProject = project;
    activeFolderIndex = null;
    dialog.classList.remove('project-folder--programme-open');
    dialog.style.setProperty('--folder-image', `url("${project.image}")`);
    const photo = dialog.querySelector('.project-folder-photo');
    photo.style.backgroundImage = `url("${project.image}")`;
    fill(dialog.querySelector('#project-folder-title'), project.title);
    fill(dialog.querySelector('.project-profile-description'), project.description);
    fill(dialog.querySelector('.project-result-chip'), project.result || `${project.folders.length} programmes`);
    fill(dialog.querySelector('.project-funder'), project.funder);
    fill(dialog.querySelector('[data-project-action="learn"] .project-tab-label'), `See more about ${project.shortName || project.title}`);
    const firstUrl = project.folders[0]?.url || project.url || '#';
    dialog.querySelector('[data-project-action="learn"]').href = firstUrl;
    index.replaceChildren();
    project.folders.forEach((programme, i) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'project-divider';
      button.dataset.folderIndex = String(i);
      button.style.setProperty('--divider-color', ['#8E1B21', '#A8232B', '#C8303A'][i % 3]);
      button.style.setProperty('--divider-z', String(30 - i));
      button.style.setProperty('--divider-delay', `${i * 65}ms`);
      button.setAttribute('aria-label', `Open ${programme.title}`);
      button.setAttribute('aria-pressed', 'false');
      const ear = document.createElement('span');
      ear.className = 'project-divider-ear';
      const label = document.createElement('span');
      label.textContent = programme.title;
      ear.append(label);
      button.append(ear);
      button.addEventListener('click', () => showProgramme(programme, i));
      index.append(button);
    });
    detail.hidden = true;
    detail.classList.remove('programme-folder-layer--open', 'programme-folder-layer--opening', 'programme-folder-layer--closing');
    detailStage?.classList.remove('project-stage--folder-open');
  }

  function openProject(trigger) {
    const project = projects[Number(trigger.dataset.projectIndex)];
    if (!project || dialog.open) return;
    activeTrigger = trigger;
    window.clearTimeout(closeTimer);
    window.clearTimeout(folderTimer);
    pageScrollBeforeOpen = window.scrollY;
    const tileRect = trigger.getBoundingClientRect();
    originBounds = {
      left: tileRect.left + window.scrollX,
      top: tileRect.top + window.scrollY,
      width: tileRect.width,
      height: tileRect.height,
    };
    renderProject(project);
    triggers.forEach((tile) => tile.classList.add('project-tile--dimmed'));
    area.classList.add('projects-stage--open');

    if (window.innerWidth >= 1024) setBounds(originBounds);
    dialog.classList.remove('project-folder--open', 'project-folder--opening', 'project-folder--closing', 'project-folder--programme-open');
    dialog.classList.add('project-folder--primed');
    dialog.show();

    if (window.innerWidth >= 768) {
      const target = getOpenBounds();
      const navHeight = window.innerWidth >= 1024 ? 80 : 64;
      window.scrollTo({ top: target.top - navHeight, behavior: reduceMotion ? 'auto' : 'smooth' });
    }

    requestAnimationFrame(() => requestAnimationFrame(() => {
      if (window.innerWidth >= 1024) setBounds(getOpenBounds());
      dialog.classList.remove('project-folder--primed');
      dialog.classList.add('project-folder--opening');
    }));
    window.clearTimeout(closeTimer);
    closeTimer = window.setTimeout(() => {
      if (!dialog.open || dialog.classList.contains('project-folder--closing')) return;
      dialog.classList.remove('project-folder--opening');
      dialog.classList.add('project-folder--open');
    }, reduceMotion ? 1 : 720);
  }

  function closeProject() {
    if (!dialog.open || dialog.classList.contains('project-folder--closing')) return;
    window.clearTimeout(folderTimer);
    window.clearTimeout(closeTimer);
    detail.hidden = true;
    detailStage?.classList.remove('project-stage--folder-open');
    detail.classList.remove('programme-folder-layer--open', 'programme-folder-layer--opening', 'programme-folder-layer--closing');
    if (window.innerWidth >= 1024 && originBounds) setBounds(originBounds);
    dialog.classList.remove('project-folder--open', 'project-folder--opening', 'project-folder--primed', 'project-folder--programme-open');
    dialog.classList.add('project-folder--closing');
    if (window.innerWidth >= 768) {
      restorePageScroll();
    }
    window.clearTimeout(closeTimer);
    closeTimer = window.setTimeout(() => {
      dialog.close();
      dialog.classList.remove('project-folder--closing');
    }, reduceMotion ? 1 : 720);
  }

  function closeProgrammeFolder() {
    if (!detail || detail.hidden || detail.classList.contains('programme-folder-layer--closing')) return;
    window.clearTimeout(folderTimer);
    const activeFolderButton = index.querySelector('.project-divider--active');
    detail.classList.remove('programme-folder-layer--open', 'programme-folder-layer--opening');
    detail.classList.add('programme-folder-layer--closing');
    window.clearTimeout(folderTimer);
    folderTimer = window.setTimeout(() => {
      detail.hidden = true;
      detail.classList.remove('programme-folder-layer--closing');
      detailStage?.classList.remove('project-stage--folder-open');
      activeFolderIndex = null;
      updateDividerButtons(-1);
      if (window.innerWidth >= 1024) {
        restorePageScroll();
      }
      activeFolderButton?.focus({ preventScroll: true });
    }, reduceMotion ? 1 : 650);
  }

  function handleEscape(event) {
    if (event.key !== 'Escape' || !dialog.open) return;
    event.preventDefault();
    event.stopPropagation();
    closeProject();
  }

  triggers.forEach((trigger) => trigger.addEventListener('click', () => openProject(trigger)));
  dialog.querySelector('[data-project-close]')?.addEventListener('click', closeProject);
  dialog.querySelector('.programme-folder-close')?.addEventListener('click', closeProgrammeFolder);
  dialog.addEventListener('click', (event) => { if (event.target === dialog) closeProject(); });
  window.addEventListener('resize', alignFolderHorizontally);
  dialog.addEventListener('cancel', (event) => {
    event.preventDefault();
    closeProject();
  });
  document.addEventListener('keydown', handleEscape);
  dialog.addEventListener('close', () => {
    window.clearTimeout(closeTimer);
    window.clearTimeout(folderTimer);
    detail.hidden = true;
    detailStage?.classList.remove('project-stage--folder-open');
    area.classList.remove('projects-stage--open');
    triggers.forEach((tile) => tile.classList.remove('project-tile--dimmed'));
    dialog.classList.remove('project-folder--open', 'project-folder--opening', 'project-folder--primed', 'project-folder--closing', 'project-folder--programme-open');
    ['left', 'top', 'width', 'height'].forEach((property) => dialog.style.removeProperty(property));
    const triggerToRestore = activeTrigger;
    activeTrigger = null;
    activeProject = null;
    originBounds = null;
    activeFolderIndex = null;
    requestAnimationFrame(() => {
      if (triggerToRestore?.isConnected) triggerToRestore.focus({ preventScroll: true });
    });
  });
  dialog.addEventListener('touchstart', (event) => { touchStartY = event.touches[0]?.clientY ?? null; }, { passive: true });
  dialog.addEventListener('touchend', (event) => {
    const end = event.changedTouches[0]?.clientY;
    if (touchStartY !== null && end !== undefined && end - touchStartY > 90) closeProject();
    touchStartY = null;
  }, { passive: true });
});
