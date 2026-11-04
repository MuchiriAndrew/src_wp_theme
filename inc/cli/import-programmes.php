<?php
if (!defined('ABSPATH')) exit;

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('src import-programmes', static function ($args, $assoc_args) {
        $path = get_template_directory() . '/data/programmes-v3.json';
        $programmes = json_decode((string) file_get_contents($path), true);
        if (!is_array($programmes)) WP_CLI::error('Could not read data/programmes-v3.json.');

        $dry_run = isset($assoc_args['dry-run']);
        $overwrite = isset($assoc_args['overwrite']);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        foreach ($programmes as $order => $programme) {
            $slug = sanitize_title($programme['slug'] ?? '');
            if ($slug === '') continue;
            $hash = hash('sha256', wp_json_encode($programme));
            $existing = get_page_by_path($slug, OBJECT, 'programme');
            if ($existing && get_post_meta($existing->ID, '_src_v3_import_hash', true) === $hash && !$overwrite) {
                $skipped++;
                continue;
            }
            if ($existing && get_post_meta($existing->ID, '_src_v3_import_hash', true) && !$overwrite) {
                WP_CLI::warning("{$slug}: skipped because it has changed since the previous import; use --overwrite to replace imported fields.");
                $skipped++;
                continue;
            }
            if ($dry_run) {
                $existing ? $updated++ : $created++;
                WP_CLI::log(($existing ? 'Would update: ' : 'Would create: ') . $slug);
                continue;
            }

            $post_data = [
                'post_type' => 'programme',
                'post_name' => $slug,
                'post_title' => sanitize_text_field($programme['name'] ?? $programme['heroTitle'] ?? $slug),
                'post_excerpt' => sanitize_textarea_field($programme['audience'] ?? ''),
                'post_content' => sanitize_textarea_field($programme['outcomes'] ?? ''),
                'post_status' => !empty($programme['published']) ? 'publish' : 'draft',
                'menu_order' => $order + 1,
            ];
            if ($existing) $post_data['ID'] = $existing->ID;
            $post_id = wp_insert_post(wp_slash($post_data), true);
            if (is_wp_error($post_id)) {
                WP_CLI::warning($slug . ': ' . $post_id->get_error_message());
                continue;
            }

            $mapping = [
                'shortName' => 'programme_short_name', 'heroTitle' => 'programme_hero_title', 'heroSubtitle' => 'programme_hero_subtitle',
                'status' => 'programme_status', 'enrolment' => 'programme_enrolment', 'type' => 'programme_type', 'pillar' => 'programme_pillar',
                'tags' => 'programme_tags', 'funders' => 'programme_funders', 'partners' => 'programme_partners', 'logos' => 'programme_logos',
                'reach' => 'programme_reach', 'audience' => 'programme_audience', 'outcomes' => 'programme_outcomes', 'duration' => 'programme_duration',
                'dates' => 'programme_dates', 'delivery' => 'programme_delivery', 'journey' => 'programme_journey', 'fee' => 'programme_fee',
                'code' => 'programme_code', 'note' => 'programme_note', 'nextCohortDate' => 'programme_next_cohort_date',
                'nextCohortLabel' => 'programme_next_cohort_label',
            ];
            foreach ($mapping as $source_key => $meta_key) {
                if (!array_key_exists($source_key, $programme)) continue;
                $value = $programme[$source_key];
                if (is_array($value)) $value = array_values(array_map('sanitize_text_field', $value));
                elseif (is_null($value)) $value = '';
                elseif (in_array($source_key, ['audience', 'outcomes', 'delivery'], true)) $value = sanitize_textarea_field((string) $value);
                else $value = sanitize_text_field((string) $value);
                update_post_meta($post_id, '_' . $meta_key, $value);
            }
            update_post_meta($post_id, '_programme_detail_available', ($programme['detailAvailable'] ?? true) === false ? '0' : '1');
            update_post_meta($post_id, '_programme_image_id', sanitize_text_field($programme['image'] ?? ''));
            update_post_meta($post_id, '_programme_fee_amount', '');
            $is_home_featured = ($programme['status'] ?? '') === 'current' && ($programme['enrolment'] ?? '') === 'open';
            update_post_meta($post_id, '_programme_home_featured', $is_home_featured ? '1' : '0');
            update_post_meta($post_id, '_programme_home_order', $is_home_featured ? (string) ($order + 1) : '0');
            update_post_meta($post_id, '_src_v3_imported', '1');
            update_post_meta($post_id, '_src_v3_import_hash', $hash);

            foreach (['programme_theme' => 'theme', 'programme_pillar' => 'pillar'] as $taxonomy => $source_key) {
                $term_name = (string) ($programme[$source_key] ?? '');
                if ($term_name === '') continue;
                $term = get_term_by('slug', sanitize_title($term_name), $taxonomy);
                if (!$term) {
                    $term_label = $taxonomy === 'programme_pillar'
                        ? ['bms' => 'Business Model Strengthening', 'irf' => 'Investment Readiness & Finance', 'ml' => 'Market Linkages'][$term_name] ?? $term_name
                        : $term_name;
                    $term = term_exists($term_label, $taxonomy);
                    if (!$term) $term = wp_insert_term($term_label, $taxonomy, ['slug' => sanitize_title($term_name)]);
                }
                if (is_wp_error($term)) continue;
                $term_id = is_object($term) ? (int) $term->term_id : (is_array($term) ? (int) $term['term_id'] : (int) $term);
                wp_set_object_terms($post_id, [$term_id], $taxonomy);
            }
            $county_slugs = [];
            foreach (($programme['counties'] ?? []) as $county) {
                $county_name = preg_replace('/^county-/', '', sanitize_title((string) $county));
                if ($county_name === '') continue;
                $term = get_term_by('slug', $county_name, 'programme_county');
                if (!$term) {
                    $term = wp_insert_term(ucwords(str_replace('-', ' ', $county_name)), 'programme_county', ['slug' => $county_name]);
                    if (is_wp_error($term)) continue;
                    $term_id = (int) $term['term_id'];
                } else $term_id = (int) $term->term_id;
                $county_slugs[] = $term_id;
            }
            wp_set_object_terms($post_id, $county_slugs, 'programme_county');

            $reach = (string) ($programme['reach'] ?? '');
            $country_map = [
                'Kenya' => 'KE', 'Uganda' => 'UG', 'Tanzania' => 'TZ', 'Malawi' => 'MW',
                'Zambia' => 'ZM', 'South Africa' => 'ZA', 'Senegal' => 'SN',
            ];
            $country_term_ids = [];
            foreach ($country_map as $country_name => $code) {
                if (stripos($reach, $country_name) === false && !(!empty($programme['counties']) && $country_name === 'Kenya')) continue;
                $term = get_term_by('slug', strtolower($code), 'programme_country');
                if (!$term) $term = get_term_by('name', $country_name, 'programme_country');
                if (!$term) {
                    $term = wp_insert_term($country_name, 'programme_country', ['slug' => strtolower($code)]);
                    if (!is_wp_error($term)) update_term_meta((int) $term['term_id'], 'src_country_code', $code);
                    $country_term_id = is_wp_error($term) ? 0 : (int) $term['term_id'];
                } else {
                    $country_term_id = (int) $term->term_id;
                    if (!get_term_meta($country_term_id, 'src_country_code', true)) update_term_meta($country_term_id, 'src_country_code', $code);
                }
                if ($country_term_id) $country_term_ids[] = $country_term_id;
            }
            wp_set_object_terms($post_id, $country_term_ids, 'programme_country');
            $existing ? $updated++ : $created++;
        }

        WP_CLI::success(sprintf('%s: %d created, %d updated, %d skipped.', $dry_run ? 'Dry run complete' : 'Import complete', $created, $updated, $skipped));
    });
}
