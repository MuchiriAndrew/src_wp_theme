<?php
if (!defined('ABSPATH')) exit;

/** Register the content types and terms needed by the v3 design. */
function src_v3_register_content_model() {
    $types = [
        'src_testimonial' => ['Testimonials', 'Testimonial', 'dashicons-format-quote'],
        'src_expert' => ['Pool of Experts', 'Expert', 'dashicons-businessperson'],
        'src_project_folder' => ['Project Folders', 'Project Folder', 'dashicons-portfolio'],
        'src_publication' => ['Publications', 'Publication', 'dashicons-media-document'],
        'src_news_item' => ['Latest News', 'News Story', 'dashicons-media-text'],
        'portfolio_company' => ['Portfolio Companies', 'Portfolio Company', 'dashicons-store'],
        'impact_metric' => ['Impact Metrics', 'Impact Metric', 'dashicons-chart-bar'],
    ];

    foreach ($types as $type => [$plural, $singular, $icon]) {
        $is_news = $type === 'src_news_item';
        register_post_type($type, [
            'labels' => [
                'name' => __($plural, 'src'),
                'singular_name' => __($singular, 'src'),
                'add_new_item' => sprintf(__('Add %s', 'src'), $singular),
                'edit_item' => sprintf(__('Edit %s', 'src'), $singular),
            ],
            'public' => $is_news,
            'publicly_queryable' => $is_news,
            'show_ui' => true,
            'show_in_rest' => true,
            'menu_icon' => $icon,
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
            'has_archive' => $is_news ? 'news' : false,
            'rewrite' => $is_news ? ['slug' => 'news', 'with_front' => false] : false,
        ]);
    }

    $taxonomies = [
        'programme_theme' => ['Programme Themes', 'Programme Theme', true, ['programme']],
        'programme_pillar' => ['Programme Pillars', 'Programme Pillar', true, ['programme']],
        'programme_county' => ['Programme Counties', 'Programme County', true, ['programme']],
        'expert_category' => ['Expert Categories', 'Expert Category', true, ['src_expert']],
        'testimonial_category' => ['Story Categories', 'Story Category', false, ['src_testimonial']],
        'partner_category' => ['Partner Categories', 'Partner Category', true, ['src_partner_item']],
        'project_theme' => ['Project Themes', 'Project Theme', true, ['src_project_folder']],
    ];

    foreach ($taxonomies as $taxonomy => [$plural, $singular, $hierarchical, $objects]) {
        register_taxonomy($taxonomy, $objects, [
            'labels' => ['name' => __($plural, 'src'), 'singular_name' => __($singular, 'src')],
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'hierarchical' => $hierarchical,
            'rewrite' => false,
        ]);
    }
}
add_action('init', 'src_v3_register_content_model', 20);

/** Fields displayed by the custom, plugin-free editor panels below. */
function src_v3_meta_schema() {
    return [
        'programme' => [
            'programme_short_name' => ['Short card name', 'text'],
            'programme_hero_title' => ['Hero title', 'text'],
            'programme_hero_subtitle' => ['Hero subtitle', 'text'],
            'programme_status' => ['Status', 'select', ['current' => 'Current', 'upcoming' => 'Upcoming', 'past' => 'Past']],
            'programme_enrolment' => ['Enrolment', 'select', ['open' => 'Open', 'partner' => 'Partner managed', 'closed' => 'Closed']],
            'programme_type' => ['Programme type', 'select', ['signature' => 'SRC Signature', 'partner' => 'Partner programme']],
            'programme_pillar' => ['Pillar key', 'select', ['bms' => 'Business Model Strengthening', 'irf' => 'Investment Readiness & Finance', 'ml' => 'Market Linkages']],
            'programme_pillar_order' => ['Order in What We Do pillar', 'number'],
            'programme_tags' => ['Tags (one per line)', 'lines'],
            'programme_funders' => ['Funders (one per line)', 'lines'],
            'programme_partners' => ['Delivery partners (one per line)', 'lines'],
            'programme_logos' => ['Logo keys (one per line)', 'lines'],
            'programme_reach' => ['Reach summary', 'text'],
            'programme_audience' => ['Who the programme serves', 'textarea'],
            'programme_outcomes' => ['Outcomes', 'textarea'],
            'programme_duration' => ['Duration', 'text'],
            'programme_dates' => ['Dates label', 'text'],
            'programme_delivery' => ['Delivery model', 'textarea'],
            'programme_journey' => ['Journey stages (one per line)', 'lines'],
            'programme_fee' => ['Fee label (editorial display only)', 'text'],
            'programme_fee_amount' => ['WooCommerce price in KES (leave blank until confirmed)', 'number'],
            'programme_next_cohort_date' => ['Next cohort date', 'date'],
            'programme_next_cohort_label' => ['Next cohort label', 'text'],
            'programme_code' => ['Programme code', 'text'],
            'programme_note' => ['Delivery note', 'textarea'],
            'programme_detail_available' => ['Detail page available', 'checkbox'],
            'programme_home_featured' => ['Feature on homepage', 'checkbox'],
            'programme_home_order' => ['Homepage order', 'number'],
            'programme_project_order' => ['Project folder order', 'number'],
            'programme_product_id' => ['Linked WooCommerce product ID', 'number'],
        ],
        'src_testimonial' => [
            'story_person' => ['Person name', 'text'], 'story_business' => ['Business and location', 'text'],
            'story_headline' => ['Story headline', 'text'], 'story_paragraphs' => ['Story paragraphs (one per line)', 'lines'],
            'story_image_url' => ['Story image URL', 'url'], 'story_video_url' => ['Video URL', 'url'],
            'story_order' => ['Display order', 'number'], 'story_featured' => ['Show on homepage', 'checkbox'],
        ],
        'src_news_item' => [
            'news_date_label' => ['Display date', 'text'], 'news_category' => ['News category', 'text'],
            'news_image_url' => ['Card image URL', 'url'], 'news_order' => ['Display order', 'number'],
            'news_featured' => ['Show on homepage', 'checkbox'], 'news_programme_slug' => ['Related programme slug', 'text'],
            'news_url' => ['Article URL (leave blank for internal story)', 'url'],
        ],
        'src_expert' => [
            'expert_role' => ['Specialty / role', 'text'], 'expert_summary' => ['Short summary', 'textarea'],
            'expert_order' => ['Display order', 'number'], 'expert_featured' => ['Featured', 'checkbox'],
            'expert_request_label' => ['Request link label', 'text'], 'expert_request_url' => ['Request link URL', 'url'],
        ],
        'src_project_folder' => [
            'folder_summary' => ['Folder summary', 'textarea'], 'folder_funders' => ['Funders (one per line)', 'lines'],
            'folder_order' => ['Display order', 'number'], 'folder_cta_url' => ['Results / stories URL', 'url'],
        ],
        'src_publication' => [
            'publication_year' => ['Year', 'number'], 'publication_pdf' => ['PDF URL', 'url'],
            'publication_flipbook' => ['Flipbook URL', 'url'], 'publication_featured' => ['Featured publication', 'checkbox'],
        ],
        'src_partner_item' => [
            'partner_name' => ['Partner display name', 'text'], 'partner_logo_url' => ['Logo image URL', 'url'],
            'partner_link' => ['Partner website URL', 'url'], 'partner_order' => ['Marquee order', 'number'],
            'partner_marquee' => ['Show in homepage partner marquee', 'checkbox'],
        ],
        'portfolio_company' => [
            'portfolio_sector' => ['Sector', 'text'], 'portfolio_location' => ['Location', 'text'],
            'portfolio_url' => ['Company website', 'url'], 'portfolio_abbreviation' => ['Card monogram', 'text'],
            'portfolio_order' => ['Display order', 'number'], 'portfolio_featured' => ['Show in portfolio', 'checkbox'],
        ],
        'impact_metric' => [
            'metric_value' => ['Target number', 'number'], 'metric_prefix' => ['Prefix', 'text'],
            'metric_suffix' => ['Suffix', 'text'], 'metric_label' => ['Label', 'text'], 'metric_detail' => ['Small supporting line', 'text'],
            'metric_context' => ['Expanded explanation', 'textarea'], 'metric_facts' => ['Detail bullets (one per line)', 'lines'],
            'metric_image' => ['Background image key or URL', 'text'],
            'metric_area' => ['Layout position', 'select', ['money' => 'Money unlocked', 'jobs' => 'Jobs created', 'ent' => 'Enterprises supported', 'coop' => 'Cooperatives & value chains', 'ctry' => 'Countries', 'assoc' => 'Associates & partners']],
            'metric_link' => ['Related link', 'url'], 'metric_order' => ['Display order', 'number'],
        ],
    ];
}

function src_v3_register_meta() {
    foreach (src_v3_meta_schema() as $post_type => $fields) {
        foreach ($fields as $key => $definition) {
            $kind = $definition[1];
            $type = $kind === 'checkbox' ? 'boolean' : (in_array($kind, ['number'], true) ? 'number' : (in_array($kind, ['lines'], true) ? 'array' : 'string'));
            $args = [
                'single' => true,
                'type' => $type,
                'show_in_rest' => $type === 'array'
                    ? ['schema' => ['type' => 'array', 'items' => ['type' => 'string']]]
                    : true,
                'auth_callback' => static fn() => current_user_can('edit_posts'),
            ];
            if ($type === 'array') $args['show_in_rest'] = ['schema' => ['type' => 'array', 'items' => ['type' => 'string']]];
            register_post_meta($post_type, '_' . $key, $args);
        }
    }
}
add_action('init', 'src_v3_register_meta', 25);

function src_v3_add_meta_boxes() {
    foreach (array_keys(src_v3_meta_schema()) as $post_type) {
        add_meta_box('src-v3-fields', __('SRC v3 Content', 'src'), 'src_v3_render_meta_box', $post_type, 'normal', 'high');
    }
}
add_action('add_meta_boxes', 'src_v3_add_meta_boxes');

function src_v3_render_meta_box($post) {
    $schema = src_v3_meta_schema()[$post->post_type] ?? [];
    wp_nonce_field('src_v3_save_meta', 'src_v3_meta_nonce');
    echo '<div class="src-v3-admin-fields">';
    foreach ($schema as $key => $definition) {
        [$label, $kind] = $definition;
        $value = get_post_meta($post->ID, '_' . $key, true);
        $id = 'src-v3-' . $key;
        echo '<p class="src-v3-admin-field"><label for="' . esc_attr($id) . '"><strong>' . esc_html($label) . '</strong></label><br>';
        if ($kind === 'checkbox') {
            echo '<input id="' . esc_attr($id) . '" name="' . esc_attr($key) . '" type="checkbox" value="1" ' . checked((bool) $value, true, false) . '>';
        } elseif ($kind === 'textarea' || $kind === 'lines') {
            $text = is_array($value) ? implode("\n", $value) : (string) $value;
            echo '<textarea id="' . esc_attr($id) . '" name="' . esc_attr($key) . '" rows="' . ($kind === 'lines' ? '5' : '4') . '" class="large-text">' . esc_textarea($text) . '</textarea>';
            if ($kind === 'lines') echo '<span class="description">One item per line.</span>';
        } elseif ($kind === 'select') {
            echo '<select id="' . esc_attr($id) . '" name="' . esc_attr($key) . '"><option value="">— Select —</option>';
            foreach ($definition[2] as $option => $option_label) echo '<option value="' . esc_attr($option) . '" ' . selected((string) $value, (string) $option, false) . '>' . esc_html($option_label) . '</option>';
            echo '</select>';
        } else {
            $input_kind = $kind === 'url' ? 'url' : ($kind === 'date' ? 'date' : ($kind === 'number' ? 'number' : 'text'));
            $step = $kind === 'number' && str_ends_with($key, 'fee_amount') ? ' step="1" min="0"' : '';
            echo '<input id="' . esc_attr($id) . '" name="' . esc_attr($key) . '" type="' . esc_attr($input_kind) . '" value="' . esc_attr(is_scalar($value) ? (string) $value : '') . '" class="large-text"' . $step . '>';
        }
        echo '</p>';
    }
    echo '</div>';
}

function src_v3_save_meta($post_id) {
    if (!isset($_POST['src_v3_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['src_v3_meta_nonce'])), 'src_v3_save_meta')) return;
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || !current_user_can('edit_post', $post_id)) return;
    $schema = src_v3_meta_schema()[get_post_type($post_id)] ?? [];
    foreach ($schema as $key => $definition) {
        [$label, $kind] = $definition;
        if ($kind === 'checkbox') {
            update_post_meta($post_id, '_' . $key, isset($_POST[$key]) ? '1' : '0');
            continue;
        }
        if (!isset($_POST[$key])) continue;
        $raw = wp_unslash($_POST[$key]);
        if ($kind === 'lines') {
            $rows = preg_split('/\r\n|\r|\n/', sanitize_textarea_field($raw));
            update_post_meta($post_id, '_' . $key, array_values(array_filter(array_map('trim', $rows), static fn($row) => $row !== '')));
        } elseif ($kind === 'textarea') {
            update_post_meta($post_id, '_' . $key, sanitize_textarea_field($raw));
        } elseif ($kind === 'url') {
            update_post_meta($post_id, '_' . $key, esc_url_raw($raw));
        } elseif ($kind === 'number') {
            $number = trim((string) $raw);
            if ($number === '') {
                delete_post_meta($post_id, '_' . $key);
            } elseif (is_numeric($number)) {
                update_post_meta($post_id, '_' . $key, max(0, (float) $number));
            }
        } else {
            update_post_meta($post_id, '_' . $key, sanitize_text_field($raw));
        }
    }
}
add_action('save_post', 'src_v3_save_meta', 20);

function src_v3_seed_terms() {
    $terms = [
        'programme_theme' => ['Resilience & Livelihoods', 'Livestock & Agribusiness', "Women's Enterprise", 'Investment & Export Readiness', 'Digital & Learning'],
        'programme_pillar' => ['bms' => 'Business Model Strengthening', 'irf' => 'Investment Readiness & Finance', 'ml' => 'Market Linkages'],
        'programme_county' => ['Baringo', 'Bomet', 'Bungoma', 'Busia', 'Elgeyo-Marakwet', 'Embu', 'Garissa', 'Homa Bay', 'Isiolo', 'Kajiado', 'Kakamega', 'Kericho', 'Kiambu', 'Kilifi', 'Kirinyaga', 'Kisii', 'Kisumu', 'Kitui', 'Kwale', 'Laikipia', 'Lamu', 'Machakos', 'Makueni', 'Mandera', 'Marsabit', 'Meru', 'Migori', 'Mombasa', 'Murang’a', 'Nairobi', 'Nakuru', 'Nandi', 'Narok', 'Nyamira', 'Nyandarua', 'Nyeri', 'Samburu', 'Siaya', 'Taita-Taveta', 'Tana River', 'Tharaka-Nithi', 'Trans Nzoia', 'Turkana', 'Uasin Gishu', 'Vihiga', 'Wajir', 'West Pokot'],
        'expert_category' => ['Technical / Value-chain', 'Financial & Governance', 'Marketing & Growth', 'Compliance & Safety'],
    ];
    foreach ($terms as $taxonomy => $names) {
        foreach ($names as $key => $value) {
            $name = is_string($key) ? $value : $value;
            $slug = is_string($key) ? $key : sanitize_title($name);
            if (!term_exists($slug, $taxonomy)) wp_insert_term($name, $taxonomy, ['slug' => $slug]);
        }
    }
}
add_action('init', 'src_v3_seed_terms', 30);

/** Keep the programme archive limited to the editorially approved v3 catalogue. */
function src_v3_programme_archive_query($query) {
    if (is_admin() || !$query->is_main_query() || !$query->is_post_type_archive('programme')) return;
    $meta_query = (array) $query->get('meta_query');
    $meta_query[] = ['key' => '_src_v3_imported', 'value' => '1', 'compare' => '='];
    if (!empty($_GET['status']) && in_array($_GET['status'], ['current', 'upcoming', 'past'], true)) {
        $meta_query[] = ['key' => '_programme_status', 'value' => sanitize_key(wp_unslash($_GET['status'])), 'compare' => '='];
    }
    if (!empty($_GET['signature']) && $_GET['signature'] === '1') {
        $meta_query[] = ['key' => '_programme_type', 'value' => 'signature', 'compare' => '='];
    }
    $query->set('meta_query', $meta_query);
    if (!empty($_GET['theme'])) {
        $query->set('tax_query', [[
            'taxonomy' => 'programme_theme',
            'field' => 'slug',
            'terms' => sanitize_title(wp_unslash($_GET['theme'])),
        ]]);
    }
    $query->set('posts_per_page', -1);
    $query->set('meta_key', '_programme_status');
    $query->set('orderby', ['menu_order' => 'ASC', 'title' => 'ASC']);
}
add_action('pre_get_posts', 'src_v3_programme_archive_query', 30);
