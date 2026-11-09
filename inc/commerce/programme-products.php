<?php
if (!defined('ABSPATH')) exit;

/** Keep each explicitly priced, open v3 programme linked to one hidden virtual product. */
function src_v3_sync_programme_product($post_id, $post, $update) {
    if (!class_exists('WooCommerce') || !$post || $post->post_type !== 'programme') return;
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) return;
    if (get_post_meta($post_id, '_src_v3_imported', true) !== '1') return;

    $product_id = absint(get_post_meta($post_id, '_programme_product_id', true));
    $status = get_post_meta($post_id, '_programme_status', true);
    $enrolment = get_post_meta($post_id, '_programme_enrolment', true);
    $fee_label = trim((string) get_post_meta($post_id, '_programme_fee', true));
    $amount_raw = trim((string) get_post_meta($post_id, '_programme_fee_amount', true));
    $is_free = strtolower($fee_label) === 'free';
    $amount = $is_free ? 0 : (is_numeric($amount_raw) ? (float) $amount_raw : null);
    $is_open = $post->post_status === 'publish' && $status === 'current' && $enrolment === 'open';
    $is_valid_price = $amount !== null && $amount >= 0 && floor($amount) === $amount;

    if (!$is_open || !$is_valid_price) {
        if ($product_id && get_post_type($product_id) === 'product') {
            wp_update_post(['ID' => $product_id, 'post_status' => 'draft']);
        }
        return;
    }

    $product = $product_id ? wc_get_product($product_id) : false;
    if (!$product || !$product instanceof WC_Product) $product = new WC_Product_Simple();

    $title = (string) get_post_meta($post_id, '_programme_short_name', true);
    if ($title === '') $title = get_the_title($post_id);
    $product->set_name(sprintf(__('%s enrolment', 'src'), $title));
    $product->set_status('publish');
    $product->set_catalog_visibility('hidden');
    $product->set_virtual(true);
    $product->set_sold_individually(true);
    $product->set_regular_price((string) $amount);
    $product->set_price((string) $amount);
    $product->set_tax_status('none');
    $product->set_description((string) get_post_meta($post_id, '_programme_audience', true));
    $saved_product_id = $product->save();
    update_post_meta($saved_product_id, '_src_programme_id', $post_id);
    update_post_meta($post_id, '_programme_product_id', $saved_product_id);
}
add_action('save_post_programme', 'src_v3_sync_programme_product', 99, 3);

/** Enforce the programme's editorial eligibility even if a product URL is reused. */
function src_v3_programme_product_purchasable($purchasable, $product) {
    if (!$product instanceof WC_Product) return $purchasable;
    $programme_id = absint(get_post_meta($product->get_id(), '_src_programme_id', true));
    if (!$programme_id) return $purchasable;
    if (get_post_meta($programme_id, '_src_v3_imported', true) !== '1' || get_post_status($programme_id) !== 'publish') return false;
    if (get_post_meta($programme_id, '_programme_status', true) !== 'current' || get_post_meta($programme_id, '_programme_enrolment', true) !== 'open') return false;
    $fee = strtolower(trim((string) get_post_meta($programme_id, '_programme_fee', true)));
    $raw = trim((string) get_post_meta($programme_id, '_programme_fee_amount', true));
    if ($fee === 'free') return (float) $product->get_price() === 0.0;
    if ($raw === '' || !is_numeric($raw) || (float) $raw <= 0 || floor((float) $raw) !== (float) $raw) return false;
    return (float) $product->get_price() === (float) $raw && get_woocommerce_currency() === 'KES';
}
add_filter('woocommerce_is_purchasable', 'src_v3_programme_product_purchasable', 20, 2);

/** A programme enrolment always starts a fresh secure checkout. */
function src_v3_validate_programme_cart_item($passed, $product_id, $quantity, $variation_id = 0) {
    if (!$passed || !function_exists('WC') || !WC()->cart) return $passed;
    $new_programme = absint(get_post_meta($product_id, '_src_programme_id', true));
    if ($new_programme && !WC()->cart->is_empty()) WC()->cart->empty_cart();
    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'src_v3_validate_programme_cart_item', 20, 4);

function src_v3_programme_purchase_state($programme_id) {
    if (!class_exists('WooCommerce') || !function_exists('wc_get_product')) {
        return ['available' => false, 'product_id' => 0, 'price' => null, 'currency' => 'KES'];
    }
    $product_id = absint(get_post_meta($programme_id, '_programme_product_id', true));
    $product = $product_id ? wc_get_product($product_id) : false;
    $ready = $product instanceof WC_Product && $product->is_purchasable() && $product->is_in_stock();
    return [
        'available' => $ready,
        'product_id' => $product_id,
        'price' => $ready ? (float) $product->get_price() : null,
        'currency' => get_woocommerce_currency(),
    ];
}

/** Prepare the selected programme in the WooCommerce cart and carry checkout details in the WC customer session. */
function src_v3_prepare_programme_checkout() {
    check_ajax_referer('src_v3_registration', 'nonce');

    $programme_id = absint($_POST['programme_id'] ?? 0);
    $phone = preg_replace('/\D+/', '', (string) wp_unslash($_POST['phone'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $consent = sanitize_text_field(wp_unslash($_POST['consent'] ?? ''));

    if (!$programme_id || get_post_type($programme_id) !== 'programme' || get_post_status($programme_id) !== 'publish'
        || get_post_meta($programme_id, '_src_v3_imported', true) !== '1'
        || get_post_meta($programme_id, '_programme_status', true) !== 'current'
        || get_post_meta($programme_id, '_programme_enrolment', true) !== 'open') {
        wp_send_json_error(['message' => __('This programme is not currently accepting registrations.', 'src')], 400);
    }

    if (!preg_match('/^[71][0-9]{8}$/', $phone) || !is_email($email) || $consent !== '1') {
        wp_send_json_error(['message' => __('Enter a valid Kenyan phone number and email address, and provide consent.', 'src')], 400);
    }

    $purchase = src_v3_programme_purchase_state($programme_id);
    if (!$purchase['available']) {
        wp_send_json_error(['message' => __('Checkout is not open yet because SRC has not published a payable fee for this programme.', 'src')], 409);
    }

    if (!function_exists('WC') || !WC()->cart || !WC()->customer || !WC()->session) {
        wp_send_json_error(['message' => __('Secure checkout is temporarily unavailable. Please try again shortly.', 'src')], 503);
    }

    // A programme checkout is a single-purpose flow. Remove stale products or
    // an earlier programme before setting up this registration.
    if (!WC()->cart->is_empty()) WC()->cart->empty_cart();
    $added = WC()->cart->add_to_cart($purchase['product_id'], 1);
    if (!$added) {
        wp_send_json_error(['message' => __('We could not prepare secure checkout. Please try again.', 'src')], 500);
    }

    WC()->customer->set_billing_phone('+254' . $phone);
    WC()->customer->set_billing_email($email);
    WC()->customer->save();
    WC()->cart->calculate_totals();

    wp_send_json_success(['checkoutUrl' => wc_get_checkout_url()]);
}
add_action('wp_ajax_src_v3_prepare_programme_checkout', 'src_v3_prepare_programme_checkout');
add_action('wp_ajax_nopriv_src_v3_prepare_programme_checkout', 'src_v3_prepare_programme_checkout');

/** Keep the WooCommerce cart page out of the public registration journey. */
function src_v3_redirect_cart_page() {
    $cart_page_id = function_exists('wc_get_page_id') ? absint(wc_get_page_id('cart')) : 0;
    $is_cart_page = (function_exists('is_cart') && is_cart()) || is_page('cart') || ($cart_page_id && is_page($cart_page_id));
    $programmes_url = get_post_type_archive_link('programme');
    if (!$programmes_url) $programmes_url = home_url('/programmes/');

    if ($is_cart_page) {
        wp_safe_redirect($programmes_url, 302);
        exit;
    }

    // A direct checkout URL without a selected programme has no valid order context.
    // Keep payment retry and order-received endpoints available for existing orders.
    $is_empty_checkout = function_exists('is_checkout') && is_checkout()
        && !is_wc_endpoint_url('order-pay')
        && !is_wc_endpoint_url('order-received')
        && function_exists('WC') && WC()->cart && WC()->cart->is_empty();
    if ($is_empty_checkout) {
        wp_safe_redirect($programmes_url, 302);
        exit;
    }
}
add_action('template_redirect', 'src_v3_redirect_cart_page', 1);

/** Customers do not need an account page to enrol or pay for a programme. */
function src_v3_redirect_account_page() {
    $account_page_id = function_exists('wc_get_page_id') ? absint(wc_get_page_id('myaccount')) : 0;
    $is_account_page = (function_exists('is_account_page') && is_account_page()) || is_page('my-account') || ($account_page_id && is_page($account_page_id));
    if (!$is_account_page) return;

    wp_safe_redirect(get_post_type_archive_link('programme') ?: home_url('/programmes/'), 302);
    exit;
}
add_action('template_redirect', 'src_v3_redirect_account_page', 1);

/** Show the M-PESA number in Checkout Blocks, prefilled from the registration wizard. */
add_action('woocommerce_init', static function () {
    if (!function_exists('woocommerce_register_additional_checkout_field')) return;
    woocommerce_register_additional_checkout_field([
        'id' => 'src/mobile_phone',
        'label' => __('Phone number for M-PESA', 'src'),
        'location' => 'contact',
        'type' => 'text',
        'required' => true,
        'attributes' => ['autocomplete' => 'tel'],
        'validate_callback' => static function ($value) {
            $digits = preg_replace('/\D+/', '', (string) $value);
            if (preg_match('/^(?:254|0)[71][0-9]{8}$/', $digits)) return null;
            return new WP_Error('src_invalid_mpesa_phone', __('Enter a valid Kenyan mobile number, for example +254 712 345 678.', 'src'));
        },
    ]);
});

add_filter('woocommerce_get_default_value_for_src/mobile_phone', static function ($value) {
    if (!function_exists('WC') || !WC()->customer) return $value;
    return WC()->customer->get_billing_phone() ?: $value;
});

add_action('woocommerce_store_api_checkout_update_order_from_request', static function ($order, $request) {
    $phone = $request['additional_fields']['src/mobile_phone'] ?? '';
    $digits = preg_replace('/\D+/', '', (string) $phone);
    if (!preg_match('/^(?:254|0)[71][0-9]{8}$/', $digits)) return;
    $order->set_billing_phone('+254' . substr($digits, -9));
    $order->save();
}, 20, 2);

/** Create or link a silent WooCommerce customer record for programme registrations. */
function src_v3_attach_programme_customer($order) {
    if (is_numeric($order)) $order = wc_get_order(absint($order));
    if (!$order instanceof WC_Order || !src_v3_order_programme_id($order)) return;
    $email = sanitize_email($order->get_billing_email());
    if (!is_email($email)) return;

    $user = get_user_by('email', $email);
    if ($user) {
        $customer_id = (int) $user->ID;
    } else {
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $username = wc_create_new_customer_username($email, ['first_name' => $first_name, 'last_name' => $last_name]);
        $customer_id = wp_insert_user([
            'user_login' => $username,
            'user_pass' => wp_generate_password(40, true, true),
            'user_email' => $email,
            'role' => 'customer',
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => trim($first_name . ' ' . $last_name) ?: $email,
        ]);
        if (is_wp_error($customer_id)) {
            wc_get_logger()->error('Could not create programme customer record.', ['source' => 'src-programme-registration']);
            return;
        }
    }

    if ($order->get_customer_id() !== $customer_id) {
        $order->set_customer_id($customer_id);
        $order->save();
    }
    $customer = new WC_Customer($customer_id);
    $customer->set_billing_email($email);
    $customer->set_billing_first_name($order->get_billing_first_name());
    $customer->set_billing_last_name($order->get_billing_last_name());
    $customer->set_billing_phone($order->get_billing_phone());
    $customer->save();
}
add_action('woocommerce_store_api_checkout_order_processed', 'src_v3_attach_programme_customer', 30, 1);
add_action('woocommerce_checkout_order_processed', 'src_v3_attach_programme_customer', 30, 3);

/** The order key is required before exposing a registration result to the browser. */
function src_v3_order_programme_id($order) {
    if (!$order instanceof WC_Order) return 0;
    foreach ($order->get_items() as $item) {
        $programme_id = absint(get_post_meta($item->get_product_id(), '_src_programme_id', true));
        if ($programme_id) return $programme_id;
    }
    return 0;
}

function src_v3_registration_return_order() {
    if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-received')) return null;
    $order_id = absint(get_query_var('order-received'));
    $key = sanitize_text_field(wp_unslash($_GET['key'] ?? ''));
    $order = $order_id ? wc_get_order($order_id) : false;
    if (!$order || !$key || !hash_equals($order->get_order_key(), $key) || !src_v3_order_programme_id($order)) return null;
    return $order;
}

function src_v3_registration_order_status() {
    $order_id = absint($_POST['order_id'] ?? 0);
    $key = sanitize_text_field(wp_unslash($_POST['order_key'] ?? ''));
    $order = $order_id ? wc_get_order($order_id) : false;
    if (!$order || !$key || !hash_equals($order->get_order_key(), $key) || !src_v3_order_programme_id($order)) {
        wp_send_json_error(['message' => __('Order not found.', 'src')], 404);
    }
    nocache_headers();
    wp_send_json_success(src_v3_registration_payment_state($order));
}
add_action('wp_ajax_src_v3_registration_order_status', 'src_v3_registration_order_status');
add_action('wp_ajax_nopriv_src_v3_registration_order_status', 'src_v3_registration_order_status');

/** The order can remain payable after a failed M-PESA attempt. */
function src_v3_registration_payment_state($order) {
    $paid = $order->is_paid() || $order->get_meta('_mpesa_payment_confirmed') === 'yes';
    $failed = !$paid && ($order->get_meta('_mpesa_payment_attempt_status') === 'failed'
        || in_array($order->get_status(), ['failed', 'cancelled'], true));
    $result_code = (string) $order->get_meta('_mpesa_last_result_code');
    $failure_message = $result_code === '1032'
        ? __('The M-PESA request was cancelled on your phone. You can try again.', 'src')
        : ($result_code === '1037'
            ? __('The M-PESA request timed out. You can try again.', 'src')
            : __('Your payment was not completed. You can try again.', 'src'));
    return [
        'paid' => $paid,
        'failed' => $failed,
        'failureMessage' => $failed ? $failure_message : '',
        'receipt' => (string) $order->get_meta('_mpesa_receipt_number'),
    ];
}

/** Payment emails use WooCommerce's paid order lifecycle and mail transport. */
add_filter('woocommerce_email_styles', static function ($css, $email) {
    if (!in_array($email->id ?? '', ['customer_processing_order', 'customer_completed_order'], true)) return $css;
    return $css . '\n#wrapper {background:#f7f4ef!important;padding:36px 12px!important} #template_container {border:0!important;border-radius:0!important;box-shadow:none!important} #template_header {background:#78191e!important;border-radius:0!important} #template_header h1 {color:#fff!important;font-family:Georgia,serif!important;font-size:28px!important;line-height:1.2!important} #body_content {background:#fff!important} #body_content_inner {color:#242323!important;font-family:Arial,sans-serif!important;font-size:15px!important;line-height:1.7!important} #body_content_inner h2,#body_content_inner h3 {color:#78191e!important;font-family:Georgia,serif!important} #body_content_inner a {color:#78191e!important} #template_footer {background:#272322!important} #template_footer #credit {color:#fff!important} .td {border-color:#ded7ce!important}';
}, 20, 2);

add_filter('woocommerce_email_heading_customer_processing_order', 'src_v3_registration_email_heading', 20, 2);
add_filter('woocommerce_email_heading_customer_completed_order', 'src_v3_registration_email_heading', 20, 2);
add_filter('woocommerce_email_enabled_customer_on_hold_order', static function ($enabled, $order) {
    return src_v3_order_programme_id($order) ? false : $enabled;
}, 20, 2);
function src_v3_registration_email_heading($heading, $order) {
    return src_v3_order_programme_id($order) ? __('Your programme payment is confirmed', 'src') : $heading;
}

add_action('woocommerce_email_before_order_table', static function ($order, $sent_to_admin, $plain_text, $email) {
    if ($sent_to_admin || !in_array($email->id ?? '', ['customer_processing_order', 'customer_completed_order'], true)) return;
    $programme_id = src_v3_order_programme_id($order);
    if (!$programme_id) return;
    $name = $order->get_billing_first_name();
    $programme = get_post_meta($programme_id, '_programme_hero_title', true) ?: get_the_title($programme_id);
    $receipt = (string) $order->get_meta('_mpesa_receipt_number');
    if ($plain_text) {
        echo "\n" . sprintf(__('Hello %s,', 'src'), $name ?: __('there', 'src')) . "\n";
        echo sprintf(__('Your payment for %s is confirmed. Order #%s.', 'src'), $programme, $order->get_order_number()) . "\n";
        if ($receipt) echo sprintf(__('M-PESA receipt: %s', 'src'), $receipt) . "\n";
        echo __('Please keep this email for your records. The SRC team will contact you with the next steps for your programme.', 'src') . "\n\n";
        return;
    }
    echo '<div style="border-top:4px solid #ee8023;padding:24px 0 16px"><p style="margin:0 0 10px;color:#a74b24;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase">Smart Regional Consultants · Registration</p>';
    echo '<p style="margin:0 0 16px;font-family:Georgia,serif;font-size:26px;line-height:1.25;color:#78191e">' . esc_html(sprintf(__('You’re registered for %s', 'src'), $programme)) . '</p>';
    echo '<p style="margin:0 0 18px">' . esc_html(sprintf(__('Hello %s, your payment has been received. Keep this email as your order confirmation.', 'src'), $name ?: __('there', 'src'))) . '</p>';
    echo '<p style="margin:0;padding:14px 18px;background:#f7f4ef;color:#242323"><strong>' . esc_html__('Payment confirmed', 'src') . '</strong><br>' . esc_html(sprintf(__('Order #%s', 'src'), $order->get_order_number()));
    if ($receipt) echo '<br>' . esc_html(sprintf(__('M-PESA receipt: %s', 'src'), $receipt));
    echo '</p><p style="margin:20px 0 0">' . esc_html__('The SRC team will contact you with programme details and next steps. Please check your inbox and spam folder for future messages.', 'src') . '</p></div>';
}, 10, 4);
