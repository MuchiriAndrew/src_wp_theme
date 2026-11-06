(() => {
  const nav = document.querySelector('[data-expert-filters]');
  if (!nav) return;

  const row = nav.querySelector('.src-experts-v3-filter-row');
  const filterButtons = [...nav.querySelectorAll('[data-expert-filter]')];
  const categories = [...document.querySelectorAll('[data-expert-category]')];
  if (!row || !filterButtons.length || !categories.length) return;

  const updateDocked = () => {
    const docked = nav.getBoundingClientRect().top <= 88;
    nav.classList.toggle('is-docked', docked);
    document.documentElement.classList.toggle('subnav-is-docked', docked);
  };

  const updateScrollEdges = () => {
    nav.classList.toggle('has-scroll-left', row.scrollLeft > 1);
    nav.classList.toggle('has-scroll-right', row.scrollLeft + row.clientWidth < row.scrollWidth - 1);
  };

  const updateLastVisibleCategory = () => {
    const visible = categories.filter((category) => !category.hidden);
    categories.forEach((category) => {
      category.classList.toggle('is-last-visible', visible[visible.length - 1] === category);
    });
  };

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const selected = button.dataset.expertFilter;
      filterButtons.forEach((item) => {
        const active = item === button;
        item.classList.toggle('is-active', active);
        item.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      categories.forEach((category) => {
        category.hidden = selected !== 'all' && category.dataset.expertCategory !== selected;
      });
      updateLastVisibleCategory();
    });
  });

  updateLastVisibleCategory();
  updateDocked();
  updateScrollEdges();
  window.addEventListener('scroll', updateDocked, { passive: true });
  window.addEventListener('resize', () => {
    updateDocked();
    updateScrollEdges();
  }, { passive: true });
  row.addEventListener('scroll', updateScrollEdges, { passive: true });
  if ('ResizeObserver' in window) new ResizeObserver(updateScrollEdges).observe(row);
})();
