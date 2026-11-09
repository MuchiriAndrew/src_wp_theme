<?php
if (!defined('ABSPATH')) exit;

$registration_programmes = get_posts([
    'post_type' => 'programme',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_query' => [
        ['key' => '_src_v3_imported', 'value' => '1'],
        ['key' => '_programme_status', 'value' => 'current'],
        ['key' => '_programme_enrolment', 'value' => 'open'],
    ],
]);
$registration_order = ['investor-readiness' => 1, 'export-readiness' => 2, 'herventure' => 3, 'webinar-series' => 4];
usort($registration_programmes, static function ($a, $b) use ($registration_order) {
    return ($registration_order[$a->post_name] ?? 99) <=> ($registration_order[$b->post_name] ?? 99);
});
if (!$registration_programmes) return;

$first_programme = $registration_programmes[0];
$first_image = src_image_url($first_programme->ID, '');
?>
<dialog class="src-register-dialog" id="src-registration-dialog" aria-label="<?php echo esc_attr(src_option('registration_dialog_aria', 'Programme registration')); ?>" data-src-registration-dialog>
    <div class="src-register-shell">
        <header class="src-register-hero" data-register-hero style="--register-image: url('<?php echo esc_url($first_image); ?>')">
            <div class="src-register-hero-copy">
                <p><?php echo esc_html(src_option('registration_heading', 'Programme registration')); ?></p>
                <h2 data-register-title><?php echo esc_html(get_post_meta($first_programme->ID, '_programme_hero_title', true) ?: get_the_title($first_programme)); ?></h2>
            </div>
            <button class="src-register-close" type="button" aria-label="<?php echo esc_attr(src_option('registration_close_label', 'Close registration')); ?>" data-register-close>×</button>
        </header>

        <ol class="src-register-steps" aria-label="<?php echo esc_attr(src_option('registration_progress_label', 'Registration progress')); ?>">
            <li aria-current="step" data-register-step-label="0"><span>01</span> <?php echo esc_html(src_option('registration_step_programme', 'Select Programme')); ?></li>
            <li data-register-step-label="1"><span>02</span> <?php echo esc_html(src_option('registration_step_details', 'Your Details')); ?></li>
            <li data-register-step-label="2"><span>03</span> <?php echo esc_html(src_option('registration_step_payment', 'Payment')); ?></li>
            <li data-register-step-label="3"><span>04</span> <?php echo esc_html(src_option('registration_step_confirmation', 'Confirmation')); ?></li>
        </ol>

        <div class="src-register-content">
            <section data-register-step="0">
                <h3><?php echo esc_html(src_option('registration_choose_programme_heading', 'Choose your programme')); ?></h3>
                <div class="src-register-options" role="group" aria-label="<?php echo esc_attr(src_option('registration_available_programmes_label', 'Available programmes')); ?>">
                    <?php foreach ($registration_programmes as $index => $programme) :
                        $programme_id = $programme->ID;
                        $title = get_post_meta($programme_id, '_programme_hero_title', true) ?: get_the_title($programme);
                        $dates = get_post_meta($programme_id, '_programme_dates', true) ?: src_option('registration_rolling_dates_label', 'Rolling applications');
                        $image = src_image_url($programme_id, '');
                        $purchase = src_v3_programme_purchase_state($programme_id);
                        $fee = get_post_meta($programme_id, '_programme_fee', true) ?: src_option('registration_fee_tbc_label', '[TBC]');
                        $price = $purchase['available'] ? number_format_i18n($purchase['price'], 0) . ' ' . $purchase['currency'] : $fee;
                        $contact_url = add_query_arg('programme', $programme->post_name, src_page_url('contact'));
                        ?>
                        <button class="src-register-option<?php echo $index === 0 ? ' is-selected' : ''; ?>" type="button"
                            data-register-program
                            data-programme-slug="<?php echo esc_attr($programme->post_name); ?>"
                            data-programme-id="<?php echo esc_attr($programme_id); ?>"
                            data-programme-title="<?php echo esc_attr($title); ?>"
                            data-programme-date="<?php echo esc_attr($dates); ?>"
                            data-programme-fee="<?php echo esc_attr($price); ?>"
                            data-programme-image="<?php echo esc_url($image); ?>"
                            data-programme-purchasable="<?php echo $purchase['available'] ? '1' : '0'; ?>"
                            data-programme-contact="<?php echo esc_url($contact_url); ?>"
                            aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                            <span><?php echo esc_html($title); ?></span>
                            <small><?php echo esc_html($dates); ?></small>
                        </button>
                    <?php endforeach; ?>
                </div>
                <button class="src-register-primary" type="button" data-register-next><?php echo esc_html(src_option('registration_continue_label', 'Continue')); ?></button>
            </section>

            <section data-register-step="1" hidden>
                <h3><?php echo esc_html(src_option('registration_details_heading', 'Tell us about you')); ?></h3>
                <form data-register-details novalidate>
                    <div class="src-register-fields">
                        <label><?php echo esc_html(src_option('registration_phone_label', 'Phone number')); ?>
                            <span class="src-register-phone"><span>+254</span><input type="tel" inputmode="numeric" autocomplete="tel-national" placeholder="<?php echo esc_attr(src_option('registration_phone_placeholder', '7XXXXXXXX')); ?>" maxlength="9" data-register-phone aria-describedby="register-phone-error"></span>
                            <small class="src-register-error" id="register-phone-error" data-register-error="phone" hidden></small>
                        </label>
                        <label><?php echo esc_html(src_option('registration_email_label', 'Email address')); ?>
                            <input type="email" autocomplete="email" placeholder="<?php echo esc_attr(src_option('registration_email_placeholder', 'you@example.com')); ?>" data-register-email aria-describedby="register-email-error">
                            <small class="src-register-error" id="register-email-error" data-register-error="email" hidden></small>
                        </label>
                    </div>
                    <label class="src-register-consent"><input type="checkbox" data-register-consent aria-describedby="register-consent-error"><span><?php echo esc_html(src_option('registration_consent_copy', 'I consent to SRC processing my information for programme registration and communication.')); ?></span></label>
                    <small class="src-register-error" id="register-consent-error" data-register-error="consent" hidden></small>
                    <div class="src-register-actions">
                        <button class="src-register-back" type="button" data-register-back><?php echo esc_html(src_option('registration_back_to_programme', 'Back')); ?></button>
                        <button class="src-register-primary" type="submit" data-register-details-submit><?php echo esc_html(src_option('registration_continue_payment', 'Continue to payment')); ?></button>
                    </div>
                </form>
            </section>

            <section data-register-step="2" hidden>
                <h3><?php echo esc_html(src_option('registration_payment_heading', 'Complete payment')); ?></h3>
                <div class="src-register-payment" data-register-payment-ready hidden>
                    <p><?php echo esc_html(src_option('registration_ready_copy', 'Your registration is ready for secure checkout. Available payment methods, including M-Pesa when enabled, will appear there.')); ?></p>
                    <dl><div><dt><?php echo esc_html(src_option('registration_programme_label', 'Programme')); ?></dt><dd data-register-summary-title></dd></div><div><dt><?php echo esc_html(src_option('registration_fee_label', 'Fee')); ?></dt><dd data-register-summary-fee></dd></div></dl>
                    <p class="src-register-error" role="status" data-register-checkout-error hidden></p>
                    <div class="src-register-actions"><button class="src-register-back" type="button" data-register-back><?php echo esc_html(src_option('registration_back_to_details', 'Back to your details')); ?></button><button class="src-register-primary" type="button" data-register-checkout><?php echo esc_html(src_option('registration_continue_checkout', 'Continue to secure checkout')); ?></button></div>
                </div>
                <div class="src-register-payment-pending" data-register-payment-pending hidden>
                    <p><?php echo esc_html(src_option('registration_payment_pending_copy', 'The programme fee is still being confirmed. Checkout will open once SRC publishes a payable fee for this programme.')); ?></p>
                    <a class="src-register-primary" data-register-contact href="<?php echo esc_url(src_page_url('contact')); ?>"><?php echo esc_html(src_option('registration_ask_about_label', 'Ask SRC about this programme')); ?></a>
                    <button class="src-register-back" type="button" data-register-back><?php echo esc_html(src_option('registration_back_to_details', 'Back to your details')); ?></button>
                </div>
            </section>

            <section data-register-step="3" hidden>
                <p class="src-register-confirm-kicker"><?php echo esc_html(src_option('registration_confirmation_kicker', 'Programme registration')); ?></p>
                <div class="src-register-confirm-status" role="status" aria-live="polite" aria-atomic="true">
                    <span class="src-register-confirm-indicator" data-register-confirm-indicator data-state="pending" aria-hidden="true"></span>
                    <h3 data-register-confirm-heading><?php echo esc_html(src_option('registration_checking_payment', 'Checking your payment')); ?></h3>
                </div>
                <p data-register-confirm-message><?php echo esc_html(src_option('registration_waiting_payment_copy', 'We are waiting for your payment provider to confirm the transaction. Please complete the M-PESA prompt on your phone.')); ?></p>
                <dl class="src-register-confirm-facts"><div><dt><?php echo esc_html(src_option('registration_programme_label', 'Programme')); ?></dt><dd data-register-confirm-programme></dd></div><div><dt><?php echo esc_html(src_option('registration_order_label', 'Order')); ?></dt><dd data-register-confirm-order></dd></div><div data-register-confirm-receipt-row hidden><dt><?php echo esc_html(src_option('registration_receipt_label', 'M-PESA receipt')); ?></dt><dd data-register-confirm-receipt></dd></div></dl>
                <p class="src-register-confirm-email" data-register-confirm-email></p>
                <a class="src-register-primary" data-register-confirm-retry hidden href="#"><?php echo esc_html(src_option('registration_retry_label', 'Retry payment')); ?></a>
                <a class="src-register-primary" data-register-confirm-done href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html(src_option('registration_return_home_label', 'Return to homepage')); ?></a>
            </section>
        </div>
    </div>
</dialog>
