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
})();
