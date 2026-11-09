document.addEventListener('DOMContentLoaded', () => {
  const dialog = document.querySelector('[data-src-registration-dialog]');
  if (!dialog) return;

  const options = [...dialog.querySelectorAll('[data-register-program]')];
  const steps = [...dialog.querySelectorAll('[data-register-step]')];
  const stepLabels = [...dialog.querySelectorAll('[data-register-step-label]')];
  const hero = dialog.querySelector('[data-register-hero]');
  const title = dialog.querySelector('[data-register-title]');
  const phone = dialog.querySelector('[data-register-phone]');
  const email = dialog.querySelector('[data-register-email]');
  const consent = dialog.querySelector('[data-register-consent]');
  const detailsForm = dialog.querySelector('[data-register-details]');
  const checkoutButton = dialog.querySelector('[data-register-checkout]');
  const returnedOrder = window.srcRegistrationConfig?.order;
  let selected = options[0] || null;
  let activeStep = 0;

  const showError = (name, message) => {
    const error = dialog.querySelector(`[data-register-error="${name}"]`);
    const control = name === 'phone' ? phone : name === 'email' ? email : consent;
    if (error) {
      error.textContent = message;
      error.hidden = !message;
    }
    if (control && name !== 'consent') control.setAttribute('aria-invalid', message ? 'true' : 'false');
  };

  const selectProgramme = (option) => {
    if (!option) return;
    selected = option;
    options.forEach((item) => {
      const active = item === selected;
      item.classList.toggle('is-selected', active);
      item.setAttribute('aria-pressed', String(active));
    });
    if (title) title.textContent = selected.dataset.programmeTitle || '';
    if (hero && selected.dataset.programmeImage) hero.style.setProperty('--register-image', `url("${selected.dataset.programmeImage}")`);
    const canCheckout = selected.dataset.programmePurchasable === '1';
    const ready = dialog.querySelector('[data-register-payment-ready]');
    const pending = dialog.querySelector('[data-register-payment-pending]');
    if (ready) ready.hidden = !canCheckout;
    if (pending) pending.hidden = canCheckout;
    dialog.querySelector('[data-register-summary-title]')?.replaceChildren(document.createTextNode(selected.dataset.programmeTitle || ''));
    dialog.querySelector('[data-register-summary-fee]')?.replaceChildren(document.createTextNode(selected.dataset.programmeFee || '[TBC]'));
    const contact = dialog.querySelector('[data-register-contact]');
    if (contact && selected.dataset.programmeContact) contact.href = selected.dataset.programmeContact;
  };

  const showStep = (step) => {
    activeStep = Math.max(0, Math.min(3, step));
    steps.forEach((panel) => { panel.hidden = Number(panel.dataset.registerStep) !== activeStep; });
    stepLabels.forEach((label) => {
      const index = Number(label.dataset.registerStepLabel);
      if (index === activeStep) label.setAttribute('aria-current', 'step');
      else label.removeAttribute('aria-current');
      label.classList.toggle('is-complete', index < activeStep);
    });
    const panel = dialog.querySelector(`[data-register-step="${activeStep}"]`);
    panel?.querySelector('h3')?.focus?.({ preventScroll: true });
  };

  const clearForm = () => {
    detailsForm?.reset();
    showError('phone', '');
    showError('email', '');
    showError('consent', '');
    const checkoutError = dialog.querySelector('[data-register-checkout-error]');
    if (checkoutError) { checkoutError.hidden = true; checkoutError.textContent = ''; }
  };

  const openRegistration = (slug) => {
    const option = options.find((item) => item.dataset.programmeSlug === slug) || options[0];
    selectProgramme(option);
    clearForm();
    showStep(0);
    if (!dialog.open) dialog.showModal();
    requestAnimationFrame(() => (selected || options[0])?.focus({ preventScroll: true }));
  };

  if (returnedOrder) {
    const orderProgramme = options.find((item) => Number(item.dataset.programmeId) === Number(returnedOrder.programmeId));
    if (orderProgramme) selectProgramme(orderProgramme);
    else if (title) title.textContent = returnedOrder.programmeTitle || '';
    const heading = dialog.querySelector('[data-register-confirm-heading]');
    const message = dialog.querySelector('[data-register-confirm-message]');
    const emailNote = dialog.querySelector('[data-register-confirm-email]');
    const retry = dialog.querySelector('[data-register-confirm-retry]');
    const done = dialog.querySelector('[data-register-confirm-done]');
    dialog.querySelector('[data-register-confirm-programme]').textContent = returnedOrder.programmeTitle || '';
    dialog.querySelector('[data-register-confirm-order]').textContent = `#${returnedOrder.number}`;
    const paintStatus = (paid, failed, receipt) => {
      if (paid) {
        heading.textContent = 'Your payment is confirmed';
        message.textContent = 'Thank you for registering. Your place has been confirmed and our team will follow up with programme details.';
        emailNote.textContent = `We have sent your order confirmation to ${returnedOrder.email}. Please check your inbox and spam folder.`;
        if (receipt) {
          dialog.querySelector('[data-register-confirm-receipt]').textContent = receipt;
          dialog.querySelector('[data-register-confirm-receipt-row]').hidden = false;
        }
        retry.hidden = true;
        done.hidden = false;
      } else if (failed) {
        heading.textContent = 'Payment was not completed';
        message.textContent = 'Your registration is still awaiting payment. You can try the payment again.';
        emailNote.textContent = 'Your confirmation email will be sent after the payment is confirmed.';
        retry.hidden = false;
        done.hidden = true;
      } else {
        heading.textContent = 'Waiting for payment confirmation';
        message.textContent = 'Complete the M-PESA prompt on your phone. This window will update when your payment is confirmed.';
        emailNote.textContent = `Your confirmation email will be sent to ${returnedOrder.email} after payment.`;
        retry.hidden = true;
        done.hidden = true;
      }
    };
    retry.href = returnedOrder.retryUrl;
    paintStatus(returnedOrder.paid, false, returnedOrder.receipt);
    showStep(3);
    if (!dialog.open) dialog.showModal();
    if (!returnedOrder.paid) {
      let attempts = 0;
      const poll = async () => {
        if (attempts++ >= 120) return;
        const payload = new FormData();
        payload.set('action', 'src_v3_registration_order_status');
        payload.set('order_id', returnedOrder.id);
        payload.set('order_key', returnedOrder.key);
        try {
          const response = await fetch(window.srcRegistrationConfig.ajaxUrl, { method: 'POST', body: payload, cache: 'no-store', credentials: 'same-origin' });
          const result = await response.json();
          if (response.ok && result.success) {
            paintStatus(result.data.paid, result.data.failed, result.data.receipt);
            if (result.data.paid || result.data.failed) return;
          }
        } catch (error) { /* A temporary network error should not change payment state. */ }
        setTimeout(poll, 5000);
      };
      setTimeout(poll, 5000);
    }
  }

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-src-open-registration]');
    if (!trigger) return;
    event.preventDefault();
    openRegistration(trigger.dataset.srcOpenRegistration);
  });

  options.forEach((option) => option.addEventListener('click', () => selectProgramme(option)));
  dialog.querySelector('[data-register-close]')?.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
  dialog.addEventListener('close', () => { showStep(0); clearForm(); });
  dialog.querySelector('[data-register-next]')?.addEventListener('click', () => showStep(1));
  dialog.querySelectorAll('[data-register-back]').forEach((button) => button.addEventListener('click', () => showStep(activeStep - 1)));

  phone?.addEventListener('input', () => {
    phone.value = phone.value.replace(/\D/g, '').slice(0, 9);
    if (phone.value) showError('phone', '');
  });
  email?.addEventListener('input', () => { if (email.value) showError('email', ''); });
  consent?.addEventListener('change', () => { if (consent.checked) showError('consent', ''); });

  detailsForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    const validPhone = /^[71]\d{8}$/.test(phone?.value.trim() || '');
    const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email?.value.trim() || '');
    showError('phone', validPhone ? '' : 'Enter 9 digits starting with 7 or 1.');
    showError('email', validEmail ? '' : 'Enter a valid email address.');
    showError('consent', consent?.checked ? '' : 'Consent is required to continue.');
    if (validPhone && validEmail && consent?.checked) showStep(2);
  });

  checkoutButton?.addEventListener('click', async () => {
    if (!selected || !window.srcRegistrationConfig?.ajaxUrl) return;
    const checkoutError = dialog.querySelector('[data-register-checkout-error]');
    checkoutButton.disabled = true;
    checkoutButton.setAttribute('aria-busy', 'true');
    checkoutButton.textContent = 'Preparing checkout…';
    if (checkoutError) checkoutError.hidden = true;

    const payload = new FormData();
    payload.set('action', 'src_v3_prepare_programme_checkout');
    payload.set('nonce', window.srcRegistrationConfig.nonce);
    payload.set('programme_id', selected.dataset.programmeId || '');
    payload.set('phone', phone?.value || '');
    payload.set('email', email?.value || '');
    payload.set('consent', consent?.checked ? '1' : '0');

    try {
      const response = await fetch(window.srcRegistrationConfig.ajaxUrl, { method: 'POST', body: payload, credentials: 'same-origin' });
      const result = await response.json();
      if (!response.ok || !result.success || !result.data?.checkoutUrl) throw new Error(result.data?.message || 'Checkout could not be started. Please try again.');
      window.location.assign(result.data.checkoutUrl);
    } catch (error) {
      if (checkoutError) {
        checkoutError.textContent = error.message || 'Checkout could not be started. Please try again.';
        checkoutError.hidden = false;
      }
      checkoutButton.disabled = false;
      checkoutButton.removeAttribute('aria-busy');
      checkoutButton.textContent = 'Continue to secure checkout';
    }
  });
});
