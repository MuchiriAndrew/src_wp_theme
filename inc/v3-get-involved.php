<?php
if (!defined('ABSPATH')) exit;

function src_get_involved_form_handler() {
    $page = get_page_by_path('get-involved');
    $redirect = $page ? get_permalink($page) : home_url('/get-involved/');
    $read = static function ($key, $textarea = false) {
        $value = isset($_POST[$key]) && is_scalar($_POST[$key]) ? wp_unslash($_POST[$key]) : '';
        return $textarea ? sanitize_textarea_field($value) : sanitize_text_field($value);
    };
    $track = sanitize_key($read('involvement_track'));
    $redirect = add_query_arg('track', $track !== '' ? $track : 'donor', $redirect);

    if (!isset($_POST['src_involvement_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['src_involvement_nonce'])), 'src_involvement_submit')) {
        wp_safe_redirect(add_query_arg('involvement_status', 'error', $redirect));
        exit;
    }

    $tracks = $page ? src_parse_rows(get_post_meta($page->ID, '_page_involvement_tracks', true)) : [];
    $track_names = [];
    foreach ($tracks as $row) if (!empty($row[0])) $track_names[sanitize_key($row[0])] = sanitize_text_field($row[1] ?? $row[0]);
    if (!isset($track_names[$track])) $track = 'donor';

    $name = $read('name');
    $role = $read('role');
    $email = sanitize_email($read('email'));
    $organisation = $read('organisation');
    $message = $read('message', true);
    $recipient = $page ? sanitize_email(get_post_meta($page->ID, '_page_involvement_email', true)) : '';
    if (!is_email($recipient)) {
        $contact_page = get_page_by_path('contact');
        $recipient = $contact_page ? sanitize_email(get_post_meta($contact_page->ID, '_page_contact_email', true)) : '';
    }
    if (!is_email($recipient)) $recipient = sanitize_email(get_option('admin_email'));

    $sent = false;
    if ($name !== '' && is_email($email) && is_email($recipient)) {
        $track_label = $track_names[$track] ?? 'Partner With Us';
        $mail_subject = 'SRC website enquiry: ' . $track_label;
        $mail_body = "Path: {$track_label}\nName: {$name}\nRole / Title: {$role}\nEmail: {$email}\nOrganisation: {$organisation}\n\n{$message}";
        $sent = wp_mail($recipient, $mail_subject, $mail_body, ['Reply-To: ' . $name . ' <' . $email . '>']);
    }
    wp_safe_redirect(add_query_arg('involvement_status', $sent ? 'sent' : 'error', add_query_arg('track', $track, $redirect)));
    exit;
}
add_action('admin_post_src_involvement', 'src_get_involved_form_handler');
add_action('admin_post_nopriv_src_involvement', 'src_get_involved_form_handler');
