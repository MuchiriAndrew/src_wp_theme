<?php
if (!defined('ABSPATH')) exit;
if (!function_exists('src_v3_order_programme_id') || !src_v3_order_programme_id($order)) {
    include WC()->plugin_path() . '/templates/emails/customer-processing-order.php';
    return;
}
include __DIR__ . '/src-programme-order.php';
