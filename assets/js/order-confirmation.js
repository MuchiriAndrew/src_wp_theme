document.addEventListener('DOMContentLoaded', () => {
  const card = document.querySelector('[data-src-order-confirmation]');
  const config = window.srcRegistrationConfig;
  const order = config?.order;
  if (!card || !order) return;

  const copy = config.copy || {};
  const heading = card.querySelector('[data-register-confirm-heading]');
  const message = card.querySelector('[data-register-confirm-message]');
  const emailNote = card.querySelector('[data-register-confirm-email]');
  const receiptRow = card.querySelector('[data-register-confirm-receipt-row]');
  const receiptValue = card.querySelector('[data-register-confirm-receipt]');
  const retry = card.querySelector('[data-register-confirm-retry]');
  const contact = card.querySelector('[data-register-confirm-contact]');
  const refresh = card.querySelector('[data-register-confirm-refresh]');
  const programme = card.querySelector('[data-register-confirm-programme]');

  const paintStatus = ({ paid, failed, retryable, receipt, failureMessage }) => {
    const state = paid ? 'paid' : failed ? 'failed' : 'pending';
    card.dataset.paymentState = state;
    heading.textContent = paid
      ? (copy.paidHeading || 'Your payment is confirmed')
      : failed ? (copy.failedHeading || 'Payment was not completed') : (copy.waitingHeading || 'Waiting for payment confirmation');
    message.textContent = paid
      ? (copy.paidMessage || 'Thank you for registering. Your place has been confirmed and our team will follow up with programme details.')
      : failed ? (failureMessage || copy.failedMessage || 'Your payment was not completed. You can try again.')
        : (copy.waitingMessage || 'Complete the M-PESA prompt on your phone. This page will update when your payment is confirmed.');
    emailNote.textContent = paid
      ? (copy.paidEmail || 'We have sent your order confirmation to %s. Please check your inbox and spam folder.').replace('%s', order.email)
      : failed ? (retryable
        ? (copy.retryGuide || 'Your registration details are saved. On the next page, confirm your M-PESA number before requesting a new payment prompt.')
        : (copy.contactGuide || 'Have your order number ready when you contact SRC. Please do not start another payment until the order is checked.'))
        : (copy.waitingEmail || 'Your confirmation email will be sent to %s after payment.').replace('%s', order.email);
    receiptRow.hidden = !receipt;
    receiptValue.textContent = receipt || '';
    retry.hidden = state !== 'failed' || !retryable;
    contact.hidden = state !== 'failed' || !!retryable;
    refresh.hidden = state !== 'pending';
    programme.hidden = state !== 'paid';
  };

  if (order.paid || order.failed) return;
  let attempts = 0;
  const poll = async () => {
    if (attempts++ >= 120) return;
    const payload = new FormData();
    payload.set('action', 'src_v3_registration_order_status');
    payload.set('order_id', order.id);
    payload.set('order_key', order.key);
    try {
      const response = await fetch(config.ajaxUrl, { method: 'POST', body: payload, cache: 'no-store', credentials: 'same-origin' });
      const result = await response.json();
      if (response.ok && result.success) {
        paintStatus(result.data);
        if (result.data.paid || result.data.failed) return;
      }
    } catch (error) { /* The refresh link remains available when a poll fails. */ }
    setTimeout(poll, 5000);
  };
  setTimeout(poll, 5000);
});
