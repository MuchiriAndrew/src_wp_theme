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

/** A registration cart can contain one programme only. */
function src_v3_validate_programme_cart_item($passed, $product_id, $quantity, $variation_id = 0) {
    if (!$passed || !function_exists('WC') || !WC()->cart) return $passed;
    $new_programme = absint(get_post_meta($product_id, '_src_programme_id', true));
    foreach (WC()->cart->get_cart() as $item) {
        $existing_programme = absint(get_post_meta($item['product_id'], '_src_programme_id', true));
        if (!$new_programme || $existing_programme !== $new_programme) {
            wc_add_notice(__('Please complete one programme registration per order.', 'src'), 'error');
            return false;
        }
    }
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

    foreach (WC()->cart->get_cart() as $item) {
        $existing_programme_id = absint(get_post_meta($item['product_id'], '_src_programme_id', true));
        if ($existing_programme_id !== $programme_id) {
            wp_send_json_error(['message' => __('Your cart already contains another item. Complete or clear it before starting this programme registration.', 'src')], 409);
        }
    }

    if (!WC()->cart->find_product_in_cart(WC()->cart->generate_cart_id($purchase['product_id']))) {
        $added = WC()->cart->add_to_cart($purchase['product_id'], 1);
        if (!$added) {
            wp_send_json_error(['message' => __('We could not add this programme to your cart. Please try again.', 'src')], 500);
        }
    }

    WC()->customer->set_billing_phone('+254' . $phone);
    WC()->customer->set_billing_email($email);
    WC()->customer->save();
    WC()->cart->calculate_totals();

    wp_send_json_success(['checkoutUrl' => wc_get_checkout_url()]);
}
add_action('wp_ajax_src_v3_prepare_programme_checkout', 'src_v3_prepare_programme_checkout');
add_action('wp_ajax_nopriv_src_v3_prepare_programme_checkout', 'src_v3_prepare_programme_checkout');
