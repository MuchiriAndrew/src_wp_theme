(() => {
  const cards = [...document.querySelectorAll('[data-career-card]')];
  if (!cards.length) return;

  const setOpen = (card, open) => {
    const button = card.querySelector('[data-career-toggle]');
    const details = card.querySelector('[data-career-details]');
    if (!button || !details) return;
    details.hidden = !open;
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
    button.textContent = open ? button.dataset.closeLabel : button.dataset.applyLabel;
  };

  cards.forEach((card) => {
    const button = card.querySelector('[data-career-toggle]');
    if (!button) return;
    button.addEventListener('click', () => {
      const wasOpen = button.getAttribute('aria-expanded') === 'true';
      cards.forEach((other) => setOpen(other, false));
      if (!wasOpen) setOpen(card, true);
    });
  });

  document.querySelectorAll('[data-cv-dropzone]').forEach((zone) => {
    const input = zone.querySelector('input[type="file"]');
    const title = zone.querySelector('[data-cv-title]');
    const defaultTitle = title?.textContent || '';
    if (!input || !title) return;

    const showFile = (file) => {
      title.textContent = file ? file.name : defaultTitle;
      zone.classList.toggle('has-file', Boolean(file));
    };
    input.addEventListener('change', () => showFile(input.files?.[0]));
    ['dragenter', 'dragover'].forEach((eventName) => zone.addEventListener(eventName, (event) => {
      event.preventDefault();
      zone.classList.add('is-dragging');
    }));
    ['dragleave', 'drop'].forEach((eventName) => zone.addEventListener(eventName, (event) => {
      event.preventDefault();
      zone.classList.remove('is-dragging');
    }));
    zone.addEventListener('drop', (event) => {
      const file = event.dataTransfer?.files?.[0];
      if (!file) return;
      const transfer = new DataTransfer();
      transfer.items.add(file);
      input.files = transfer.files;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    });
  });
})();
