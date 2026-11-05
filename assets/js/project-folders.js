document.addEventListener('DOMContentLoaded', () => {
  const dialog = document.querySelector('#project-folder');
  const dataNode = document.querySelector('#project-modal-data');
  const triggers = [...document.querySelectorAll('[data-project-index]')];
  if (!dialog || !dataNode || !triggers.length) return;

  const projects = JSON.parse(dataNode.textContent || '[]');
  const index = dialog.querySelector('.project-index');
  const detail = dialog.querySelector('.programme-folder-layer');
  const stage = dialog.querySelector('.project-stage');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let activeTrigger = null;
  let activeProject = null;
  let closeTimer = 0;
  let touchStartY = null;

  const fill = (node, value) => { if (node) node.textContent = value || ''; };

  function showProgramme(programme, position) {
    if (!programme || !detail) return;
    const dividerButtons = [...index.querySelectorAll('[data-folder-index]')];
    dividerButtons.forEach((button) => {
      const selected = Number(button.dataset.folderIndex) === position;
      button.classList.toggle('project-divider--active', selected);
      button.setAttribute('aria-pressed', String(selected));
    });
    detail.hidden = false;
    stage?.classList.add('project-stage--folder-open');
    detail.style.setProperty('--programme-color', ['#8E1B21', '#A8232B', '#C8303A'][position % 3]);
    detail.style.setProperty('--folder-image', `url("${activeProject.image}")`);
    detail.classList.remove('programme-folder-layer--open');
    requestAnimationFrame(() => requestAnimationFrame(() => detail.classList.add('programme-folder-layer--open')));
    fill(detail.querySelector('.programme-folder-eyebrow'), `${activeProject.title} · Programme`);
    fill(detail.querySelector('.programme-folder-copy h3'), programme.title);
    const status = detail.querySelector('.programme-status-chip');
    fill(status, `${(programme.status || 'past').toUpperCase()} · ${(programme.fee || 'Free / not stated').replace(/[\[\]]/g, '')}`);
    fill(detail.querySelector('.programme-folder-left > p'), programme.description || programme.outcomes);
    const facts = detail.querySelector('.programme-facts');
    facts.replaceChildren();
    [['Duration', programme.duration], ['Dates', programme.dates], ['Reach', programme.reach]].forEach(([label, value], factIndex) => {
      const row = document.createElement('div');
      row.innerHTML = `<span>${label}</span><strong></strong>`;
      row.querySelector('strong').textContent = value || 'Details coming soon';
      const icon = document.createElement('span');
      icon.className = 'programme-fact-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.textContent = ['◷', '▦', '◎'][factIndex];
      row.prepend(icon);
      facts.append(row);
    });
    detail.querySelectorAll('.programme-actions a').forEach((link) => { link.href = programme.url || '#'; });
    detail.querySelector('.programme-folder-close')?.focus({ preventScroll: true });
  }

  function renderProject(project) {
    activeProject = project;
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
    stage?.classList.remove('project-stage--folder-open');
    detail.classList.remove('programme-folder-layer--open');
    dialog.classList.remove('project-folder--open');
    dialog.classList.add('project-folder--opening');
  }

  function openProject(trigger) {
    const project = projects[Number(trigger.dataset.projectIndex)];
    if (!project || dialog.open) return;
    activeTrigger = trigger;
    renderProject(project);
    const stage = document.querySelector('#portfolio .projects-stage');
    if (window.innerWidth >= 768) {
      const rect = stage?.getBoundingClientRect();
      if (rect) window.scrollTo({ top: window.scrollY + rect.top - (window.innerWidth >= 1024 ? 80 : 64), behavior: reduceMotion ? 'auto' : 'smooth' });
    }
    dialog.show();
    requestAnimationFrame(() => {
      dialog.classList.remove('project-folder--opening');
      dialog.classList.add('project-folder--open');
    });
    dialog.querySelector('[data-project-close]')?.focus({ preventScroll: true });
  }

  function closeProject() {
    if (!dialog.open) return;
    detail.hidden = true;
    stage?.classList.remove('project-stage--folder-open');
    dialog.classList.remove('project-folder--open');
    dialog.classList.add('project-folder--closing');
    window.clearTimeout(closeTimer);
    closeTimer = window.setTimeout(() => {
      dialog.close();
      dialog.classList.remove('project-folder--closing');
    }, reduceMotion ? 1 : 700);
  }

  function closeProgrammeFolder() {
    if (!detail || detail.hidden) return;
    const activeFolderButton = index.querySelector('.project-divider--active');
    detail.classList.remove('programme-folder-layer--open');
    stage?.classList.remove('project-stage--folder-open');
    window.setTimeout(() => {
      detail.hidden = true;
      activeFolderButton?.focus({ preventScroll: true });
    }, reduceMotion ? 1 : 320);
    index.querySelectorAll('.project-divider').forEach((button) => {
      button.classList.remove('project-divider--active');
      button.setAttribute('aria-pressed', 'false');
    });
  }

  function handleEscape(event) {
    if (event.key !== 'Escape' || !dialog.open) return;
    event.preventDefault();
    event.stopPropagation();
    if (detail && !detail.hidden) closeProgrammeFolder();
    else closeProject();
  }

  triggers.forEach((trigger) => trigger.addEventListener('click', () => openProject(trigger)));
  dialog.querySelector('[data-project-close]')?.addEventListener('click', closeProject);
  dialog.querySelector('.programme-folder-close')?.addEventListener('click', closeProgrammeFolder);
  dialog.addEventListener('click', (event) => { if (event.target === dialog) closeProject(); });
  dialog.addEventListener('cancel', (event) => {
    event.preventDefault();
    if (detail && !detail.hidden) closeProgrammeFolder();
    else closeProject();
  });
  document.addEventListener('keydown', handleEscape);
  dialog.addEventListener('close', () => {
    window.clearTimeout(closeTimer);
    detail.hidden = true;
    stage?.classList.remove('project-stage--folder-open');
    dialog.classList.remove('project-folder--open', 'project-folder--opening', 'project-folder--closing');
    const triggerToRestore = activeTrigger;
    activeTrigger = null;
    activeProject = null;
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
