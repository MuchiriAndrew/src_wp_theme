(() => {
  const nav = document.querySelector('[data-involvement-tabs]');
  const content = document.querySelector('[data-involvement-content]');
  if (!nav || !content) return;

  const tabs = [...nav.querySelectorAll('[data-involvement-tab]')];
  const panels = [...content.querySelectorAll('[data-involvement-panel]')];
  const trackInput = content.querySelector('[data-involvement-track-input]');
  const submit = content.querySelector('[data-involvement-submit]');
  if (!tabs.length || !panels.length) return;

  const activate = (trackId) => {
    const tab = tabs.find((item) => item.dataset.involvementTab === trackId);
    const panel = panels.find((item) => item.dataset.involvementPanel === trackId);
    if (!tab || !panel) return;

    tabs.forEach((item) => {
      const active = item === tab;
      item.classList.toggle('is-active', active);
    });
    panels.forEach((item) => { item.hidden = item !== panel; });
    if (trackInput) trackInput.value = trackId;
    content.style.setProperty('--involvement-background', panel.dataset.background || '#B3262C');
    content.style.setProperty('--involvement-accent', panel.dataset.accent || '#B3262C');
    if (submit) submit.style.color = panel.dataset.accent || '#B3262C';
  };

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => activate(tab.dataset.involvementTab));
  });

  const selected = tabs.find((tab) => tab.classList.contains('is-active')) || tabs[0];
  activate(selected.dataset.involvementTab);
})();
