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
<dialog class="src-register-dialog" id="src-registration-dialog" aria-label="Programme registration" data-src-registration-dialog>
    <div class="src-register-shell">
        <header class="src-register-hero" data-register-hero style="--register-image: url('<?php echo esc_url($first_image); ?>')">
            <div class="src-register-hero-copy">
                <p>Programme registration</p>
                <h2 data-register-title><?php echo esc_html(get_post_meta($first_programme->ID, '_programme_hero_title', true) ?: get_the_title($first_programme)); ?></h2>
            </div>
            <button class="src-register-close" type="button" aria-label="Close registration" data-register-close>×</button>
        </header>

        <ol class="src-register-steps" aria-label="Registration progress">
            <li aria-current="step" data-register-step-label="0"><span>01</span> Select Programme</li>
            <li data-register-step-label="1"><span>02</span> Your Details</li>
            <li data-register-step-label="2"><span>03</span> Payment</li>
            <li data-register-step-label="3"><span>04</span> Confirmation</li>
        </ol>

        <div class="src-register-content">
            <section data-register-step="0">
                <h3>Choose your programme</h3>
                <div class="src-register-options" role="group" aria-label="Available programmes">
                    <?php foreach ($registration_programmes as $index => $programme) :
                        $programme_id = $programme->ID;
                        $title = get_post_meta($programme_id, '_programme_hero_title', true) ?: get_the_title($programme);
                        $dates = get_post_meta($programme_id, '_programme_dates', true) ?: 'Rolling applications';
                        $image = src_image_url($programme_id, '');
                        $purchase = src_v3_programme_purchase_state($programme_id);
                        $fee = get_post_meta($programme_id, '_programme_fee', true) ?: '[TBC]';
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
                <button class="src-register-primary" type="button" data-register-next>Continue</button>
            </section>

            <section data-register-step="1" hidden>
                <h3>Tell us about you</h3>
                <form data-register-details novalidate>
                    <div class="src-register-fields">
                        <label>Phone number
                            <span class="src-register-phone"><span>+254</span><input type="tel" inputmode="numeric" autocomplete="tel-national" placeholder="7XXXXXXXX" maxlength="9" data-register-phone aria-describedby="register-phone-error"></span>
                            <small class="src-register-error" id="register-phone-error" data-register-error="phone" hidden></small>
                        </label>
                        <label>Email address
                            <input type="email" autocomplete="email" placeholder="you@example.com" data-register-email aria-describedby="register-email-error">
                            <small class="src-register-error" id="register-email-error" data-register-error="email" hidden></small>
                        </label>
                    </div>
                    <label class="src-register-consent"><input type="checkbox" data-register-consent aria-describedby="register-consent-error"><span>I consent to SRC processing my information for programme registration and communication.</span></label>
                    <small class="src-register-error" id="register-consent-error" data-register-error="consent" hidden></small>
                    <div class="src-register-actions">
                        <button class="src-register-back" type="button" data-register-back>Back</button>
                        <button class="src-register-primary" type="submit" data-register-details-submit>Continue to payment</button>
                    </div>
                </form>
            </section>

            <section data-register-step="2" hidden>
                <h3>Complete payment</h3>
                <div class="src-register-payment" data-register-payment-ready hidden>
                    <p>Your registration is ready for secure checkout. Available payment methods, including M-Pesa when enabled, will appear there.</p>
                    <dl><div><dt>Programme</dt><dd data-register-summary-title></dd></div><div><dt>Fee</dt><dd data-register-summary-fee></dd></div></dl>
                    <p class="src-register-error" role="status" data-register-checkout-error hidden></p>
                    <div class="src-register-actions"><button class="src-register-back" type="button" data-register-back>Back</button><button class="src-register-primary" type="button" data-register-checkout>Continue to secure checkout</button></div>
                </div>
                <div class="src-register-payment-pending" data-register-payment-pending hidden>
                    <p>The programme fee is still being confirmed. Checkout will open once SRC publishes a payable fee for this programme.</p>
                    <a class="src-register-primary" data-register-contact href="<?php echo esc_url(src_page_url('contact')); ?>">Ask SRC about this programme</a>
                    <button class="src-register-back" type="button" data-register-back>Back to your details</button>
                </div>
            </section>

            <section data-register-step="3" hidden>
                <p class="src-register-confirm-kicker">Programme registration</p>
                <h3 data-register-confirm-heading>Checking your payment</h3>
                <p data-register-confirm-message>We are waiting for your payment provider to confirm the transaction. Please complete the M-PESA prompt on your phone.</p>
                <dl class="src-register-confirm-facts"><div><dt>Programme</dt><dd data-register-confirm-programme></dd></div><div><dt>Order</dt><dd data-register-confirm-order></dd></div><div data-register-confirm-receipt-row hidden><dt>M-PESA receipt</dt><dd data-register-confirm-receipt></dd></div></dl>
                <p class="src-register-confirm-email" data-register-confirm-email></p>
                <a class="src-register-primary" data-register-confirm-retry hidden href="#">Retry payment</a>
                <a class="src-register-primary" data-register-confirm-done href="<?php echo esc_url(home_url('/')); ?>">Return to homepage</a>
            </section>
        </div>
    </div>
</dialog>
