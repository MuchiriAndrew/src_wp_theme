(() => {
  const form = document.querySelector('.src-programme-filters');
  if (!form) return;

  const statusField = form.querySelector('input[name="status"]');
  form.querySelectorAll('[data-programme-status]').forEach((button) => {
    button.addEventListener('click', () => {
      if (statusField) statusField.value = button.dataset.programmeStatus || '';
      form.requestSubmit();
    });
  });

  form.querySelectorAll('select, input[type="checkbox"]').forEach((control) => {
    control.addEventListener('change', () => form.requestSubmit());
  });
})();
