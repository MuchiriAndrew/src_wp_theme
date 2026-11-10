<?php
if (!defined('ABSPATH')) exit;

function src_newsletter_table() {
    global $wpdb;
    return $wpdb->prefix . 'src_newsletter_subscribers';
}

function src_newsletter_install() {
    if (get_option('src_newsletter_schema_version') === '1') return;

    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = src_newsletter_table();
    $collate = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        email varchar(254) NOT NULL,
        subscribed_at datetime NOT NULL,
        source varchar(50) NOT NULL DEFAULT 'homepage',
        PRIMARY KEY  (id),
        UNIQUE KEY email (email)
    ) $collate;");
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table) {
        update_option('src_newsletter_schema_version', '1', false);
    }
}
add_action('init', 'src_newsletter_install');

function src_newsletter_subscribe() {
    $redirect = home_url('/');
    $nonce = isset($_POST['src_newsletter_nonce']) && is_scalar($_POST['src_newsletter_nonce'])
        ? sanitize_text_field(wp_unslash((string) $_POST['src_newsletter_nonce'])) : '';
    $email = isset($_POST['email']) && is_scalar($_POST['email'])
        ? strtolower(trim(sanitize_email(wp_unslash((string) $_POST['email'])))) : '';
    $trap = isset($_POST['website']) && is_scalar($_POST['website'])
        ? trim((string) wp_unslash($_POST['website'])) : '';

    $status = 'error';
    if (wp_verify_nonce($nonce, 'src_newsletter_subscribe') && $trap === '' && strlen($email) <= 254 && is_email($email)) {
        global $wpdb;
        $table = src_newsletter_table();
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE email = %s", $email));
        if ($existing) {
            $status = 'already';
        } else {
            $inserted = $wpdb->insert($table, [
                'email' => $email,
                'subscribed_at' => current_time('mysql', true),
                'source' => 'homepage',
            ], ['%s', '%s', '%s']);
            $status = $inserted ? 'subscribed' : 'error';
        }
    }

    wp_safe_redirect(add_query_arg('newsletter_status', $status, $redirect) . '#stay-connected');
    exit;
}
add_action('admin_post_src_newsletter_subscribe', 'src_newsletter_subscribe');
add_action('admin_post_nopriv_src_newsletter_subscribe', 'src_newsletter_subscribe');

function src_newsletter_admin_menu() {
    add_management_page('Newsletter Subscribers', 'Newsletter Subscribers', 'manage_options', 'src-newsletter-subscribers', 'src_newsletter_admin_page');
}
add_action('admin_menu', 'src_newsletter_admin_menu');

function src_newsletter_admin_page() {
    if (!current_user_can('manage_options')) return;
    global $wpdb;
    $table = src_newsletter_table();
    $page = max(1, absint($_GET['paged'] ?? 1));
    $per_page = 50;
    $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    $rows = $wpdb->get_results($wpdb->prepare("SELECT email, subscribed_at, source FROM $table ORDER BY subscribed_at DESC, id DESC LIMIT %d OFFSET %d", $per_page, ($page - 1) * $per_page));
    $export_url = wp_nonce_url(admin_url('admin-post.php?action=src_newsletter_export'), 'src_newsletter_export');
    echo '<div class="wrap"><h1>Newsletter Subscribers</h1><p>' . esc_html(number_format_i18n($total)) . ' subscribers</p>';
    echo '<p><a class="button button-secondary" href="' . esc_url($export_url) . '">Export CSV</a></p>';
    echo '<table class="widefat striped"><thead><tr><th>Email</th><th>Subscribed (UTC)</th><th>Source</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr><td>' . esc_html($row->email) . '</td><td>' . esc_html($row->subscribed_at) . '</td><td>' . esc_html($row->source) . '</td></tr>';
    }
    if (!$rows) echo '<tr><td colspan="3">No subscribers yet.</td></tr>';
    echo '</tbody></table>';
    if ($total > $per_page) {
        echo '<div class="tablenav"><div class="tablenav-pages">';
        echo wp_kses_post(paginate_links([
            'base' => add_query_arg('paged', '%#%', admin_url('tools.php?page=src-newsletter-subscribers')),
            'current' => $page,
            'total' => (int) ceil($total / $per_page),
        ]));
        echo '</div></div>';
    }
    echo '</div>';
}

function src_newsletter_export() {
    if (!current_user_can('manage_options')) wp_die('You do not have permission to export subscribers.');
    check_admin_referer('src_newsletter_export');
    global $wpdb;
    $table = src_newsletter_table();
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="src-newsletter-subscribers.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Email', 'Subscribed (UTC)', 'Source']);
    $offset = 0;
    do {
        $rows = $wpdb->get_results($wpdb->prepare("SELECT email, subscribed_at, source FROM $table ORDER BY id ASC LIMIT %d OFFSET %d", 500, $offset), ARRAY_N);
        foreach ($rows as $row) fputcsv($output, $row);
        $offset += count($rows);
    } while (count($rows) === 500);
    fclose($output);
    exit;
}
add_action('admin_post_src_newsletter_export', 'src_newsletter_export');
