<?php
if (!defined('ABSPATH')) exit;
do_action('woocommerce_email_header', $email_heading, $email);
do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email);
echo '<h2 style="margin:24px 0 10px;color:#78191e;font-family:Georgia,serif">' . esc_html__('Payment summary', 'src') . '</h2>';
echo '<table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;border-top:1px solid #ded7ce;border-bottom:1px solid #ded7ce">';
foreach ($order->get_items() as $item) {
    echo '<tr><td style="padding:15px 0;color:#242323">' . esc_html($item->get_name()) . '</td><td style="padding:15px 0;text-align:right;color:#242323">' . wp_kses_post($order->get_formatted_line_subtotal($item)) . '</td></tr>';
}
echo '<tr><td style="padding:15px 0;border-top:1px solid #ded7ce;font-weight:700">' . esc_html__('Total paid', 'src') . '</td><td style="padding:15px 0;border-top:1px solid #ded7ce;text-align:right;font-weight:700">' . wp_kses_post($order->get_formatted_order_total()) . '</td></tr></table>';
echo '<p style="margin:24px 0 0;color:#555;font-size:13px">' . esc_html__('Registration email', 'src') . ': <strong>' . esc_html($order->get_billing_email()) . '</strong>';
if ($order->get_billing_phone()) echo '<br>' . esc_html__('M-PESA phone', 'src') . ': <strong>' . esc_html($order->get_billing_phone()) . '</strong>';
echo '</p>';
if (!empty($additional_content)) echo wp_kses_post(wpautop(wptexturize($additional_content)));
do_action('woocommerce_email_footer', $email);
