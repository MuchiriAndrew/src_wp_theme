<?php
if (!defined('ABSPATH')) exit;

define('SRC_VERSION', '3.1.0');

require_once get_template_directory() . '/inc/v3-content-model.php';
require_once get_template_directory() . '/inc/cli/import-programmes.php';
require_once get_template_directory() . '/inc/cli/import-impact.php';
require_once get_template_directory() . '/inc/commerce/programme-products.php';

function src_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', ['height' => 64, 'width' => 220, 'flex-height' => true, 'flex-width' => true]);
    register_nav_menus(['primary' => __('Primary Navigation', 'src'), 'footer' => __('Footer Navigation', 'src')]);
}
add_action('after_setup_theme', 'src_setup');

function src_assets() {
    wp_enqueue_style('src-style', get_stylesheet_uri(), [], SRC_VERSION);
    wp_enqueue_script('src-site', get_template_directory_uri() . '/assets/js/site.js', [], SRC_VERSION, true);
    if (is_post_type_archive('programme')) {
        wp_enqueue_script('src-programmes', get_template_directory_uri() . '/assets/js/programmes.js', [], SRC_VERSION, true);
    }
    if (is_front_page()) wp_enqueue_script('src-home-v3', get_template_directory_uri() . '/assets/js/home-v3.js', [], SRC_VERSION, true);
}
add_action('wp_enqueue_scripts', 'src_assets');

function src_register_content() {
    register_post_type('project', [
        'labels' => ['name' => 'Projects', 'singular_name' => 'Project', 'add_new_item' => 'Add Project'],
        'public' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-chart-area', 'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'has_archive' => true, 'rewrite' => ['slug' => 'our-work'],
    ]);
    register_taxonomy('sector', 'project', ['labels' => ['name' => 'Sectors', 'singular_name' => 'Sector'], 'public' => true, 'show_in_rest' => true, 'hierarchical' => true, 'rewrite' => ['slug' => 'sector']]);
    register_post_type('src_sector_item', [
        'labels' => ['name' => 'Homepage Sectors', 'singular_name' => 'Homepage Sector', 'add_new_item' => 'Add Homepage Sector'],
        'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-location-alt',
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'], 'rewrite' => false,
    ]);
    register_post_type('src_pillar_item', [
        'labels' => ['name' => 'Homepage Pillars', 'singular_name' => 'Homepage Pillar', 'add_new_item' => 'Add Homepage Pillar'],
        'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-awards',
        'supports' => ['title', 'editor', 'page-attributes'], 'rewrite' => false,
    ]);
    register_post_type('src_audience_item', [
        'labels' => ['name' => 'Homepage Audiences', 'singular_name' => 'Homepage Audience', 'add_new_item' => 'Add Homepage Audience'],
        'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-groups',
        'supports' => ['title', 'editor', 'page-attributes'], 'rewrite' => false,
    ]);
    register_post_type('src_partner_item', [
        'labels' => ['name' => 'Homepage Partners', 'singular_name' => 'Homepage Partner', 'add_new_item' => 'Add Homepage Partner'],
        'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-building',
        'supports' => ['title', 'thumbnail', 'page-attributes'], 'rewrite' => false,
    ]);
    register_post_type('src_stat_item', [
        'labels' => ['name' => 'Homepage Stats', 'singular_name' => 'Homepage Stat', 'add_new_item' => 'Add Homepage Stat'],
        'public' => false, 'show_ui' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-chart-bar',
        'supports' => ['title', 'page-attributes'], 'rewrite' => false,
    ]);
    register_post_type('programme', [
        'labels' => ['name' => 'Programmes', 'singular_name' => 'Programme', 'add_new_item' => 'Add Programme'],
        'public' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-welcome-learn-more', 'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'has_archive' => true, 'rewrite' => ['slug' => 'programmes'],
    ]);
    register_taxonomy('programme_type', 'programme', ['labels' => ['name' => 'Programme Types', 'singular_name' => 'Programme Type'], 'public' => true, 'show_in_rest' => true, 'hierarchical' => true, 'rewrite' => ['slug' => 'programme-type']]);
    register_taxonomy('programme_country', 'programme', [
        'labels' => [
            'name' => 'Programme Countries',
            'singular_name' => 'Programme Country',
            'search_items' => 'Search Programme Countries',
            'all_items' => 'All Programme Countries',
            'edit_item' => 'Edit Programme Country',
            'update_item' => 'Update Programme Country',
            'add_new_item' => 'Add Programme Country',
            'new_item_name' => 'New Programme Country',
        ],
        'public' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'rewrite' => ['slug' => 'programme-country'],
    ]);
    register_post_type('team_member', [
        'labels' => ['name' => 'Team', 'singular_name' => 'Team Member', 'add_new_item' => 'Add Team Member'],
        'public' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-groups', 'supports' => ['title', 'editor', 'thumbnail'],
        'has_archive' => true, 'rewrite' => ['slug' => 'leadership'],
    ]);
    register_post_type('job', [
        'labels' => ['name' => 'Careers', 'singular_name' => 'Job', 'add_new_item' => 'Add Job Opening'],
        'public' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-businessperson', 'supports' => ['title', 'editor', 'excerpt'],
        'has_archive' => true, 'rewrite' => ['slug' => 'careers'],
    ]);
    register_taxonomy('job_department', 'job', ['labels' => ['name' => 'Departments', 'singular_name' => 'Department'], 'public' => true, 'show_in_rest' => true, 'hierarchical' => true]);
}
add_action('init', 'src_register_content');

function src_meta_box($post, $fields) {
    wp_nonce_field('src_meta_save', 'src_meta_nonce');
    echo '<div class="src-meta-fields">';
    foreach ($fields as $key => $definition) {
        $label = is_array($definition) ? ($definition[0] ?? $key) : $definition;
        $type = is_array($definition) ? ($definition[1] ?? 'text') : 'text';
        $description = is_array($definition) ? ($definition[2] ?? '') : '';
        $value = get_post_meta($post->ID, '_' . $key, true);
        if ($type === 'textarea') {
            printf('<p><label for="%1$s"><strong>%2$s</strong></label><br><textarea style="width:100%%;min-height:110px" id="%1$s" name="%1$s">%3$s</textarea>%4$s</p>', esc_attr($key), esc_html($label), esc_textarea($value), $description ? '<span class="description">' . esc_html($description) . '</span>' : '');
        } elseif ($type === 'checkbox') {
            printf('<p><label><input id="%1$s" name="%1$s" type="checkbox" value="1"%2$s> <strong>%3$s</strong></label>%4$s</p>', esc_attr($key), checked($value, '1', false), esc_html($label), $description ? '<br><span class="description">' . esc_html($description) . '</span>' : '');
        } else {
            printf('<p><label for="%1$s"><strong>%2$s</strong></label><br><input style="width:100%%" id="%1$s" name="%1$s" type="%4$s" value="%3$s">%5$s</p>', esc_attr($key), esc_html($label), esc_attr($value), esc_attr($type), $description ? '<br><span class="description">' . esc_html($description) . '</span>' : '');
        }
    }
    echo '</div>';
}
function src_add_meta_boxes() {
    add_meta_box('src-project-meta', 'Project Details', fn($p) => src_meta_box($p, [
        'project_partner'=>'Partner / Funder', 'project_funder'=>'Modal funder label', 'project_location'=>'Location', 'project_year'=>'Year', 'project_impact'=>'Impact statement',
        'project_span'=>['Homepage tile size: 1 or 2', 'number'], 'project_home_order'=>['Homepage order', 'number'], 'project_home_featured'=>['Show on homepage', 'checkbox'],
        'project_external_url'=>'External project website', 'project_modal_description'=>['Modal description', 'textarea'], 'project_image_url'=>'Fallback image URL',
        'project_stats'=>['Overview stats, one per line as Label | Value', 'textarea'], 'project_impacts'=>['Impact results, one per line as Heading | Description', 'textarea'],
    ]), 'project', 'normal');
    add_meta_box('src-programme-meta', 'Programme Details', fn($p) => src_meta_box($p, [
        'programme_status'=>'Status: enrolling, upcoming, or closed', 'programme_partner'=>'Partner / Funder', 'programme_duration'=>'Duration', 'programme_start'=>'Next start date', 'programme_fee'=>'Fee', 'programme_location'=>'Location', 'programme_spots'=>'Available spots',
        'programme_home_order'=>['Homepage order', 'number'], 'programme_home_featured'=>['Show on homepage', 'checkbox'], 'programme_image_url'=>'Fallback image URL',
    ]), 'programme', 'normal');
    add_meta_box('src-team-meta', 'Team Details', fn($p) => src_meta_box($p, [
        'team_role'=>'Role', 'team_quote'=>'Short quote / bio', 'team_category'=>'Category: leadership, board, or staff', 'team_panel_color'=>'Homepage panel colour: red, charcoal, or orange', 'team_home_order'=>['Homepage order', 'number'], 'team_home_featured'=>['Show on homepage', 'checkbox'], 'team_image_url'=>'Fallback image URL',
    ]), 'team_member', 'normal');
    add_meta_box('src-job-meta', 'Job Details', fn($p) => src_meta_box($p, ['job_location'=>'Location', 'job_type'=>'Type: full-time, contract, or consultancy', 'job_department'=>'Department', 'job_deadline'=>'Application deadline']), 'job', 'normal');
    add_meta_box('src-sector-item-meta', 'Homepage Sector Details', fn($p) => src_meta_box($p, ['sector_number'=>'Display number', 'sector_home_order'=>['Homepage order', 'number'], 'sector_image_url'=>'Fallback image URL', 'sector_link'=>'Explore link']), 'src_sector_item', 'normal');
    add_meta_box('src-pillar-item-meta', 'Homepage Pillar Details', fn($p) => src_meta_box($p, ['pillar_number'=>'Display number', 'pillar_home_order'=>['Homepage order', 'number'], 'pillar_link'=>'Learn more link']), 'src_pillar_item', 'normal');
    add_meta_box('src-audience-item-meta', 'Homepage Audience Details', fn($p) => src_meta_box($p, ['audience_home_order'=>['Homepage order', 'number'], 'audience_link'=>'Audience link']), 'src_audience_item', 'normal');
    add_meta_box('src-partner-item-meta', 'Homepage Partner Details', fn($p) => src_meta_box($p, ['partner_type'=>'Tile type: logo, image, or cta', 'partner_abbreviation'=>'Short label (use a line break only in the full-name field if desired)', 'partner_name'=>'Full name', 'partner_color'=>'Logo tile colour: red, charcoal, or orange', 'partner_image_url'=>'Fallback image URL', 'partner_span'=>['Tile span: 1 or 2', 'number'], 'partner_home_order'=>['Homepage order', 'number'], 'partner_link'=>'Partner page link']), 'src_partner_item', 'normal');
    add_meta_box('src-stat-item-meta', 'Homepage Stat Details', fn($p) => src_meta_box($p, ['stat_value'=>'Value', 'stat_label'=>'Label', 'stat_home_order'=>['Homepage order', 'number'], 'stat_home_enabled'=>['Show on homepage', 'checkbox']]), 'src_stat_item', 'normal');
    add_meta_box('src-page-content-meta', 'SRC Page Content', fn($p) => src_meta_box($p, src_page_field_schema()), 'page', 'normal');
}
add_action('add_meta_boxes', 'src_add_meta_boxes');
function src_save_meta($post_id) {
    if (!isset($_POST['src_meta_nonce']) || !wp_verify_nonce($_POST['src_meta_nonce'], 'src_meta_save') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) return;
    if (!current_user_can('edit_post', $post_id)) return;
    $keys = array_merge(['project_partner','project_funder','project_location','project_year','project_impact','project_span','project_home_order','project_home_featured','project_external_url','project_modal_description','project_image_url','project_stats','project_impacts','programme_status','programme_partner','programme_duration','programme_start','programme_fee','programme_location','programme_spots','programme_home_order','programme_home_featured','programme_image_url','team_role','team_quote','team_category','team_panel_color','team_home_order','team_home_featured','team_image_url','job_location','job_type','job_department','job_deadline','sector_number','sector_home_order','sector_image_url','sector_link','pillar_number','pillar_home_order','pillar_link','audience_home_order','audience_link','partner_type','partner_abbreviation','partner_name','partner_color','partner_image_url','partner_span','partner_home_order','partner_link','stat_value','stat_label','stat_home_order','stat_home_enabled'], array_keys(src_page_field_schema()));
    $checkboxes = ['project_home_featured','programme_home_featured','team_home_featured','stat_home_enabled'];
    foreach ($keys as $key) {
        if (in_array($key, $checkboxes, true)) {
            update_post_meta($post_id, '_' . $key, isset($_POST[$key]) ? '1' : '0');
            continue;
        }
        if (!isset($_POST[$key])) continue;
        $value = wp_unslash($_POST[$key]);
        $textarea_keys = array_keys(array_filter(src_page_field_schema(), static fn($definition) => ($definition[1] ?? '') === 'textarea'));
        $value = in_array($key, array_merge(['project_stats','project_impacts','project_modal_description'], $textarea_keys), true) ? sanitize_textarea_field($value) : sanitize_text_field($value);
        update_post_meta($post_id, '_' . $key, $value);
    }
}
add_action('save_post', 'src_save_meta');

function src_image_url($post_id, $fallback) {
    $featured = get_the_post_thumbnail_url($post_id, 'large');
    if ($featured) return $featured;
    $programme_image = trim((string) get_post_meta($post_id, '_programme_image_id', true));
    if ($programme_image !== '') {
        if (preg_match('#^https?://#i', $programme_image)) return esc_url_raw($programme_image);
        return 'https://images.unsplash.com/' . rawurlencode($programme_image) . '?w=1200&h=800&fit=crop&auto=format&q=82';
    }
    foreach (['_project_image_url', '_programme_image_url', '_team_image_url', '_sector_image_url', '_expert_image_url'] as $key) {
        $url = trim((string) get_post_meta($post_id, $key, true));
        if ($url !== '') return esc_url_raw($url);
    }
    return $fallback;
}
function src_page_url($slug) { $page = get_page_by_path($slug); return $page ? get_permalink($page) : home_url('/' . trim($slug, '/') . '/'); }
function src_field($key, $post_id = null, $fallback = '') { $value = get_post_meta($post_id ?: get_the_ID(), '_' . $key, true); return $value !== '' ? $value : $fallback; }
function src_option($key, $fallback = '') { $settings = get_option('src_settings', []); return isset($settings[$key]) && $settings[$key] !== '' ? $settings[$key] : $fallback; }
function src_parse_pairs($value) {
    $pairs = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $value) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = array_map('trim', explode('|', $line, 2));
        $pairs[] = [$parts[0], $parts[1] ?? ''];
    }
    return $pairs;
}
function src_parse_rows($value) {
    $rows = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $value) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $rows[] = array_map('trim', explode('|', $line));
    }
    return $rows;
}
function src_page_field_schema() {
    return [
        'page_hero_title' => ['Hero title', 'text'], 'page_hero_eyebrow' => ['Hero eyebrow', 'text'], 'page_hero_intro' => ['Hero introduction', 'textarea'], 'page_hero_image_url' => ['Hero image URL', 'text'],
        'page_story_eyebrow' => ['Story eyebrow', 'text'], 'page_story_heading' => ['Story heading', 'text'], 'page_story_body' => ['Story body, one paragraph per blank line', 'textarea'], 'page_story_image_url' => ['Story image URL', 'text'], 'page_story_stat' => ['Story stat', 'text'], 'page_story_stat_label' => ['Story stat label', 'text'], 'page_pillars' => ['Pillars, one per line as Label | Description', 'textarea'],
        'page_values_eyebrow' => ['Values eyebrow', 'text'], 'page_values_heading' => ['Values heading', 'text'], 'page_values' => ['Values, one per line as Title | Description', 'textarea'],
        'page_capabilities_eyebrow' => ['Capabilities eyebrow', 'text'], 'page_capabilities_heading' => ['Capabilities heading', 'text'], 'page_capabilities_intro' => ['Capabilities introduction', 'textarea'], 'page_capabilities_cards' => ['Capability cards, one per line as Title | Description | Image URL', 'textarea'], 'page_capabilities_steps' => ['Capability steps, one per line as Title | Description', 'textarea'],
        'page_contact_eyebrow' => ['Contact eyebrow', 'text'], 'page_contact_heading' => ['Contact heading', 'text'], 'page_contact_intro' => ['Contact introduction', 'textarea'], 'page_contact_email' => ['Contact email', 'email'], 'page_contact_city' => ['Contact city', 'text'], 'page_contact_coverage' => ['Contact coverage line', 'text'],
        'page_careers_eyebrow' => ['Careers eyebrow', 'text'], 'page_careers_heading' => ['Careers heading', 'text'], 'page_application_email' => ['Applications email', 'email'],
    ];
}
function src_page_field($key, $post_id = null, $fallback = '') { return src_field($key, $post_id ?: get_queried_object_id(), $fallback); }
function src_ordered_posts($post_type, $meta_key, $limit = -1) {
    $stored_key = '_' . ltrim($meta_key, '_');
    $posts = get_posts(['post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => $limit, 'orderby' => 'meta_value_num', 'meta_key' => $stored_key, 'order' => 'ASC']);
    usort($posts, static function ($a, $b) use ($meta_key) {
        $a_order = (int) get_post_meta($a->ID, '_' . $meta_key, true);
        $b_order = (int) get_post_meta($b->ID, '_' . $meta_key, true);
        return ($a_order <=> $b_order) ?: strcasecmp($a->post_title, $b->post_title);
    });
    return $posts;
}
function src_menu_fallback() { echo '<a href="' . esc_url(home_url('/')) . '">Home</a><a href="' . esc_url(src_page_url('about')) . '">About</a><a href="' . esc_url(src_page_url('what-we-do')) . '">What We Do</a><a href="' . esc_url(get_post_type_archive_link('project')) . '">Our Work</a><a href="' . esc_url(get_post_type_archive_link('programme')) . '">Programmes</a><a href="' . esc_url(src_page_url('contact')) . '">Contact</a>'; }

function src_settings_fields() {
    return [
        'hero_meta' => ['Hero meta line', 'text'], 'hero_title' => ['Hero title, use a new line for each line', 'textarea'], 'hero_accent_line' => ['Hero accent line', 'text'], 'hero_copy' => ['Hero description', 'textarea'], 'hero_image_url' => ['Hero image URL', 'text'], 'header_cta_kicker' => ['Header CTA kicker', 'text'], 'header_cta_label' => ['Header CTA label', 'text'],
        'sector_eyebrow' => ['Sectors eyebrow', 'text'], 'sector_heading' => ['Sectors heading', 'text'], 'sector_intro' => ['Sectors introduction', 'textarea'],
        'audience_eyebrow' => ['Audience eyebrow', 'text'], 'audience_heading' => ['Audience heading', 'text'], 'footprint_eyebrow' => ['Footprint eyebrow', 'text'], 'footprint_heading' => ['Footprint heading', 'text'], 'partners_eyebrow' => ['Partners eyebrow', 'text'], 'partners_heading' => ['Partners heading', 'text'],
        'projects_eyebrow' => ['Projects eyebrow', 'text'], 'projects_heading' => ['Projects heading', 'text'], 'team_eyebrow' => ['Team eyebrow', 'text'], 'team_heading' => ['Team heading', 'text'],
        'programmes_eyebrow' => ['Programmes eyebrow', 'text'], 'programmes_heading' => ['Programmes heading', 'text'], 'quote_eyebrow' => ['Quote eyebrow', 'text'], 'quote_text' => ['Quote text', 'textarea'], 'quote_attribution' => ['Quote attribution', 'text'], 'quote_summary' => ['Quote summary', 'textarea'],
        'newsletter_eyebrow' => ['Newsletter eyebrow', 'text'], 'newsletter_heading' => ['Newsletter heading', 'textarea'], 'site_description' => ['Footer description', 'textarea'], 'county_footprint' => ['County footprint', 'textarea'], 'contact_city' => ['Contact city', 'text'], 'contact_email' => ['Contact email', 'email'], 'contact_phone' => ['Contact phone', 'text'],
        'social_linkedin' => ['LinkedIn URL', 'url'], 'social_x' => ['X URL', 'url'], 'social_facebook' => ['Facebook URL', 'url'], 'social_youtube' => ['YouTube URL', 'url'],
    ];
}
function src_settings_defaults() {
    return [
        'hero_meta' => 'Est. 2012 · Nairobi, Kenya · 15+ Counties', 'hero_title' => "Building Enterprises\nFrom Good\nto Great.", 'hero_accent_line' => 'From Good', 'hero_copy' => 'SRC partners with donors, financial institutions and commercial enterprises to unlock growth for SMEs and cooperatives across Kenya and East Africa.', 'hero_image_url' => 'https://images.unsplash.com/photo-1687422808311-a776f467a468?w=1800&h=1000&fit=crop&auto=format', 'header_cta_kicker' => 'Start a conversation', 'header_cta_label' => 'Partner with us',
        'sector_eyebrow' => 'Value chains', 'sector_heading' => 'Sectors We\nWork Across', 'sector_intro' => "From dairy and poultry to livestock markets — SRC brings deep sector knowledge to enterprise development across Kenya's arid and semi-arid counties.",
        'audience_eyebrow' => 'Thematic focus', 'audience_heading' => 'Who We Serve', 'footprint_eyebrow' => 'Where we work', 'footprint_heading' => 'Our Footprint', 'partners_eyebrow' => 'Trusted by', 'partners_heading' => 'Leading donors, NGOs and enterprises', 'projects_eyebrow' => 'Impact in action', 'projects_heading' => 'Our Projects', 'team_eyebrow' => 'The people behind SRC', 'team_heading' => 'Meet the Team', 'programmes_eyebrow' => 'Build your business', 'programmes_heading' => "Ongoing & Upcoming\nProgrammes", 'quote_eyebrow' => 'From our programmes', 'quote_text' => '“SRC helped us go from a struggling cooperative to a bankable enterprise — in 18 months.”', 'quote_attribution' => 'Women-led dairy cooperative, Isiolo County', 'quote_summary' => 'SRC has supported 2,500+ women entrepreneurs, unlocked $2M+ in SME financing, and operated across 5 African countries.',
        'newsletter_eyebrow' => 'Stay connected', 'newsletter_heading' => "Building enterprises from Good to Great\nsince 2012.", 'site_description' => 'Nairobi-based enterprise support organisation. Building SMEs from Good to Great across Kenya and East Africa.', 'county_footprint' => 'Nairobi · Mombasa · Kisumu · Nakuru · Garissa · Marsabit · Turkana · Wajir · Mandera · Isiolo · Samburu · Laikipia · Tana River · Kwale · Kajiado', 'contact_city' => 'Nairobi, Kenya', 'contact_email' => 'info@srctech.co.ke', 'contact_phone' => '+254 (0) 20 000 0000', 'social_linkedin' => '', 'social_x' => '', 'social_facebook' => '', 'social_youtube' => '',
    ];
}
function src_register_settings() { register_setting('src_settings', 'src_settings', ['sanitize_callback' => static function ($input) { $clean = []; foreach (src_settings_fields() as $key => $definition) { $value = isset($input[$key]) ? wp_unslash($input[$key]) : ''; $clean[$key] = in_array($definition[1], ['textarea'], true) ? sanitize_textarea_field($value) : ($definition[1] === 'url' ? esc_url_raw($value) : sanitize_text_field($value)); } return $clean; }]); }
add_action('admin_init', 'src_register_settings');
function src_add_settings_page() { add_theme_page('SRC Content Settings', 'SRC Content Settings', 'manage_options', 'src-content-settings', 'src_render_settings_page'); }
add_action('admin_menu', 'src_add_settings_page');
function src_render_settings_page() {
    if (!current_user_can('manage_options')) return;
    $fields = src_settings_fields(); $settings = array_merge(src_settings_defaults(), get_option('src_settings', []));
    echo '<div class="wrap"><h1>SRC Content Settings</h1><p>Manage homepage copy, newsletter content, footer contact details, and social links. Repeatable homepage content is managed under the Homepage content menus.</p><form method="post" action="options.php">';
    settings_fields('src_settings');
    foreach ([['Homepage hero and header', ['hero_meta','hero_title','hero_accent_line','hero_copy','hero_image_url','header_cta_kicker','header_cta_label']], ['Homepage section headings', ['sector_eyebrow','sector_heading','sector_intro','audience_eyebrow','audience_heading','footprint_eyebrow','footprint_heading','partners_eyebrow','partners_heading','projects_eyebrow','projects_heading','team_eyebrow','team_heading','programmes_eyebrow','programmes_heading']], ['Homepage quote', ['quote_eyebrow','quote_text','quote_attribution','quote_summary']], ['Newsletter and footer', ['newsletter_eyebrow','newsletter_heading','site_description','county_footprint','contact_city','contact_email','contact_phone','social_linkedin','social_x','social_facebook','social_youtube']]] as [$heading, $keys]) {
        echo '<h2>' . esc_html($heading) . '</h2><table class="form-table" role="presentation">';
        foreach ($keys as $key) { [$label, $type] = $fields[$key]; $value = $settings[$key] ?? ''; echo '<tr><th scope="row"><label for="src-setting-' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td>'; if ($type === 'textarea') printf('<textarea class="large-text" rows="3" id="src-setting-%1$s" name="src_settings[%1$s]">%2$s</textarea>', esc_attr($key), esc_textarea($value)); else printf('<input class="regular-text" type="%1$s" id="src-setting-%2$s" name="src_settings[%2$s]" value="%3$s">', esc_attr($type), esc_attr($key), esc_attr($value)); echo '</td></tr>'; }
        echo '</table>';
    }
    submit_button('Save SRC Content Settings'); echo '</form></div>';
}

function src_country_field_schema() {
    return [
        'src_country_code' => ['Map code', 'Two-letter ISO/SVG code, for example KE or TZ.', 'text'],
        'src_country_summary' => ['Country summary', 'Short line shown below the country name.', 'text'],
        'src_country_order' => ['Display order', 'Lower numbers appear first in the legend.', 'number'],
        'src_country_scale' => ['Selected map scale', 'Zoom used after selection, for example 1.56.', 'number'],
        'src_country_x' => ['Selected map X offset', 'Horizontal offset including %, for example -12%.', 'text'],
        'src_country_y' => ['Selected map Y offset', 'Vertical offset including %, for example 7%.', 'text'],
    ];
}

function src_country_add_fields() {
    wp_nonce_field('src_country_meta', 'src_country_meta_nonce');
    foreach (src_country_field_schema() as $key => [$label, $description, $type]) {
        $step = $key === 'src_country_scale' ? ' step="0.01" min="1" max="3"' : '';
        printf('<div class="form-field"><label for="%1$s">%2$s</label><input id="%1$s" name="%1$s" type="%3$s"%4$s><p>%5$s</p></div>', esc_attr($key), esc_html($label), esc_attr($type), $step, esc_html($description));
    }
    echo '<div class="form-field"><label><input name="src_country_hq" type="checkbox" value="1"> Headquarters country</label><p>Adds the HQ marker in the map legend.</p></div>';
}
add_action('programme_country_add_form_fields', 'src_country_add_fields');

function src_country_edit_fields($term) {
    wp_nonce_field('src_country_meta', 'src_country_meta_nonce');
    foreach (src_country_field_schema() as $key => [$label, $description, $type]) {
        $value = get_term_meta($term->term_id, $key, true);
        $step = $key === 'src_country_scale' ? ' step="0.01" min="1" max="3"' : '';
        printf('<tr class="form-field"><th><label for="%1$s">%2$s</label></th><td><input id="%1$s" name="%1$s" type="%3$s" value="%4$s"%5$s><p class="description">%6$s</p></td></tr>', esc_attr($key), esc_html($label), esc_attr($type), esc_attr($value), $step, esc_html($description));
    }
    $checked = get_term_meta($term->term_id, 'src_country_hq', true) ? ' checked' : '';
    printf('<tr class="form-field"><th>Headquarters</th><td><label><input name="src_country_hq" type="checkbox" value="1"%s> Headquarters country</label><p class="description">Adds the HQ marker in the map legend.</p></td></tr>', $checked);
}
add_action('programme_country_edit_form_fields', 'src_country_edit_fields');

function src_save_country_fields($term_id) {
    if (!isset($_POST['src_country_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['src_country_meta_nonce'])), 'src_country_meta')) return;
    if (!current_user_can('manage_categories')) return;
    foreach (array_keys(src_country_field_schema()) as $key) {
        $value = isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
        if ($key === 'src_country_code') $value = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $value), 0, 2));
        update_term_meta($term_id, $key, $value);
    }
    update_term_meta($term_id, 'src_country_hq', isset($_POST['src_country_hq']) ? '1' : '0');
}
add_action('created_programme_country', 'src_save_country_fields');
add_action('edited_programme_country', 'src_save_country_fields');

function src_get_footprint_countries() {
    $terms = get_terms(['taxonomy' => 'programme_country', 'hide_empty' => false]);
    if (is_wp_error($terms)) return [];
    $countries = [];
    foreach ($terms as $term) {
        $code = strtoupper((string) get_term_meta($term->term_id, 'src_country_code', true));
        if (!preg_match('/^[A-Z]{2}$/', $code)) continue;
        $programmes = get_posts([
            'post_type' => 'programme',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
            'meta_query' => [[
                'key' => '_src_v3_imported',
                'value' => '1',
                'compare' => '=',
            ]],
            'tax_query' => [[
                'taxonomy' => 'programme_country',
                'field' => 'term_id',
                'terms' => $term->term_id,
            ]],
        ]);
        $programme_rows = array_map(static function ($programme) {
            $partners = get_post_meta($programme->ID, '_programme_partners', true);
            $funders = get_post_meta($programme->ID, '_programme_funders', true);
            return [
                'title' => get_the_title($programme),
                'partner' => (is_array($partners) && $partners ? implode(', ', $partners) : (is_array($funders) && $funders ? implode(', ', $funders) : src_field('programme_partner', $programme->ID, ''))),
                'url' => get_permalink($programme),
            ];
        }, $programmes);
        $count = count($programme_rows);
        if (!$count) continue;
        $countries[$code] = [
            'name' => $term->name,
            'count' => sprintf(_n('%d programme', '%d programmes', $count, 'src'), $count),
            'meta' => get_term_meta($term->term_id, 'src_country_summary', true) ?: $term->description,
            'scale' => (float) (get_term_meta($term->term_id, 'src_country_scale', true) ?: 1.55),
            'x' => get_term_meta($term->term_id, 'src_country_x', true) ?: '0%',
            'y' => get_term_meta($term->term_id, 'src_country_y', true) ?: '0%',
            'hq' => (bool) get_term_meta($term->term_id, 'src_country_hq', true),
            'order' => (int) get_term_meta($term->term_id, 'src_country_order', true),
            'programmes' => $programme_rows,
        ];
    }
    uasort($countries, static fn($a, $b) => ($a['order'] <=> $b['order']) ?: strcasecmp($a['name'], $b['name']));
    return $countries;
}

function src_seed_content() {
    if (get_option('src_seeded')) return;
    $pages = [
        'about' => ['About SRC', 'A Nairobi-based enterprise support organisation building SMEs from Good to Great since 2012.'],
        'partners' => ['Clients & Partners', 'Leading donors, NGOs and enterprises building stronger ecosystems with SRC.'],
        'what-we-do' => ['What We Do', 'We unlock growth for the enterprises, cooperatives and ecosystems that power Africa.'],
        'get-involved' => ['Get Involved', 'Partner with us to build stronger enterprises, better markets and more resilient communities.'],
        'contact' => ['Contact', 'Let’s talk about the enterprise challenge you are trying to solve.'],
        'pool-of-experts' => ['Pool of Experts', 'A trusted network of technical specialists ready to support ambitious programmes.'],
    ];
    foreach ($pages as $slug => $page) if (!get_page_by_path($slug)) wp_insert_post(['post_title' => $page[0], 'post_name' => $slug, 'post_content' => $page[1], 'post_status' => 'publish', 'post_type' => 'page']);
    $home = get_page_by_path('home');
    if (!$home) $home = wp_insert_post(['post_title' => 'Home', 'post_name' => 'home', 'post_status' => 'publish', 'post_type' => 'page']);
    update_option('show_on_front', 'page'); update_option('page_on_front', is_object($home) ? $home->ID : $home);
    $menu = wp_get_nav_menu_object('SRC Primary');
    if (!$menu) {
        $menu_id = wp_create_nav_menu('SRC Primary');
        foreach ([['Home', home_url('/')], ['About', src_page_url('about')], ['What We Do', src_page_url('what-we-do')], ['Our Work', get_post_type_archive_link('project')], ['Programmes', get_post_type_archive_link('programme')], ['Contact', src_page_url('contact')]] as $item) wp_update_nav_menu_item($menu_id, 0, ['menu-item-title' => $item[0], 'menu-item-url' => $item[1], 'menu-item-status' => 'publish']);
        $locations = get_theme_mod('nav_menu_locations', []); $locations['primary'] = $menu_id; set_theme_mod('nav_menu_locations', $locations);
    }
    $seed = [
        ['project', 'Kijani Co-operative Growth Programme', 'Strengthening producer organisations and improving market access across Kenya.', ['project_partner'=>'FCDO', 'project_location'=>'Western Kenya', 'project_year'=>'2024', 'project_impact'=>'2,400 enterprises reached', 'project_image_url'=>'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=900&h=600&fit=crop&auto=format']],
        ['project', 'Hustle Economy Accelerator', 'Helping youth-led businesses turn early traction into sustainable, investable growth.', ['project_partner'=>'Mastercard Foundation', 'project_location'=>'Nairobi & Coast', 'project_year'=>'2023', 'project_impact'=>'1,100 jobs supported', 'project_image_url'=>'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=900&h=600&fit=crop&auto=format']],
        ['programme', 'Road to Growth', 'A 14-week digital business skills programme for women entrepreneurs.', ['programme_status'=>'enrolling', 'programme_duration'=>'14 weeks', 'programme_start'=>'16 Oct 2026', 'programme_fee'=>'Free', 'programme_location'=>'Nairobi + online', 'programme_image_url'=>'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=900&h=600&fit=crop&auto=format']],
        ['programme', 'Road to Markets', 'Helping SMEs build market linkages and formal offtake relationships.', ['programme_status'=>'upcoming', 'programme_duration'=>'8 weeks', 'programme_start'=>'Feb 2027', 'programme_fee'=>'KES 5,000', 'programme_location'=>'Hybrid', 'programme_image_url'=>'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=900&h=600&fit=crop&auto=format']],
        ['team_member', 'Catherine Gitonga', 'Founder and CEO', ['team_role'=>'Founder & CEO', 'team_quote'=>'Over a decade building enterprise support systems across East Africa — from cooperative governance to investor readiness.', 'team_category'=>'leadership', 'team_panel_color'=>'red', 'team_image_url'=>'https://images.unsplash.com/photo-1573497019418-b400bb3ab074?w=600&h=780&fit=crop&crop=faces,top&auto=format&q=80']],
        ['team_member', 'Samuel Wamae', 'Board Chair', ['team_role'=>'Board Chair', 'team_quote'=>"Senior leader in private sector development with deep roots in Kenya's agricultural finance ecosystem.", 'team_category'=>'board', 'team_panel_color'=>'charcoal', 'team_image_url'=>'https://images.unsplash.com/photo-1614023342667-6f060e9d1e04?w=600&h=780&fit=crop&crop=faces,top&auto=format&q=80']],
    ];
    foreach ($seed as [$type, $title, $content, $meta]) { if (get_page_by_path(sanitize_title($title), OBJECT, $type)) continue; $id = wp_insert_post(['post_title'=>$title, 'post_content'=>$content, 'post_excerpt'=>$content, 'post_status'=>'publish', 'post_type'=>$type]); foreach ($meta as $key=>$value) update_post_meta($id, '_' . $key, $value); }
    $reference_seed = [
        ['project', 'Her Venture', 'A women-led enterprise development programme equipping women in arid counties with business skills, savings groups, and market linkages.', ['project_partner'=>'USAID', 'project_location'=>'Marsabit, Isiolo, Samburu', 'project_year'=>'2024', 'project_image_url'=>'https://images.unsplash.com/photo-1563132337-f159f484226c?w=900&h=600&fit=crop&auto=format']],
        ['project', 'Dairy Cooperative Strengthening', 'Supporting dairy cooperatives to improve governance, productivity and access to formal markets.', ['project_partner'=>'FCDO', 'project_location'=>'Kenya', 'project_year'=>'2024', 'project_image_url'=>'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=900&h=600&fit=crop&auto=format']],
        ['project', 'Agrovet Network Scale-Up', 'Building stronger last-mile veterinary input distribution and agro-dealer systems.', ['project_partner'=>'World Bank', 'project_location'=>'East Africa', 'project_year'=>'2023', 'project_image_url'=>'https://images.unsplash.com/photo-1606235357537-84aea24d4c4f?w=900&h=600&fit=crop&auto=format']],
        ['project', 'WASH Enterprise Development', 'Growing water, sanitation and hygiene enterprises for arid communities.', ['project_partner'=>'IKEA Foundation', 'project_location'=>'Northern Kenya', 'project_year'=>'2023', 'project_image_url'=>'https://images.unsplash.com/photo-1553775927-a071d5a6a39a?w=900&h=600&fit=crop&auto=format']],
        ['project', 'Livestock Market Systems', 'Strengthening market systems for pastoralist livestock traders in Northern Kenya.', ['project_partner'=>'Cherie Blair Foundation', 'project_location'=>'Northern Kenya', 'project_year'=>'2022', 'project_image_url'=>'https://images.unsplash.com/photo-1542838132-92c53300491e?w=900&h=600&fit=crop&auto=format']],
        ['project', 'SME Business Strategy Advisory', 'Practical strategy and investor-readiness support for growth-stage SMEs.', ['project_partner'=>'Private Sector Council', 'project_location'=>'Kenya', 'project_year'=>'2022', 'project_image_url'=>'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=900&h=600&fit=crop&auto=format']],
        ['programme', 'Investor Readiness Bootcamp', 'Intensive 5-day bootcamp to prepare growth-stage SMEs for investment.', ['programme_status'=>'enrolling', 'programme_duration'=>'5 days', 'programme_start'=>'October 14, 2026', 'programme_fee'=>'KES 15,000', 'programme_location'=>'Nairobi', 'programme_image_url'=>'https://images.unsplash.com/photo-1573167507387-6b4b98cb7c13?w=700&h=450&fit=crop&auto=format']],
        ['programme', 'Expert Consultation Session', "One-on-one 2-hour session with a sector specialist from SRC's Pool of Experts.", ['programme_status'=>'enrolling', 'programme_duration'=>'2 hours', 'programme_start'=>'Rolling — book any weekday', 'programme_fee'=>'KES 5,000', 'programme_location'=>'Nairobi / virtual', 'programme_image_url'=>'https://images.unsplash.com/photo-1614023342667-6f060e9d1e04?w=700&h=450&fit=crop&auto=format']],
        ['team_member', 'Rosemary Munyiri', 'Board Member', ['team_role'=>'Board Member', 'team_quote'=>"Champion of women's economic empowerment programmes across Kenya and the Horn of Africa.", 'team_category'=>'board', 'team_panel_color'=>'orange', 'team_image_url'=>'https://images.unsplash.com/photo-1611432579402-7037e3e2c1e4?w=600&h=780&fit=crop&crop=faces,top&auto=format&q=80']],
        ['team_member', 'Evelyn Okwach', 'Board Member', ['team_role'=>'Board Member', 'team_quote'=>'Specialist in SME market linkages and value chain finance across Sub-Saharan Africa.', 'team_category'=>'board', 'team_panel_color'=>'red', 'team_image_url'=>'https://images.unsplash.com/photo-1563132337-f159f484226c?w=600&h=780&fit=crop&crop=faces,top&auto=format&q=80']],
        ['team_member', 'Dr Samuel Muriu', 'Board Member', ['team_role'=>'Board Member', 'team_quote'=>'Academic and practitioner in enterprise development, with research spanning 5 African countries.', 'team_category'=>'board', 'team_panel_color'=>'charcoal', 'team_image_url'=>'https://images.unsplash.com/photo-1616805765352-beedbad46b2a?w=600&h=780&fit=crop&crop=faces,top&auto=format&q=80']],
    ];
    foreach ($reference_seed as [$type, $title, $content, $meta]) { if (get_page_by_path(sanitize_title($title), OBJECT, $type)) continue; $id = wp_insert_post(['post_title'=>$title, 'post_content'=>$content, 'post_excerpt'=>$content, 'post_status'=>'publish', 'post_type'=>$type]); foreach ($meta as $key=>$value) update_post_meta($id, '_' . $key, $value); }
    update_option('src_seeded', 1);
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'src_seed_content');

function src_seed_footprint_content() {
    if (get_option('src_footprint_seed_version') === '1') return;

    $countries = [
        'kenya' => ['Kenya', 'KE', 'HQ Country · 15+ Counties · Active since 2012', 10, 1, 1.56, '-12%', '7%'],
        'tanzania' => ['Tanzania', 'TZ', 'Active since 2018 · Women economic empowerment', 20, 0, 1.62, '-10%', '-1%'],
        'malawi' => ['Malawi', 'MW', 'Active since 2019 · Enterprise development', 30, 0, 1.72, '-8%', '-8%'],
        'zambia' => ['Zambia', 'ZM', 'Active since 2019 · Regional enterprise support', 40, 0, 1.66, '-2%', '-8%'],
        'south-africa' => ['South Africa', 'ZA', 'Active since 2018 · Women economic empowerment', 50, 0, 1.62, '1%', '-18%'],
        'senegal' => ['Senegal', 'SN', 'Active since 2018 · Women-led enterprise focus', 60, 0, 1.68, '18%', '13%'],
    ];
    foreach ($countries as $slug => [$name, $code, $summary, $order, $hq, $scale, $x, $y]) {
        $existing = term_exists($slug, 'programme_country');
        if (!$existing) $existing = wp_insert_term($name, 'programme_country', ['slug' => $slug]);
        if (is_wp_error($existing)) continue;
        $term_id = is_array($existing) ? (int) $existing['term_id'] : (int) $existing;
        update_term_meta($term_id, 'src_country_code', $code);
        update_term_meta($term_id, 'src_country_summary', $summary);
        update_term_meta($term_id, 'src_country_order', $order);
        update_term_meta($term_id, 'src_country_hq', $hq ? '1' : '0');
        update_term_meta($term_id, 'src_country_scale', $scale);
        update_term_meta($term_id, 'src_country_x', $x);
        update_term_meta($term_id, 'src_country_y', $y);
    }

    $programmes = [
        ['road-to-growth', 'Road to Growth', 'A 14-week digital business skills programme for women entrepreneurs.', 'Smart Regional Consultants', ['kenya']],
        ['road-to-markets', 'Road to Markets', 'Helping SMEs build market linkages and formal offtake relationships.', 'Smart Regional Consultants', ['kenya']],
        ['investor-readiness-bootcamp', 'Investor Readiness Bootcamp', 'An intensive programme preparing growth-stage SMEs for investment.', 'Smart Regional Consultants', ['kenya']],
        ['expert-consultation-session', 'Expert Consultation Session', 'One-to-one support with a specialist from the SRC Pool of Experts.', 'Smart Regional Consultants', ['kenya']],
        ['casha', 'CASHA', 'Enterprise support programme information, delivery updates and participation details.', 'BOMA + Mastercard Foundation', ['kenya']],
        ['kuza-jamii', 'Kuza Jamii', 'Enterprise support programme information, delivery updates and participation details.', 'BOMA / FCDO', ['kenya']],
        ['livestock-market-systems', 'Livestock Market Systems', 'Market-systems support for pastoralist livestock enterprises and traders.', 'USAID Feed the Future', ['kenya']],
        ['laikipia-livestock-spv', 'Laikipia Livestock SPV', 'Enterprise and investment support for the Laikipia livestock value chain.', 'World Bank / Laikipia County', ['kenya']],
        ['women-creating-wealth', 'Women Creating Wealth', 'Enterprise development support for women-led businesses across participating countries.', 'Graça Machel Trust + Mastercard Foundation', ['tanzania', 'south-africa', 'senegal']],
        ['regional-enterprise-support', 'Regional Enterprise Support', 'Regional enterprise development and business support programme.', 'Programme partner (TBC)', ['malawi', 'zambia']],
    ];
    foreach ($programmes as [$slug, $title, $content, $partner, $country_slugs]) {
        $programme = get_page_by_path($slug, OBJECT, 'programme');
        $programme_id = $programme ? (int) $programme->ID : wp_insert_post([
            'post_type' => 'programme',
            'post_status' => 'publish',
            'post_name' => $slug,
            'post_title' => $title,
            'post_content' => $content,
            'post_excerpt' => $content,
        ]);
        if (!$programme_id || is_wp_error($programme_id)) continue;
        if (!get_post_meta($programme_id, '_programme_partner', true)) update_post_meta($programme_id, '_programme_partner', $partner);
        wp_set_object_terms($programme_id, $country_slugs, 'programme_country', true);
    }

    update_option('src_footprint_seed_version', '1');
}
add_action('init', 'src_seed_footprint_content', 30);

function src_upsert_home_record($type, $slug, $title, $content = '', $meta = []) {
    $post = get_page_by_path($slug, OBJECT, $type);
    $post_id = $post ? (int) $post->ID : wp_insert_post(['post_type' => $type, 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content]);
    if (!$post_id || is_wp_error($post_id)) return 0;
    if ($post && $post->post_title !== $title) wp_update_post(['ID' => $post_id, 'post_title' => $title]);
    if ($content !== '' && (!$post || $post->post_content === '')) wp_update_post(['ID' => $post_id, 'post_content' => $content]);
    foreach ($meta as $key => $value) update_post_meta($post_id, '_' . $key, $value);
    return $post_id;
}

function src_seed_home_content() {
    if (get_option('src_home_content_seed_version') === '1') return;
    update_option('src_settings', array_merge(src_settings_defaults(), get_option('src_settings', [])));

    $sectors = [
        ['dairy', 'Dairy', 'Milk value chain development, smallholder cooperative governance and processor linkages.', '01', 'photo-1596733430284-f7437764b1a9'],
        ['poultry', 'Poultry', 'Broiler and layer enterprise systems — from hatchery linkages to retail market access.', '02', 'photo-1723357018190-c0163698165b'],
        ['meat-processing', 'Meat Processing', 'Abattoir modernisation, cold chain development and formal retail market linkages.', '03', 'photo-1759997956694-55411e03aecf'],
        ['feedlot', 'Feedlot', 'Commercial feedlot enterprise development for arid and semi-arid regions of Kenya.', '04', 'photo-1598974357035-0d439e28ccaf'],
        ['fodder', 'Fodder', 'Hay and silage production enterprises — drought-proofing livestock value chains.', '05', 'photo-1635601858869-25a186371ecb'],
        ['agrovet', 'Agrovet', 'Veterinary input distribution, last-mile supply chains and agro-dealer development.', '06', 'photo-1606235357537-84aea24d4c4f'],
        ['livestock-markets', 'Livestock Markets', 'Market systems strengthening for pastoralist communities across arid counties.', '07', 'photo-1713289590437-65e4db863617'],
        ['wash', 'WASH', 'Water, sanitation and hygiene enterprise development for arid communities.', '08', 'photo-1553775927-a071d5a6a39a'],
        ['business-strategy', 'Business Strategy', 'SME growth advisory, business model strengthening and investor-readiness programmes.', '09', 'photo-1573167507387-6b4b98cb7c13'],
    ];
    foreach ($sectors as $order => [$slug, $title, $content, $number, $image]) src_upsert_home_record('src_sector_item', $slug, $title, $content, ['sector_number' => $number, 'sector_image_url' => 'https://images.unsplash.com/' . $image . '?w=1100&h=700&fit=crop&auto=format', 'sector_link' => src_page_url('what-we-do'), 'sector_home_order' => $order + 1]);

    $pillars = [
        ['business-model-strengthening', 'Business Model Strengthening', 'Diagnosing and strengthening the financial, operational and market fundamentals of SMEs and cooperatives.', 'Pillar 01'],
        ['investor-readiness', 'Investor Readiness & Linkage to Finance', 'Preparing enterprises for debt and equity investment through bankability, governance and pitch-readiness.', 'Pillar 02'],
        ['market-linkages', 'Market Linkages', 'Connecting SMEs to buyers, off-takers, formal markets and trade networks — domestic and regional.', 'Pillar 03'],
    ];
    foreach ($pillars as $order => [$slug, $title, $content, $number]) src_upsert_home_record('src_pillar_item', $slug, $title, $content, ['pillar_number' => $number, 'pillar_link' => src_page_url('what-we-do'), 'pillar_home_order' => $order + 1]);

    $audiences = ['Women Entrepreneurs', 'Youth in Agribusiness', 'Green Enterprises', 'Cooperatives', 'Digital Inclusion'];
    foreach ($audiences as $order => $title) src_upsert_home_record('src_audience_item', sanitize_title($title), $title, '', ['audience_link' => src_page_url('what-we-do'), 'audience_home_order' => $order + 1]);

    $stats = [['2012', 'Established'], ['51', 'Staff'], ['15+', 'Kenyan counties'], ['25+', 'Associate consultants'], ['2,500+', 'Women via HerVenture'], ['$2M+', 'Funding unlocked']];
    foreach ($stats as $order => [$value, $label]) src_upsert_home_record('src_stat_item', sanitize_title($label), $label, '', ['stat_value' => $value, 'stat_label' => $label, 'stat_home_order' => $order + 1, 'stat_home_enabled' => '1']);

    $partners = [
        ['usaid', 'USAID', 'USAID', 'logo', '', 'USAID', 2, 'red'], ['fcdo', 'FCDO', 'FCDO', 'logo', '', 'FCDO', 1, 'charcoal'],
        ['market-photo', 'Entrepreneur at market', '', 'image', 'https://images.unsplash.com/photo-1751130562241-3323a0362831?w=480&h=320&fit=crop&auto=format', '', 1, ''],
        ['field-photo', 'Farmer in the field', '', 'image', 'https://images.unsplash.com/photo-1714327676794-7f8863501daf?w=480&h=320&fit=crop&auto=format', '', 1, ''],
        ['world-bank', 'World Bank', 'World Bank', 'logo', '', "World\nBank", 2, 'charcoal'], ['ikea', 'IKEA Foundation', 'IKEA Foundation', 'logo', '', "IKEA\nFdn", 1, 'red'], ['cbf', 'Cherie Blair Foundation', 'Cherie Blair Foundation', 'logo', '', 'CBF', 1, 'orange'], ['boma', 'BOMA Project', 'BOMA Project', 'logo', '', 'BOMA', 1, 'red'],
        ['partners-cta', 'View all clients & partners', '', 'cta', '', '', 2, 'charcoal'],
    ];
    foreach ($partners as $order => [$slug, $title, $name, $type, $image, $abbr, $span, $color]) src_upsert_home_record('src_partner_item', $slug, $title, '', ['partner_type' => $type, 'partner_abbreviation' => $abbr ?: $title, 'partner_name' => $name ?: $title, 'partner_color' => $color, 'partner_image_url' => $image, 'partner_span' => $span, 'partner_home_order' => $order + 1, 'partner_link' => $type === 'cta' ? src_page_url('partners') : '']);

    $project_data = [
        'her-venture' => ['USAID / BOMA PROJECT', 2, 'Marsabit, Isiolo, Samburu — Kenya', 'A women-led enterprise development programme equipping women in arid counties with business skills, savings groups, and market linkages.', [['2,500+', 'Women supported'], ['18 mo', 'Avg. programme cycle']], [['Household income', 'Average household income increased by 48% across participating women entrepreneurs within 18 months of joining the programme.'], ['Savings groups', '312 village savings and loan associations (VSLAs) formed, collectively managing over KES 14M in group savings.'], ['Market linkages', '78% of graduates established at least one formal market offtake agreement, compared to 12% at baseline.']]],
        'dairy-cooperative-strengthening' => ['FCDO KENYA', 1, 'Nyandarua, Meru — Kenya', 'Building governance, financial management, and market-access capacity for smallholder dairy cooperatives to attract commercial investment.', [['14', 'Cooperatives strengthened'], ['$1.2M', 'Finance unlocked']], [['Governance reform', '14 cooperatives adopted new bylaws, financial controls, and elected gender-balanced boards.'], ['Milk offtake', 'Average daily milk collection rose from 800L to 2,400L per cooperative following market linkage support.'], ['Commercial finance', '$1.2M in cooperative loans secured from three Kenyan commercial banks — first-ever for 9 of the 14 cooperatives.']]],
        'agrovet-network-scale-up' => ['WORLD BANK / KENYA', 1, 'North-Eastern Kenya', 'Professionalising agro-vet supply chains and building last-mile distribution networks in underserved arid counties.', [['60+', 'Agrovets trained'], ['8', 'Counties reached']], [['Stockout reduction', 'Critical livestock drug stockouts reduced by 62% after supply chain restructuring and buffer-stock financing.'], ['Farmer reach', '60+ trained agrovets now serve an estimated 45,000 pastoralist households across 8 ASAL counties.'], ['Revenue growth', 'Average agrovet business revenue grew 34% in year one following business advisory support.']]],
        'wash-enterprise-development' => ['IKEA FOUNDATION', 1, 'Turkana, West Pokot — Kenya', 'Supporting small water enterprises and sanitation providers to become commercially viable, reducing donor dependency in remote communities.', [['32', 'Water SMEs supported'], ['120K', 'People with safe water']], [['Commercial viability', '26 of 32 supported water enterprises now operate without donor subsidies, covering costs from tariff revenue alone.'], ['Safe water access', '120,000 people in Turkana and West Pokot gained reliable piped water access through strengthened local operators.'], ['Sanitation businesses', '8 new sanitation SMEs launched, collectively serving 18,000 households with improved latrine products and emptying services.']]],
        'livestock-market-systems' => ['CBF / USAID', 1, 'Kenya, Tanzania, Ethiopia', 'Strengthening formal livestock market infrastructure, price information systems, and trader networks across the Greater Horn of Africa.', [['3', 'Countries'], ['$4M+', 'Livestock trade facilitated']], [['Market price transparency', 'Real-time price SMS alerts now reach 12,000 pastoralist traders weekly across 3 countries, reducing information asymmetry.'], ['Cross-border trade', 'Formal cross-border livestock trade volumes increased by 28% following trader certification and corridor improvements.'], ['Market infrastructure', '6 livestock markets upgraded with shade structures, water troughs, and disease-screening facilities.']]],
        'sme-business-strategy-advisory' => ['PRIVATE SECTOR CLIENTS', 1, 'Nairobi + peri-urban Kenya', 'Bespoke advisory support helping growth-stage SMEs develop bankable business plans, investor decks, and operational strategies.', [['85+', 'Businesses advised'], ['72%', 'Secured financing']], [['Investment readiness', '72% of SMEs that completed the full advisory cycle secured financing within 12 months — from banks, DFIs, or equity investors.'], ['Revenue scale', 'Advisory clients grew average revenues by 2.4× over two years following strategic plan implementation.'], ['Sector spread', '85+ SMEs across agribusiness, WASH, health, and light manufacturing have received advisory services since 2018.']]],
    ];
    foreach ($project_data as $slug => [$funder, $span, $location, $description, $stats, $impacts]) {
        $project = get_page_by_path($slug, OBJECT, 'project');
        if (!$project) continue;
        $order = array_search($slug, array_keys($project_data), true) + 1;
        update_post_meta($project->ID, '_project_funder', $funder); update_post_meta($project->ID, '_project_span', $span); update_post_meta($project->ID, '_project_home_order', $order); update_post_meta($project->ID, '_project_home_featured', '1'); update_post_meta($project->ID, '_project_modal_description', $description); update_post_meta($project->ID, '_project_stats', implode("\n", array_map(static fn($row) => $row[0] . ' | ' . $row[1], $stats))); update_post_meta($project->ID, '_project_impacts', implode("\n", array_map(static fn($row) => $row[0] . ' | ' . $row[1], $impacts)));
    }

    $team = [
        'catherine-gitonga' => ['Founder & CEO', 'Over a decade building enterprise support systems across East Africa — from cooperative governance to investor readiness.', 'https://images.unsplash.com/photo-1573497019418-b400bb3ab074?w=600&h=780&fit=crop&crop=faces,top&auto=format&q=80'],
        'samuel-wamae' => ['Board Chair', 'Senior leader in private sector development with deep roots in Kenya\'s agricultural finance ecosystem.', 'https://images.unsplash.com/photo-1614023342667-6f060e9d1e04?w=600&h=780&fit=crop&crop=faces,top&auto=format&q=80'],
        'rosemary-munyiri' => ['Board Member', "Champion of women's economic empowerment programmes across Kenya and the Horn of Africa.", 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=700&h=900&fit=crop&auto=format'],
        'evelyn-okwach' => ['Board Member', 'Specialist in SME market linkages and value chain finance across Sub-Saharan Africa.', 'https://images.unsplash.com/photo-1573497019418-b400bb3ab074?w=700&h=900&fit=crop&auto=format'],
        'dr-samuel-muriu' => ['Board Member', 'Academic and practitioner in enterprise development, with research spanning 5 African countries.', 'https://images.unsplash.com/photo-1616805765352-beedbad46b2a?w=600&h=780&fit=crop&crop=faces,top&auto=format&q=80'],
    ];
    foreach ($team as $order => $data) { $member = get_page_by_path($order, OBJECT, 'team_member'); if (!$member) continue; update_post_meta($member->ID, '_team_role', $data[0]); update_post_meta($member->ID, '_team_quote', $data[1]); update_post_meta($member->ID, '_team_image_url', $data[2]); update_post_meta($member->ID, '_team_home_order', array_search($order, array_keys($team), true) + 1); update_post_meta($member->ID, '_team_home_featured', '1'); }

    $programme_data = [
        'road-to-growth' => ['enrolling now', 'A 14-week digital business skills programme for women entrepreneurs.', '14 weeks', 'October 7, 2026', 'Free', '120 spots', 'https://images.unsplash.com/photo-1573497019418-b400bb3ab074?w=500&h=280&fit=crop&crop=center&auto=format&q=75'],
        'road-to-markets' => ['upcoming', 'Helping SMEs build market linkages and formal offtake relationships.', '10 weeks', 'November 3, 2026', 'Free', '60 spots', 'https://images.unsplash.com/photo-1596733430284-f7437764b1a9?w=500&h=280&fit=crop&crop=center&auto=format&q=75'],
        'investor-readiness-bootcamp' => ['enrolling now', 'Intensive 5-day bootcamp to prepare growth-stage SMEs for investment.', '5 days', 'October 14, 2026', 'KES 15,000', '25 spots', 'https://images.unsplash.com/photo-1573167507387-6b4b98cb7c13?w=500&h=280&fit=crop&crop=center&auto=format&q=75'],
        'expert-consultation-session' => ['enrolling now', "One-on-one 2-hour session with a sector specialist from SRC's Pool of Experts.", '2 hours', 'Rolling — book any weekday', 'KES 5,000', '', 'https://images.unsplash.com/photo-1614023342667-6f060e9d1e04?w=500&h=280&fit=crop&crop=faces,top&auto=format&q=75'],
    ];
    foreach ($programme_data as $order => $data) { $programme = get_page_by_path($order, OBJECT, 'programme'); if (!$programme) continue; update_post_meta($programme->ID, '_programme_status', $data[0]); update_post_meta($programme->ID, '_programme_image_url', $data[6]); update_post_meta($programme->ID, '_programme_home_order', array_search($order, array_keys($programme_data), true) + 1); update_post_meta($programme->ID, '_programme_home_featured', '1'); update_post_meta($programme->ID, '_programme_duration', $data[2]); update_post_meta($programme->ID, '_programme_start', $data[3]); update_post_meta($programme->ID, '_programme_fee', $data[4]); update_post_meta($programme->ID, '_programme_spots', $data[5]); update_post_meta($programme->ID, '_programme_partner', 'Smart Regional Consultants'); }

    update_option('src_home_content_seed_version', '1');
}
add_action('init', 'src_seed_home_content', 31);

function src_seed_page_content() {
    if (get_option('src_page_content_seed_version') === '2') return;
    $pages = [
        'about' => [
            'page_hero_title' => 'About SRC', 'page_hero_eyebrow' => 'Smart Regional Consultants', 'page_hero_intro' => 'A firm built on proximity to the entrepreneur.', 'page_story_eyebrow' => 'Our story', 'page_story_heading' => 'A firm built on proximity to the entrepreneur.', 'page_story_body' => "SRC was founded in 2012 by Catherine Gitonga, a development professional with more than 20 years of enterprise development experience across Kenya and East Africa. The firm grew from a conviction that SMEs — particularly in agriculture, livestock and cooperatives — were underserved by both the market and the donor system.\n\nToday, SRC is a 51-strong team operating across 15+ Kenyan counties and 5 additional African countries, delivering high-quality programmes for leading development funders.\n\nBut the mission has never changed: unlock real growth, for real businesses, in real communities.", 'page_story_image_url' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=900&h=1100&fit=crop&auto=format', 'page_story_stat' => '2012', 'page_story_stat_label' => 'Founded in Nairobi', 'page_pillars' => "Vision | A thriving SME ecosystem across Kenya and East Africa.\nMission | High-quality, context-specific support for sustainable growth.\nPurpose | Building Enterprises from Good to Great.", 'page_values_eyebrow' => 'What we stand for', 'page_values_heading' => 'Core values', 'page_values' => "Integrity & safeguarding | We hold ourselves to the highest ethical standards.\nExcellence | We deliver measurable, lasting results — not outputs.\nInnovation | We bring context-specific solutions to complex challenges.\nTeaming | The best outcomes emerge from genuine collaboration.",
        ],
        'what-we-do' => [
            'page_hero_title' => 'What we do', 'page_hero_eyebrow' => 'Smart Regional Consultants', 'page_hero_intro' => 'We turn enterprise potential into practical, sustainable growth.', 'page_capabilities_eyebrow' => 'Our capabilities', 'page_capabilities_heading' => "Practical support.\nReal growth.", 'page_capabilities_intro' => 'From diagnosis and strategy to delivery and market access, we help the full enterprise journey move forward.', 'page_capabilities_cards' => "Agriculture & food | Value chain development, productivity and market systems. | https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=900&h=600&fit=crop&auto=format\nEnterprise development | Advisory, incubation, acceleration and business support. | https://images.unsplash.com/photo-1556761175-b413da4baf72?w=900&h=600&fit=crop&auto=format\nInclusive ecosystems | Finance, policy, partnerships and the systems around business. | https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=900&h=600&fit=crop&auto=format", 'page_capabilities_steps' => "Diagnosis | Assess enterprise health, market position and growth barriers.\nIntervention design | Co-design bespoke support programmes with clients and donors.\nDelivery | Deploy technical assistance, training and market linkage services.\nScale & sustain | Embed systems and skills for long-term enterprise growth.",
        ],
        'contact' => ['page_hero_title' => 'Contact', 'page_hero_eyebrow' => 'Smart Regional Consultants', 'page_hero_intro' => 'Let’s talk about the challenge you are trying to solve.', 'page_contact_eyebrow' => 'Start a conversation', 'page_contact_heading' => 'Tell us where the friction is.', 'page_contact_intro' => 'We work with funders, governments, market actors and entrepreneurs. Send a note and our team will be in touch.', 'page_contact_email' => 'hello@smartregionalconsultants.org', 'page_contact_city' => 'Nairobi, Kenya', 'page_contact_coverage' => 'Working across 15+ counties and 5 African countries'],
        'careers' => ['page_hero_title' => 'Careers', 'page_hero_eyebrow' => 'Smart Regional Consultants', 'page_hero_intro' => 'Bring your perspective. Build something that matters.', 'page_careers_eyebrow' => 'Join the team', 'page_careers_heading' => 'Open positions', 'page_application_email' => 'hello@smartregionalconsultants.org'],
        'get-involved' => ['page_hero_title' => 'Get involved', 'page_hero_eyebrow' => 'Smart Regional Consultants', 'page_hero_intro' => 'Stronger enterprises need stronger partnerships.'],
        'pool-of-experts' => ['page_hero_title' => 'Pool of experts', 'page_hero_eyebrow' => 'Smart Regional Consultants', 'page_hero_intro' => 'The right expertise, close to the work.'],
        'partners' => ['page_hero_title' => 'Clients & Partners', 'page_hero_eyebrow' => 'Smart Regional Consultants', 'page_hero_intro' => 'Leading donors, NGOs and enterprises building stronger ecosystems with SRC.'],
    ];
    foreach ($pages as $slug => $fields) { $page = get_page_by_path($slug); if (!$page) continue; foreach ($fields as $key => $value) if (get_post_meta($page->ID, '_' . $key, true) === '') update_post_meta($page->ID, '_' . $key, $value); }
    update_option('src_page_content_seed_version', '2');
}
add_action('init', 'src_seed_page_content', 32);

function src_admin_notice() { if (!get_option('src_seeded')) echo '<div class="notice notice-info"><p>Smart Regional Consultants theme is ready. Activate it to create the starter content and navigation.</p></div>'; }
add_action('admin_notices', 'src_admin_notice');

function src_contact_handler() {
    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    if ($name && is_email($email) && $message) {
        wp_mail(get_option('admin_email'), 'SRC website enquiry from ' . $name, $message . "\n\nReply to: " . $email);
    }
    wp_safe_redirect(add_query_arg('sent', '1', src_page_url('contact')));
    exit;
}
add_action('admin_post_src_contact', 'src_contact_handler');
add_action('admin_post_nopriv_src_contact', 'src_contact_handler');
