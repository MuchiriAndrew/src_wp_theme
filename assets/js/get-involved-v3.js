(() => {
  const nav = document.querySelector('[data-involvement-tabs]');
  const content = document.querySelector('[data-involvement-content]');
  if (!nav || !content) return;

  const tabs = [...nav.querySelectorAll('[data-involvement-tab]')];
  const panels = [...content.querySelectorAll('[data-involvement-panel]')];
  const trackInput = content.querySelector('[data-involvement-track-input]');
  const submit = content.querySelector('[data-involvement-submit]');
  if (!tabs.length || !panels.length) return;

  const activate = (trackId, moveFocus = false) => {
    const tab = tabs.find((item) => item.dataset.involvementTab === trackId);
    const panel = panels.find((item) => item.dataset.involvementPanel === trackId);
    if (!tab || !panel) return;

    tabs.forEach((item) => {
      const active = item === tab;
      item.classList.toggle('is-active', active);
      item.setAttribute('aria-selected', active ? 'true' : 'false');
      item.tabIndex = active ? 0 : -1;
    });
    panels.forEach((item) => { item.hidden = item !== panel; });
    if (trackInput) trackInput.value = trackId;
    content.style.setProperty('--involvement-background', panel.dataset.background || '#B3262C');
    content.style.setProperty('--involvement-accent', panel.dataset.accent || '#B3262C');
    if (submit) submit.style.color = panel.dataset.accent || '#B3262C';
    if (moveFocus) tab.focus();
  };

  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => activate(tab.dataset.involvementTab));
    tab.addEventListener('keydown', (event) => {
      let nextIndex = index;
      if (event.key === 'ArrowRight') nextIndex = (index + 1) % tabs.length;
      else if (event.key === 'ArrowLeft') nextIndex = (index - 1 + tabs.length) % tabs.length;
      else if (event.key === 'Home') nextIndex = 0;
      else if (event.key === 'End') nextIndex = tabs.length - 1;
      else return;
      event.preventDefault();
      activate(tabs[nextIndex].dataset.involvementTab, true);
    });
  });

  const selected = tabs.find((tab) => tab.getAttribute('aria-selected') === 'true') || tabs[0];
  activate(selected.dataset.involvementTab);
})();
