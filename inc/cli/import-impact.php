<?php
if (!defined('ABSPATH')) exit;

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('src import-impact', static function ($args, $assoc_args) {
        $path = get_template_directory() . '/data/impact-metrics-v3.json';
        $items = json_decode((string) file_get_contents($path), true);
        if (!is_array($items)) WP_CLI::error('Could not read data/impact-metrics-v3.json.');
        $dry_run = isset($assoc_args['dry-run']);
        $overwrite = isset($assoc_args['overwrite']);
        $created = $updated = $skipped = 0;
        foreach ($items as $order => $item) {
            $slug = sanitize_title($item['slug'] ?? '');
            if (!$slug) continue;
            $hash = hash('sha256', wp_json_encode($item));
            $existing = get_page_by_path($slug, OBJECT, 'impact_metric');
            if ($existing && get_post_meta($existing->ID, '_src_v3_import_hash', true) === $hash && !$overwrite) { $skipped++; continue; }
            if ($existing && get_post_meta($existing->ID, '_src_v3_import_hash', true) && !$overwrite) {
                WP_CLI::warning("{$slug}: skipped because it has changed since import; use --overwrite to replace imported fields."); $skipped++; continue;
            }
            if ($dry_run) { $existing ? $updated++ : $created++; WP_CLI::log(($existing ? 'Would update: ' : 'Would create: ') . $slug); continue; }
            $post = ['post_type' => 'impact_metric', 'post_name' => $slug, 'post_title' => sanitize_text_field($item['label'] ?? $slug), 'post_status' => 'publish', 'menu_order' => $order + 1];
            if ($existing) $post['ID'] = $existing->ID;
            $post_id = wp_insert_post(wp_slash($post), true);
            if (is_wp_error($post_id)) { WP_CLI::warning($slug . ': ' . $post_id->get_error_message()); continue; }
            foreach (['value' => 'metric_value', 'prefix' => 'metric_prefix', 'suffix' => 'metric_suffix', 'label' => 'metric_label', 'detail' => 'metric_detail', 'context' => 'metric_context', 'image' => 'metric_image', 'area' => 'metric_area', 'link' => 'metric_link'] as $source => $field) {
                $value = $item[$source] ?? '';
                if ($source === 'link' && str_starts_with((string) $value, '/')) $value = home_url($value);
                update_post_meta($post_id, '_' . $field, $value);
            }
            update_post_meta($post_id, '_metric_facts', array_values(array_map('sanitize_text_field', (array) ($item['facts'] ?? []))));
            update_post_meta($post_id, '_metric_order', (string) ($order + 1));
            update_post_meta($post_id, '_src_v3_imported', '1');
            update_post_meta($post_id, '_src_v3_import_hash', $hash);
            $existing ? $updated++ : $created++;
        }
        WP_CLI::success("Impact metrics import complete. Created: {$created}; updated: {$updated}; unchanged or skipped: {$skipped}.");
    });
}
