<?php
if (!defined('ABSPATH')) exit;

$order = $args['order'] ?? null;
if (!$order instanceof WC_Order) return;

$programme_id = src_v3_order_programme_id($order);
$programme_title = get_post_meta($programme_id, '_programme_hero_title', true) ?: get_the_title($programme_id);
$programme_url = get_permalink($programme_id) ?: (get_post_type_archive_link('programme') ?: home_url('/programmes/'));
$programmes_url = get_post_type_archive_link('programme') ?: home_url('/programmes/');
$state = src_v3_registration_payment_state($order);
$status = $state['paid'] ? 'paid' : ($state['failed'] ? 'failed' : 'pending');
$waiting_message = str_replace('This window will', 'This page will', src_option('registration_waiting_message', 'Complete the M-PESA prompt on your phone. This page will update when your payment is confirmed.'));
$heading = $state['paid']
    ? src_option('registration_paid_heading', 'Your payment is confirmed')
    : ($state['failed'] ? src_option('registration_failed_heading', 'Payment was not completed') : src_option('registration_waiting_heading', 'Waiting for payment confirmation'));
$message = $state['paid']
    ? src_option('registration_paid_message', 'Thank you for registering. Your place has been confirmed and our team will follow up with programme details.')
    : ($state['failed'] ? ($state['failureMessage'] ?: src_option('registration_failed_message', 'Your payment was not completed. You can try again.')) : $waiting_message);
$email_message = $state['paid']
    ? sprintf(src_option('registration_paid_email_message', 'We have sent your order confirmation to %s. Please check your inbox and spam folder.'), $order->get_billing_email())
    : ($state['failed'] ? src_option('registration_pending_email_message', 'Your confirmation email will be sent after the payment is confirmed.') : sprintf(src_option('registration_waiting_email_message', 'Your confirmation email will be sent to %s after payment.'), $order->get_billing_email()));
?>
<article class="src-order-confirmation" data-src-order-confirmation data-payment-state="<?php echo esc_attr($status); ?>" aria-labelledby="src-order-confirmation-title">
    <div class="src-order-confirmation-head">
        <p class="src-order-confirmation-kicker"><?php echo esc_html(src_option('checkout_confirmation_label', 'Registration · Confirmation')); ?></p>
        <div class="src-order-confirmation-title-row">
            <span class="src-order-confirmation-icon" aria-hidden="true"></span>
            <h2 id="src-order-confirmation-title" data-register-confirm-heading role="status" aria-live="polite"><?php echo esc_html($heading); ?></h2>
        </div>
        <p class="src-order-confirmation-message" data-register-confirm-message><?php echo esc_html($message); ?></p>
    </div>

    <dl class="src-order-confirmation-facts">
        <div><dt><?php echo esc_html(src_option('registration_programme_label', 'Programme')); ?></dt><dd><?php echo esc_html($programme_title); ?></dd></div>
        <div><dt><?php echo esc_html(src_option('registration_order_label', 'Order')); ?></dt><dd>#<?php echo esc_html($order->get_order_number()); ?></dd></div>
        <div data-register-confirm-receipt-row<?php echo $state['receipt'] ? '' : ' hidden'; ?>><dt><?php echo esc_html(src_option('registration_receipt_label', 'M-PESA receipt')); ?></dt><dd data-register-confirm-receipt><?php echo esc_html($state['receipt']); ?></dd></div>
    </dl>

    <div class="src-order-confirmation-next">
        <h3><?php esc_html_e('What happens next', 'src'); ?></h3>
        <p data-register-confirm-email><?php echo esc_html($email_message); ?></p>
    </div>

    <div class="src-order-confirmation-actions">
        <a class="src-order-confirmation-primary" data-register-confirm-retry href="<?php echo esc_url($order->get_checkout_payment_url()); ?>"<?php echo $status === 'failed' ? '' : ' hidden'; ?>><?php echo esc_html(src_option('registration_retry_label', 'Retry payment')); ?> <span aria-hidden="true">→</span></a>
        <a class="src-order-confirmation-primary" data-register-confirm-refresh href="<?php echo esc_url($order->get_checkout_order_received_url()); ?>"<?php echo $status === 'pending' ? '' : ' hidden'; ?>><?php esc_html_e('Refresh payment status', 'src'); ?> <span aria-hidden="true">↻</span></a>
        <a class="src-order-confirmation-primary" data-register-confirm-programme href="<?php echo esc_url($programme_url); ?>"<?php echo $status === 'paid' ? '' : ' hidden'; ?>><?php esc_html_e('View your programme', 'src'); ?> <span aria-hidden="true">→</span></a>
        <a class="src-order-confirmation-secondary" href="<?php echo esc_url($programmes_url); ?>"><?php esc_html_e('Explore all programmes', 'src'); ?> <span aria-hidden="true">↗</span></a>
    </div>
</article>
