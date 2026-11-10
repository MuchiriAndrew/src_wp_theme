<?php
if (!defined('ABSPATH')) exit;

define('SRC_VERSION', '3.5.206');

require_once get_template_directory() . '/inc/v3-content-model.php';
require_once get_template_directory() . '/inc/cli/import-programmes.php';
require_once get_template_directory() . '/inc/cli/import-impact.php';
require_once get_template_directory() . '/inc/commerce/programme-products.php';
require_once get_template_directory() . '/inc/v3-pool-experts.php';
require_once get_template_directory() . '/inc/v3-get-involved.php';
require_once get_template_directory() . '/inc/v3-newsletter.php';
require_once get_template_directory() . '/inc/v3-careers.php';

function src_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', ['height' => 64, 'width' => 220, 'flex-height' => true, 'flex-width' => true]);
    register_nav_menus([
        'primary' => __('Primary Navigation', 'src'),
        'footer' => __('Footer Navigation (legacy)', 'src'),
        'footer_our_story' => __('Footer: Our Story', 'src'),
        'footer_programmes' => __('Footer: Programmes', 'src'),
        'footer_more' => __('Footer: More', 'src'),
        'footer_legal' => __('Footer: Legal', 'src'),
    ]);
}
add_action('after_setup_theme', 'src_setup');

/**
 * Accessible nested navigation matching the published SRC v3 menu.
 * Menu items and submenu ordering remain editable under Appearance → Menus.
 */
class SRC_Reference_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth + 1);
        $output .= "\n$indent<ul class=\"sub-menu\" aria-label=\"Submenu\">\n";
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth + 1);
        $output .= "$indent</ul>\n";
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $is_fragment_link = $depth > 0 && !empty($item->url) && strpos($item->url, '#') !== false;
        if ($is_fragment_link) {
            $classes = array_diff($classes, ['current-menu-item', 'current_page_item']);
        }
        $classes[] = 'menu-depth-' . (int) $depth;
        $has_children = in_array('menu-item-has-children', $classes, true);
        $class_names = implode(' ', array_filter(array_map('sanitize_html_class', $classes)));
        $output .= '<li class="' . esc_attr($class_names) . '">';
        $title = apply_filters('the_title', $item->title, $item->ID);

        if ($depth > 0 && !empty($item->description)) {
            $output .= '<span class="menu-group-label">' . esc_html($item->description) . '</span>';
        }

        if ($has_children && $depth === 0) {
            $output .= '<button class="nav-parent-button" type="button" aria-expanded="false"><span>' . esc_html($title) . '</span><span class="nav-parent-indicator" aria-hidden="true">+</span></button>';
            return;
        }

        $is_current = !$is_fragment_link && !empty($item->current);
        $attributes = [
            'href' => !empty($item->url) ? $item->url : '',
            'target' => !empty($item->target) ? $item->target : '',
            'rel' => !empty($item->xfn) ? $item->xfn : '',
            'aria-current' => $is_current ? 'page' : '',
        ];
        $attribute_string = '';
        foreach ($attributes as $attribute => $value) {
            if ($value === '') continue;
            $attribute_string .= ' ' . $attribute . '="' . esc_attr($value) . '"';
        }
        $output .= '<a class="nav-link' . ($is_current ? ' is-current' : '') . '"' . $attribute_string . '>' . esc_html($title) . '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "</li>\n";
    }
}

function src_assets() {
    wp_enqueue_style('src-style', get_stylesheet_uri(), [], SRC_VERSION);
    wp_enqueue_script('src-site', get_template_directory_uri() . '/assets/js/site.js', [], SRC_VERSION, true);
    if (is_front_page()) {
        wp_enqueue_script('src-footprint-v3', get_template_directory_uri() . '/assets/js/footprint-v3.js', [], SRC_VERSION, true);
        wp_enqueue_script('src-project-folders', get_template_directory_uri() . '/assets/js/project-folders.js', [], SRC_VERSION, true);
    }
    if (is_page('what-we-do')) wp_enqueue_script('src-what-we-do', get_template_directory_uri() . '/assets/js/what-we-do.js', [], SRC_VERSION, true);
    if (is_page('contact')) wp_enqueue_script('src-contact-v3', get_template_directory_uri() . '/assets/js/contact-v3.js', [], SRC_VERSION, true);
    if (is_page('pool-of-experts')) wp_enqueue_script('src-pool-experts-v3', get_template_directory_uri() . '/assets/js/pool-experts-v3.js', [], SRC_VERSION, true);
    if (is_page('get-involved')) wp_enqueue_script('src-get-involved-v3', get_template_directory_uri() . '/assets/js/get-involved-v3.js', [], SRC_VERSION, true);
    if (is_page('careers')) wp_enqueue_script('src-careers-v3', get_template_directory_uri() . '/assets/js/careers-v3.js', [], SRC_VERSION, true);
    if (is_post_type_archive('team_member')) wp_enqueue_script('src-leadership-v3', get_template_directory_uri() . '/assets/js/leadership-v3.js', [], SRC_VERSION, true);
    if (is_post_type_archive('programme')) {
        wp_enqueue_script('src-programmes', get_template_directory_uri() . '/assets/js/programmes.js', [], SRC_VERSION, true);
    }
    $registration_return_order = function_exists('src_v3_registration_return_order') ? src_v3_registration_return_order() : null;
    $registration_payment_state = $registration_return_order ? src_v3_registration_payment_state($registration_return_order) : null;
    $registration_script = $registration_return_order ? 'src-order-confirmation' : 'src-registration';
    wp_enqueue_script($registration_script, get_template_directory_uri() . ($registration_return_order ? '/assets/js/order-confirmation.js' : '/assets/js/registration.js'), [], SRC_VERSION, true);
    wp_localize_script($registration_script, 'srcRegistrationConfig', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('src_v3_registration'),
        'copy' => [
            'paidHeading' => src_option('registration_paid_heading', 'Your payment is confirmed'),
            'paidMessage' => src_option('registration_paid_message', 'Thank you for registering. Your place has been confirmed and our team will follow up with programme details.'),
            'paidEmail' => src_option('registration_paid_email_message', 'We have sent your order confirmation to %s. Please check your inbox and spam folder.'),
            'failedHeading' => src_option('registration_failed_heading', 'Payment was not completed'),
            'failedMessage' => src_option('registration_failed_message', 'Your payment was not completed. You can try again.'),
            'retryGuide' => __('Your registration details are saved. On the next page, confirm your M-PESA number before requesting a new payment prompt.', 'src'),
            'contactGuide' => __('Have your order number ready when you contact SRC. Please do not start another payment until the order is checked.', 'src'),
            'pendingEmail' => src_option('registration_pending_email_message', 'Your confirmation email will be sent after the payment is confirmed.'),
            'waitingHeading' => src_option('registration_waiting_heading', 'Waiting for payment confirmation'),
            'waitingMessage' => str_replace('This window will', 'This page will', src_option('registration_waiting_message', 'Complete the M-PESA prompt on your phone. This page will update when your payment is confirmed.')),
            'waitingEmail' => src_option('registration_waiting_email_message', 'Your confirmation email will be sent to %s after payment.'),
            'phoneError' => src_option('registration_phone_error', 'Enter 9 digits starting with 7 or 1.'),
            'emailError' => src_option('registration_email_error', 'Enter a valid email address.'),
            'consentError' => src_option('registration_consent_error', 'Consent is required to continue.'),
            'preparingCheckout' => src_option('registration_preparing_checkout', 'Preparing checkout…'),
            'checkoutError' => src_option('registration_checkout_error', 'Checkout could not be started. Please try again.'),
            'continueCheckout' => src_option('registration_continue_checkout', 'Continue to secure checkout'),
        ],
        'order' => $registration_return_order ? [
            'id' => $registration_return_order->get_id(),
            'key' => $registration_return_order->get_order_key(),
            'programmeId' => src_v3_order_programme_id($registration_return_order),
            'programmeTitle' => get_post_meta(src_v3_order_programme_id($registration_return_order), '_programme_hero_title', true) ?: get_the_title(src_v3_order_programme_id($registration_return_order)),
            'paid' => $registration_payment_state['paid'],
            'failed' => $registration_payment_state['failed'],
            'failureMessage' => $registration_payment_state['failureMessage'],
            'number' => $registration_return_order->get_order_number(),
            'email' => $registration_return_order->get_billing_email(),
            'receipt' => $registration_payment_state['receipt'],
            'retryUrl' => $registration_return_order->get_checkout_payment_url(),
        ] : null,
    ]);
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
    register_taxonomy('team_department', 'team_member', [
        'labels' => ['name' => 'Team Departments', 'singular_name' => 'Team Department', 'all_items' => 'All Team Departments', 'edit_item' => 'Edit Team Department', 'add_new_item' => 'Add Team Department'],
        'public' => true, 'show_ui' => true, 'show_admin_column' => true, 'show_in_rest' => true, 'hierarchical' => true, 'rewrite' => false,
    ]);
    register_taxonomy('team_county', 'team_member', [
        'labels' => ['name' => 'Team Counties', 'singular_name' => 'Team County', 'all_items' => 'All Team Counties', 'edit_item' => 'Edit Team County', 'add_new_item' => 'Add Team County'],
        'public' => true, 'show_ui' => true, 'show_admin_column' => true, 'show_in_rest' => true, 'hierarchical' => true, 'rewrite' => false,
    ]);
    register_post_type('job', [
        'labels' => ['name' => 'Careers', 'singular_name' => 'Job', 'add_new_item' => 'Add Job Opening'],
        'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_rest' => true, 'menu_icon' => 'dashicons-businessperson', 'supports' => ['title', 'editor', 'excerpt'],
        'has_archive' => false, 'rewrite' => false,
    ]);
    register_taxonomy('job_department', 'job', ['labels' => ['name' => 'Departments', 'singular_name' => 'Department'], 'public' => false, 'show_ui' => true, 'show_admin_column' => true, 'show_in_rest' => true, 'hierarchical' => true, 'rewrite' => false]);
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
            $is_image = in_array($key, ['project_image_url','programme_image_url','team_image_url','sector_image_url','partner_image_url'], true);
            printf('<p><label for="%1$s"><strong>%2$s</strong></label><br><span class="src-media-field"><input style="width:100%%" id="%1$s" name="%1$s" type="%4$s" value="%3$s">%6$s</span>%5$s</p>', esc_attr($key), esc_html($label), esc_attr($value), esc_attr($type), $description ? '<br><span class="description">' . esc_html($description) . '</span>' : '', $is_image ? '<button type="button" class="button src-select-image">Choose image</button>' : '');
        }
    }
    echo '</div>';
}
function src_add_meta_boxes() {
    add_meta_box('src-project-meta', 'Project Details', fn($p) => src_meta_box($p, [
        'project_partner'=>'Partner / Funder', 'project_funder'=>'Modal funder label', 'project_location'=>'Location', 'project_year'=>'Year', 'project_impact'=>'Impact statement',
        'project_span'=>['Homepage tile size: 1 or 2', 'number'], 'project_home_order'=>['Homepage order', 'number'], 'project_home_featured'=>['Show on homepage', 'checkbox'],
        'project_detail_eyebrow'=>'Project detail eyebrow', 'project_detail_heading'=>'Project detail section heading',
        'project_external_url'=>'External project website', 'project_modal_description'=>['Modal description', 'textarea'], 'project_image_url'=>'Fallback image URL',
        'project_stats'=>['Overview stats, one per line as Label | Value', 'textarea'], 'project_impacts'=>['Impact results, one per line as Heading | Description', 'textarea'],
    ]), 'project', 'normal');
    add_meta_box('src-programme-meta', 'Programme Details', fn($p) => src_meta_box($p, [
        'programme_status'=>'Status: enrolling, upcoming, or closed', 'programme_partner'=>'Partner / Funder', 'programme_duration'=>'Duration', 'programme_start'=>'Next start date', 'programme_fee'=>'Fee', 'programme_location'=>'Location', 'programme_spots'=>'Available spots',
        'programme_home_order'=>['Homepage order', 'number'], 'programme_home_featured'=>['Show on homepage', 'checkbox'], 'programme_image_url'=>'Fallback image URL',
    ]), 'programme', 'normal');
    add_meta_box('src-team-meta', 'Team Details', fn($p) => src_meta_box($p, [
        'team_role'=>'Role', 'team_quote'=>'Short quote / bio', 'team_bio_short'=>['Directory preview bio', 'textarea'], 'team_linkedin'=>['LinkedIn profile URL', 'url'], 'team_county_level'=>'County role level: Manager or Officer',
        'team_order_senior_management'=>['Leadership order: Senior Management', 'number'], 'team_order_finance_operations'=>['Leadership order: Finance & Operations', 'number'], 'team_order_data_quality'=>['Leadership order: Data Quality', 'number'], 'team_order_strategy_consulting'=>['Leadership order: Strategy & Consulting', 'number'], 'team_order_programmes_team'=>['Leadership order: Programmes', 'number'], 'team_order_drivers'=>['Leadership order: Drivers', 'number'],
        'team_category'=>'Category: leadership, board, or staff', 'team_panel_color'=>'Homepage panel colour: red, charcoal, or orange', 'team_home_order'=>['Homepage order', 'number'], 'team_home_featured'=>['Show on homepage', 'checkbox'], 'team_image_url'=>'Portrait URL or theme-relative asset path',
    ]), 'team_member', 'normal');
    add_meta_box('src-job-meta', 'Job Details', fn($p) => src_meta_box($p, ['job_location'=>'Location', 'job_type'=>'Employment type', 'job_department'=>'Department', 'job_deadline'=>'Application deadline', 'job_order'=>['Display order', 'number'], 'job_requirements'=>['Requirements (one per line)', 'textarea']]), 'job', 'normal');
    add_meta_box('src-sector-item-meta', 'Homepage Sector Details', fn($p) => src_meta_box($p, ['sector_number'=>'Display number', 'sector_home_order'=>['Homepage order', 'number'], 'sector_image_url'=>'Fallback image URL', 'sector_link'=>'Explore link']), 'src_sector_item', 'normal');
    add_meta_box('src-pillar-item-meta', 'Homepage Pillar Details', fn($p) => src_meta_box($p, ['pillar_number'=>'Display number', 'pillar_home_order'=>['Homepage order', 'number'], 'pillar_link'=>'Learn more link']), 'src_pillar_item', 'normal');
    add_meta_box('src-audience-item-meta', 'Homepage Audience Details', fn($p) => src_meta_box($p, ['audience_home_order'=>['Homepage order', 'number'], 'audience_link'=>'Audience link']), 'src_audience_item', 'normal');
    add_meta_box('src-partner-item-meta', 'Homepage Partner Details', fn($p) => src_meta_box($p, ['partner_type'=>'Tile type: logo, image, or cta', 'partner_abbreviation'=>'Short label (use a line break only in the full-name field if desired)', 'partner_name'=>'Full name', 'partner_color'=>'Logo tile colour: red, charcoal, or orange', 'partner_image_url'=>'Fallback image URL', 'partner_span'=>['Tile span: 1 or 2', 'number'], 'partner_home_order'=>['Homepage order', 'number'], 'partner_link'=>'Partner page link']), 'src_partner_item', 'normal');
    add_meta_box('src-stat-item-meta', 'Homepage Stat Details', fn($p) => src_meta_box($p, ['stat_value'=>'Value', 'stat_label'=>'Label', 'stat_home_order'=>['Homepage order', 'number'], 'stat_home_enabled'=>['Show on homepage', 'checkbox']]), 'src_stat_item', 'normal');
    add_meta_box('src-page-content-meta', 'Page Content', 'src_render_page_content_box', 'page', 'normal', 'high');
    add_meta_box('src-post-content-meta', 'Main Content', 'src_render_post_content_box', ['project', 'programme', 'team_member', 'job', 'src_sector_item', 'src_pillar_item', 'src_audience_item', 'src_partner_item', 'src_stat_item', 'src_testimonial', 'src_expert', 'src_project_folder', 'src_publication', 'src_news_item', 'portfolio_company', 'impact_metric'], 'normal', 'high');
}
add_action('add_meta_boxes', 'src_add_meta_boxes');

/**
 * Theme-managed content uses straightforward WordPress form fields. Remove the block
 * canvas and the legacy editor pane, then expose post_content as a normal textarea.
 */
function src_admin_plain_content_types() {
    return ['page', 'project', 'programme', 'team_member', 'job', 'src_sector_item', 'src_pillar_item', 'src_audience_item', 'src_partner_item', 'src_stat_item', 'src_testimonial', 'src_expert', 'src_project_folder', 'src_publication', 'src_news_item', 'portfolio_company', 'impact_metric'];
}
function src_disable_content_editor_canvas() {
    foreach (src_admin_plain_content_types() as $post_type) remove_post_type_support($post_type, 'editor');
}
add_action('init', 'src_disable_content_editor_canvas', 100);
add_filter('use_block_editor_for_post_type', static function ($use, $post_type) {
    return in_array($post_type, src_admin_plain_content_types(), true) ? false : $use;
}, 100, 2);
function src_render_post_content_box($post) {
    wp_nonce_field('src_plain_content_save', 'src_plain_content_nonce');
    echo '<p><label for="src-post-content"><strong>Content</strong></label></p>';
    echo '<textarea id="src-post-content" name="src_post_content" rows="12" class="large-text">' . esc_textarea($post->post_content) . '</textarea>';
    echo '<p class="description">Enter the content shown on this item. Basic HTML is allowed.</p>';
}
function src_render_page_content_box($post) {
    wp_nonce_field('src_plain_content_save', 'src_plain_content_nonce');
    wp_nonce_field('src_meta_save', 'src_meta_nonce');
    if (in_array((string) $post->post_name, ['shop', 'cart', 'checkout', 'my-account'], true)) {
        echo '<p>This page is managed by WooCommerce. Configure its store function in the WooCommerce admin screens. The page block content is preserved here.</p>';
        return;
    }
    echo '<p><label for="src-post-content"><strong>Page body</strong></label></p>';
    echo '<textarea id="src-post-content" name="src_post_content" rows="8" class="large-text">' . esc_textarea($post->post_content) . '</textarea>';
    echo '<p class="description">This is the standard page body. Branded page sections are managed below.</p>';
    $schema = src_page_field_schema();
    foreach (src_page_fields_for_page($post, $schema) as $key) {
        if (!isset($schema[$key])) continue;
        $field = $schema[$key];
        $type = in_array($key, ['page_hero_image_url', 'page_story_image_url'], true) ? 'image' : $field[1];
        src_meta_box_field($post->ID, $key, $field[0], $type);
    }
}
function src_page_fields_for_page($post, $schema) {
    $slug = (string) $post->post_name;
    $groups = [
        'about' => ['page_hero_title','page_hero_emphasis','page_hero_eyebrow','page_hero_intro','page_hero_image_url','page_story_eyebrow','page_story_heading','page_story_body','page_story_image_url','page_story_image_alt','page_story_stat','page_story_stat_label','page_pillars','page_values_eyebrow','page_values_heading','page_values','page_theory_eyebrow','page_theory_heading','page_theory_steps','page_about_stats','page_about_cta_eyebrow','page_about_cta_heading','page_about_cta_button','page_about_cta_url'],
        'what-we-do' => ['page_hero_title','page_hero_emphasis','page_hero_eyebrow','page_hero_intro','page_hero_image_url','page_service_pillars','page_service_pillar_aria_label','page_service_who_label','page_service_explore_label','page_service_overview_label','page_service_pillar_programmes_label','page_service_audience_label','page_service_duration_label','page_service_delivery_label','page_service_journey_label','page_service_journey_empty_label','page_service_close_label','page_service_view_programme_label','page_service_all_programmes_label','page_service_current_programmes_label','page_service_get_notified_label','page_service_register_label','page_service_partner_label','page_service_get_involved_label','page_service_status_enrolling','page_service_status_upcoming','page_service_status_delivery','page_service_status_completed','page_service_delivery_eyebrow','page_service_delivery_heading','page_service_delivery_methods','page_service_cta_eyebrow','page_service_cta_heading','page_service_cta_button','page_service_cta_url'],
        'contact' => ['page_hero_title','page_hero_emphasis','page_hero_eyebrow','page_hero_intro','page_hero_image_url','page_contact_eyebrow','page_contact_heading','page_contact_intro','page_contact_email','page_contact_city','page_contact_coverage','page_contact_form_eyebrow','page_contact_name_placeholder','page_contact_organisation_placeholder','page_contact_email_placeholder','page_contact_subject_placeholder','page_contact_message_placeholder','page_contact_offices_eyebrow','page_contact_offices','page_contact_social_eyebrow','page_contact_socials','page_contact_subject_options','page_contact_submit_label','page_contact_success_heading','page_contact_success_message','page_contact_error_message'],
        'careers' => ['page_hero_title','page_hero_emphasis','page_hero_eyebrow','page_hero_intro','page_hero_image_url','page_careers_eyebrow','page_careers_heading','page_application_email','page_careers_positions_label','page_careers_requirements_label','page_careers_apply_label','page_careers_close_label','page_careers_form_label','page_careers_name_placeholder','page_careers_email_placeholder','page_careers_phone_placeholder','page_careers_cover_placeholder','page_careers_cv_label','page_careers_cv_help','page_careers_submit_label','page_careers_success_heading','page_careers_success_message','page_careers_error_message','page_careers_empty_message','page_careers_eoe_eyebrow','page_careers_eoe_heading','page_careers_eoe_copy','page_careers_stats'],
        'pool-of-experts' => ['page_hero_title','page_hero_eyebrow','page_hero_intro','page_hero_image_url','page_experts_all_label','page_experts_cta_eyebrow','page_experts_cta_heading','page_experts_cta_copy','page_experts_cta_button','page_experts_cta_url'],
        'get-involved' => ['page_hero_title','page_hero_eyebrow','page_hero_intro','page_hero_image_url','page_involvement_tracks','page_involvement_capabilities','page_involvement_form_eyebrow','page_involvement_form_heading','page_involvement_form_intro','page_involvement_name_placeholder','page_involvement_role_placeholder','page_involvement_email_placeholder','page_involvement_organisation_placeholder','page_involvement_message_placeholder','page_involvement_submit_label','page_involvement_success_message','page_involvement_error_message','page_involvement_email'],
        'portfolio' => ['page_hero_title','page_hero_eyebrow','page_hero_intro','page_hero_image_url','page_portfolio_card_link'],
        'partners' => ['page_hero_title','page_hero_eyebrow','page_hero_intro','page_partners_directory_eyebrow','page_partners_directory_heading','page_partners_directory_intro','page_partners_empty_message'],
    ];
    $keys = $groups[$slug] ?? ['page_hero_title','page_hero_emphasis','page_hero_eyebrow','page_hero_intro','page_hero_image_url'];
    return array_values(array_intersect($keys, array_keys($schema)));
}
function src_meta_box_field($post_id, $key, $label, $type = 'text') {
    $value = get_post_meta($post_id, '_' . $key, true);
    $id = 'src-field-' . $key;
    if ($type === 'textarea') {
        printf('<p><label for="%1$s"><strong>%2$s</strong></label><br><textarea class="large-text" rows="4" id="%1$s" name="%3$s">%4$s</textarea></p>', esc_attr($id), esc_html($label), esc_attr($key), esc_textarea($value));
    } else {
        $input_type = in_array($type, ['email','url'], true) ? $type : 'text';
        printf('<p><label for="%1$s"><strong>%2$s</strong></label><br><span class="src-media-field"><input class="large-text" id="%1$s" name="%3$s" type="%4$s" value="%5$s">%6$s</span></p>', esc_attr($id), esc_html($label), esc_attr($key), esc_attr($input_type), esc_attr($value), $type === 'image' ? '<button type="button" class="button src-select-image">Choose image</button>' : '');
    }
}
function src_admin_media_fields($hook) {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) return;
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->post_type, src_admin_plain_content_types(), true)) return;
    wp_enqueue_media();
    $script = <<<'JS'
jQuery(function($){$(document).on('click','.src-select-image',function(e){e.preventDefault();var input=$(this).siblings('input');var picker=wp.media({title:'Choose an image',button:{text:'Use image'},library:{type:'image'},multiple:false});picker.on('select',function(){input.val(picker.state().get('selection').first().toJSON().url).trigger('change');});picker.open();});});
JS;
    wp_add_inline_script('jquery-core', $script);
}
add_action('admin_enqueue_scripts', 'src_admin_media_fields');
function src_save_meta($post_id) {
    static $saving_content = false;
    if ($saving_content) return;
    if (isset($_POST['src_plain_content_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['src_plain_content_nonce'])), 'src_plain_content_save') && !wp_is_post_autosave($post_id) && !wp_is_post_revision($post_id) && current_user_can('edit_post', $post_id) && isset($_POST['src_post_content'])) {
        $saving_content = true;
        wp_update_post(['ID' => $post_id, 'post_content' => wp_kses_post(wp_unslash($_POST['src_post_content']))]);
        $saving_content = false;
    }
    if (!isset($_POST['src_meta_nonce']) || !wp_verify_nonce($_POST['src_meta_nonce'], 'src_meta_save') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) return;
    if (!current_user_can('edit_post', $post_id)) return;
    $keys = array_merge(['project_partner','project_funder','project_location','project_year','project_impact','project_span','project_home_order','project_home_featured','project_detail_eyebrow','project_detail_heading','project_external_url','project_modal_description','project_image_url','project_stats','project_impacts','programme_status','programme_partner','programme_duration','programme_start','programme_fee','programme_location','programme_spots','programme_home_order','programme_home_featured','programme_image_url','team_role','team_quote','team_bio_short','team_linkedin','team_county_level','team_order_senior_management','team_order_finance_operations','team_order_data_quality','team_order_strategy_consulting','team_order_programmes_team','team_order_drivers','team_category','team_panel_color','team_home_order','team_home_featured','team_image_url','job_location','job_type','job_department','job_deadline','job_order','job_requirements','sector_number','sector_home_order','sector_image_url','sector_link','pillar_number','pillar_home_order','pillar_link','audience_home_order','audience_link','partner_type','partner_abbreviation','partner_name','partner_color','partner_image_url','partner_span','partner_home_order','partner_link','stat_value','stat_label','stat_home_order','stat_home_enabled'], array_keys(src_page_field_schema()));
    $checkboxes = ['project_home_featured','programme_home_featured','team_home_featured','stat_home_enabled'];
    foreach ($keys as $key) {
        if (in_array($key, $checkboxes, true)) {
            update_post_meta($post_id, '_' . $key, isset($_POST[$key]) ? '1' : '0');
            continue;
        }
        if (!isset($_POST[$key])) continue;
        $value = wp_unslash($_POST[$key]);
        $textarea_keys = array_keys(array_filter(src_page_field_schema(), static fn($definition) => ($definition[1] ?? '') === 'textarea'));
        $value = in_array($key, array_merge(['project_stats','project_impacts','project_modal_description','team_bio_short','job_requirements'], $textarea_keys), true) ? sanitize_textarea_field($value) : (in_array($key, ['team_linkedin','page_experts_cta_url','page_about_cta_url','page_service_cta_url','header_cta_url'], true) ? esc_url_raw($value) : (in_array($key, ['page_involvement_email','page_application_email','page_contact_email'], true) ? sanitize_email($value) : sanitize_text_field($value)));
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
        if ($url !== '') return preg_match('#^https?://#i', $url) ? esc_url_raw($url) : get_theme_file_uri(ltrim($url, '/'));
    }
    return $fallback;
}
function src_project_tile_image_url($image_url) {
    $parts = wp_parse_url($image_url);
    if (!is_array($parts) || empty($parts['host']) || strtolower($parts['host']) !== 'images.unsplash.com') return $image_url;
    $image_url = remove_query_arg(['w', 'h', 'fit', 'crop', 'auto', 'q'], $image_url);
    return add_query_arg(['w' => 900, 'h' => 800, 'fit' => 'crop', 'crop' => 'center', 'auto' => 'format', 'q' => 82], $image_url);
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
        'page_hero_title' => ['Hero title', 'text'], 'page_hero_emphasis' => ['Hero title emphasis', 'text'], 'page_hero_eyebrow' => ['Hero eyebrow', 'text'], 'page_hero_intro' => ['Hero introduction', 'textarea'], 'page_hero_image_url' => ['Hero image URL', 'text'],
        'page_story_eyebrow' => ['Story eyebrow', 'text'], 'page_story_heading' => ['Story heading', 'text'], 'page_story_body' => ['Story body, one paragraph per blank line', 'textarea'], 'page_story_image_url' => ['Story image URL', 'text'], 'page_story_stat' => ['Story stat', 'text'], 'page_story_stat_label' => ['Story stat label', 'text'], 'page_pillars' => ['Pillars, one per line as Label | Description', 'textarea'],
        'page_story_image_alt' => ['Story image alternative text', 'text'],
        'page_values_eyebrow' => ['Values eyebrow', 'text'], 'page_values_heading' => ['Values heading', 'text'], 'page_values' => ['Values, one per line as Title | Description', 'textarea'],
        'page_theory_eyebrow' => ['Theory of Change eyebrow', 'text'], 'page_theory_heading' => ['Theory of Change heading', 'text'], 'page_theory_steps' => ['Theory of Change steps, one per line as Number | Title | Description', 'textarea'],
        'page_about_stats' => ['About impact statistics, one per line as Value | Label', 'textarea'], 'page_about_cta_eyebrow' => ['About team CTA eyebrow', 'text'], 'page_about_cta_heading' => ['About team CTA heading', 'text'], 'page_about_cta_button' => ['About team CTA button', 'text'],
        'page_about_cta_url' => ['About team CTA destination URL', 'url'],
        'page_service_pillars' => ['What We Do pillars, one per line as taxonomy key | number | title | tagline | description | audience | gradient start hex | gradient end hex | accent hex', 'textarea'],
        'page_service_pillar_aria_label' => ['What We Do: pillars section accessibility label', 'text'],
        'page_service_who_label' => ['What We Do: audience label', 'text'],
        'page_service_explore_label' => ['What We Do: explore programmes action', 'text'],
        'page_service_overview_label' => ['What We Do: programme cabinet overview tab', 'text'],
        'page_service_pillar_programmes_label' => ['What We Do: programmes in pillar heading', 'text'],
        'page_service_audience_label' => ['What We Do: programme audience fact label', 'text'],
        'page_service_duration_label' => ['What We Do: programme duration fact label', 'text'],
        'page_service_delivery_label' => ['What We Do: programme delivery fact label', 'text'],
        'page_service_journey_label' => ['What We Do: learning journey label', 'text'],
        'page_service_journey_empty_label' => ['What We Do: missing journey message', 'text'],
        'page_service_close_label' => ['What We Do: close programme cabinet accessibility label', 'text'],
        'page_service_view_programme_label' => ['What We Do: view programme action', 'text'],
        'page_service_all_programmes_label' => ['What We Do: all programmes action', 'text'],
        'page_service_current_programmes_label' => ['What We Do: current programmes action', 'text'],
        'page_service_get_notified_label' => ['What We Do: get notified action', 'text'],
        'page_service_register_label' => ['What We Do: registration action', 'text'],
        'page_service_partner_label' => ['What We Do: partner action', 'text'],
        'page_service_get_involved_label' => ['What We Do: get involved action', 'text'],
        'page_service_status_enrolling' => ['What We Do: enrolling status label', 'text'],
        'page_service_status_upcoming' => ['What We Do: upcoming status label', 'text'],
        'page_service_status_delivery' => ['What We Do: in-delivery status label', 'text'],
        'page_service_status_completed' => ['What We Do: completed status label', 'text'],
        'page_service_delivery_eyebrow' => ['What We Do delivery eyebrow', 'text'], 'page_service_delivery_heading' => ['What We Do delivery heading', 'text'], 'page_service_delivery_methods' => ['What We Do delivery methods, one per line', 'textarea'],
        'page_service_cta_eyebrow' => ['What We Do partnership CTA eyebrow', 'text'], 'page_service_cta_heading' => ['What We Do partnership CTA heading', 'text'], 'page_service_cta_button' => ['What We Do partnership CTA button', 'text'],
        'page_service_cta_url' => ['What We Do partnership CTA destination URL', 'url'],
        'page_contact_eyebrow' => ['Contact eyebrow', 'text'], 'page_contact_heading' => ['Contact heading', 'text'], 'page_contact_intro' => ['Contact introduction', 'textarea'], 'page_contact_email' => ['Contact email', 'email'], 'page_contact_city' => ['Contact city', 'text'], 'page_contact_coverage' => ['Contact coverage line', 'text'],
        'page_contact_form_eyebrow' => ['Contact form eyebrow', 'text'], 'page_contact_offices_eyebrow' => ['Contact offices eyebrow', 'text'], 'page_contact_offices' => ['Contact offices, one per line as Name | Location | Email | Accent (red or grey)', 'textarea'],
        'page_contact_name_placeholder' => ['Contact name field label', 'text'], 'page_contact_organisation_placeholder' => ['Contact organisation field label', 'text'], 'page_contact_email_placeholder' => ['Contact email field label', 'text'], 'page_contact_subject_placeholder' => ['Contact subject field label', 'text'], 'page_contact_message_placeholder' => ['Contact message field label', 'text'],
        'page_contact_social_eyebrow' => ['Contact social eyebrow', 'text'], 'page_contact_socials' => ['Contact social links, one per line as URL setting key | Label | Handle', 'textarea'],
        'page_contact_subject_options' => ['Contact enquiry choices, one per line as value | label', 'textarea'], 'page_contact_submit_label' => ['Contact submit button label', 'text'], 'page_contact_success_heading' => ['Contact success heading', 'text'], 'page_contact_success_message' => ['Contact success message', 'textarea'], 'page_contact_error_message' => ['Contact error message', 'textarea'],
        'page_careers_eyebrow' => ['Careers eyebrow', 'text'], 'page_careers_heading' => ['Careers heading', 'text'], 'page_application_email' => ['Applications email', 'email'],
        'page_careers_positions_label' => ['Careers: positions count label', 'text'],
        'page_careers_requirements_label' => ['Careers: requirements label', 'text'],
        'page_careers_apply_label' => ['Careers: Apply button label', 'text'],
        'page_careers_close_label' => ['Careers: Close button label', 'text'],
        'page_careers_form_label' => ['Careers: application form label', 'text'],
        'page_careers_name_placeholder' => ['Careers: name placeholder', 'text'],
        'page_careers_email_placeholder' => ['Careers: email placeholder', 'text'],
        'page_careers_phone_placeholder' => ['Careers: phone placeholder', 'text'],
        'page_careers_cover_placeholder' => ['Careers: cover letter placeholder', 'text'],
        'page_careers_cv_label' => ['Careers: CV attachment prompt (display only)', 'text'],
        'page_careers_cv_help' => ['Careers: CV attachment help text', 'text'],
        'page_careers_submit_label' => ['Careers: submit label', 'text'],
        'page_careers_success_heading' => ['Careers: success heading', 'text'],
        'page_careers_success_message' => ['Careers: success message', 'textarea'],
        'page_careers_error_message' => ['Careers: error message', 'textarea'],
        'page_careers_empty_message' => ['Careers: empty positions message', 'textarea'],
        'page_careers_eoe_eyebrow' => ['Careers: equal opportunity eyebrow', 'text'],
        'page_careers_eoe_heading' => ['Careers: equal opportunity heading', 'text'],
        'page_careers_eoe_copy' => ['Careers: equal opportunity copy', 'textarea'],
        'page_careers_stats' => ['Careers: workforce statistics, one per line as Value | Label', 'textarea'],
        'page_experts_all_label' => ['Pool of Experts: all filter label', 'text'],
        'page_experts_cta_eyebrow' => ['Pool of Experts: CTA eyebrow', 'text'],
        'page_experts_cta_heading' => ['Pool of Experts: CTA heading', 'text'],
        'page_experts_cta_copy' => ['Pool of Experts: CTA introduction', 'textarea'],
        'page_experts_cta_button' => ['Pool of Experts: CTA button label', 'text'],
        'page_experts_cta_url' => ['Pool of Experts: CTA button URL', 'url'],
        'page_involvement_tracks' => ['Get Involved: paths, one per line as ID | Label | Subtitle | Description | Background hex', 'textarea'],
        'page_involvement_capabilities' => ['Get Involved: capabilities, one per line as path ID | capability', 'textarea'],
        'page_involvement_form_eyebrow' => ['Get Involved: form eyebrow', 'text'],
        'page_involvement_form_heading' => ['Get Involved: form heading', 'text'],
        'page_involvement_form_intro' => ['Get Involved: form introduction', 'text'],
        'page_involvement_name_placeholder' => ['Get Involved: name placeholder', 'text'],
        'page_involvement_role_placeholder' => ['Get Involved: role placeholder', 'text'],
        'page_involvement_email_placeholder' => ['Get Involved: email placeholder', 'text'],
        'page_involvement_organisation_placeholder' => ['Get Involved: organisation placeholder', 'text'],
        'page_involvement_message_placeholder' => ['Get Involved: message placeholder', 'text'],
        'page_involvement_submit_label' => ['Get Involved: submit label', 'text'],
        'page_involvement_success_message' => ['Get Involved: success message', 'textarea'],
        'page_involvement_error_message' => ['Get Involved: error message', 'textarea'],
        'page_involvement_email' => ['Get Involved: enquiry recipient email', 'email'],
        'page_partners_directory_eyebrow' => ['Partners directory eyebrow', 'text'],
        'page_partners_directory_heading' => ['Partners directory heading', 'text'],
        'page_partners_directory_intro' => ['Partners directory introduction', 'textarea'],
        'page_partners_empty_message' => ['Partners directory empty message', 'text'],
        'page_portfolio_card_link' => ['Portfolio card link label', 'text'],
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
function src_menu_fallback() {
    $items = [
        'Home' => ['Impact' => home_url('/#impact'), 'Portfolio' => home_url('/#portfolio'), 'Programmes' => home_url('/#programmes'), 'Partners' => home_url('/#partners'), 'Testimonials' => home_url('/#testimonials'), 'News' => home_url('/#news')],
        'Our Story' => ['About SRC' => src_page_url('about'), 'Leadership' => get_post_type_archive_link('team_member'), 'Pool of Experts' => src_page_url('pool-of-experts'), 'Employment' => src_page_url('careers'), 'Short-term Consultancies' => src_page_url('careers'), 'Internships' => src_page_url('careers'), 'Subcontracts (Firms & Consultancies)' => src_page_url('careers')],
    ];
    echo '<ul class="primary-menu">';
    foreach ($items as $label => $children) {
        echo '<li class="menu-item menu-item-has-children"><button class="nav-parent-button" type="button" aria-expanded="false"><span>' . esc_html($label) . '</span><span class="nav-parent-indicator" aria-hidden="true">+</span></button><ul class="sub-menu" aria-hidden="true" inert>';
        foreach ($children as $child => $url) echo '<li class="menu-item"><a class="nav-link" href="' . esc_url($url) . '">' . esc_html($child) . '</a></li>';
        echo '</ul></li>';
    }
    foreach (['What We Do' => src_page_url('what-we-do'), 'Portfolio' => home_url('/portfolio/'), 'Programmes' => get_post_type_archive_link('programme'), 'Contact' => src_page_url('contact')] as $label => $url) echo '<li class="menu-item"><a class="nav-link" href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>';
    echo '</ul>';
}

function src_registration_copy_fields() {
    $fields = [
        'registration_dialog_aria' => ['Registration dialog accessible label', 'text'], 'registration_heading' => ['Registration dialog heading', 'text'], 'registration_close_label' => ['Close registration accessible label', 'text'], 'registration_progress_label' => ['Registration progress accessible label', 'text'],
        'registration_step_programme' => ['Registration step: programme', 'text'], 'registration_step_details' => ['Registration step: details', 'text'], 'registration_step_payment' => ['Registration step: payment', 'text'], 'registration_step_confirmation' => ['Registration step: confirmation', 'text'],
        'registration_choose_programme_heading' => ['Choose programme heading', 'text'], 'registration_available_programmes_label' => ['Available programmes accessible label', 'text'], 'registration_continue_label' => ['Continue button label', 'text'], 'registration_details_heading' => ['Registration details heading', 'text'], 'registration_back_to_programme' => ['Back to programme button label', 'text'],
        'registration_phone_label' => ['Registration phone label', 'text'], 'registration_phone_placeholder' => ['Registration phone placeholder', 'text'], 'registration_email_label' => ['Registration email label', 'text'], 'registration_email_placeholder' => ['Registration email placeholder', 'text'], 'registration_consent_copy' => ['Registration consent statement', 'textarea'], 'registration_continue_payment' => ['Continue to payment button label', 'text'],
        'registration_payment_heading' => ['Payment step heading', 'text'], 'registration_ready_copy' => ['Ready for checkout message', 'textarea'], 'registration_programme_label' => ['Registration programme summary label', 'text'], 'registration_fee_label' => ['Registration fee label', 'text'], 'registration_continue_checkout' => ['Continue to secure checkout label', 'text'], 'registration_payment_pending_copy' => ['Unpriced programme message', 'textarea'], 'registration_ask_about_label' => ['Ask about programme link label', 'text'], 'registration_back_to_details' => ['Back to details button label', 'text'],
        'registration_confirmation_kicker' => ['Payment confirmation eyebrow', 'text'], 'registration_checking_payment' => ['Checking payment message', 'text'], 'registration_waiting_payment_copy' => ['Payment pending message', 'textarea'], 'registration_order_label' => ['Order number label', 'text'], 'registration_receipt_label' => ['Payment receipt label', 'text'], 'registration_retry_label' => ['Retry payment link label', 'text'], 'registration_return_home_label' => ['Return home link label', 'text'], 'registration_rolling_dates_label' => ['Rolling applications date label', 'text'], 'registration_fee_tbc_label' => ['Unconfirmed fee label', 'text'],
        'registration_paid_heading' => ['Payment success heading', 'text'], 'registration_paid_message' => ['Payment success message', 'textarea'], 'registration_paid_email_message' => ['Payment success email message (use %s for email)', 'text'], 'registration_failed_heading' => ['Payment failure heading', 'text'], 'registration_failed_message' => ['Payment failure message', 'text'], 'registration_pending_email_message' => ['Payment pending email message', 'text'], 'registration_waiting_heading' => ['Waiting payment heading', 'text'], 'registration_waiting_message' => ['Waiting payment instructions', 'textarea'], 'registration_waiting_email_message' => ['Waiting email message (use %s for email)', 'text'], 'registration_phone_error' => ['Phone validation message', 'text'], 'registration_email_error' => ['Email validation message', 'text'], 'registration_consent_error' => ['Consent validation message', 'text'], 'registration_preparing_checkout' => ['Checkout preparation status', 'text'], 'registration_checkout_error' => ['Checkout error message', 'text'],
    ];
    return $fields;
}
function src_registration_copy_defaults() {
    return [
        'registration_dialog_aria' => 'Programme registration', 'registration_heading' => 'Programme registration', 'registration_close_label' => 'Close registration', 'registration_progress_label' => 'Registration progress',
        'registration_step_programme' => 'Select Programme', 'registration_step_details' => 'Your Details', 'registration_step_payment' => 'Payment', 'registration_step_confirmation' => 'Confirmation',
        'registration_choose_programme_heading' => 'Choose your programme', 'registration_available_programmes_label' => 'Available programmes', 'registration_continue_label' => 'Continue', 'registration_details_heading' => 'Tell us about you', 'registration_back_to_programme' => 'Back',
        'registration_phone_label' => 'Phone number', 'registration_phone_placeholder' => '7XXXXXXXX', 'registration_email_label' => 'Email address', 'registration_email_placeholder' => 'you@example.com', 'registration_consent_copy' => 'I consent to SRC processing my information for programme registration and communication.', 'registration_continue_payment' => 'Continue to payment',
        'registration_payment_heading' => 'Complete payment', 'registration_ready_copy' => 'Your registration is ready for secure checkout. Available payment methods, including M-Pesa when enabled, will appear there.', 'registration_programme_label' => 'Programme', 'registration_fee_label' => 'Fee', 'registration_continue_checkout' => 'Continue to secure checkout', 'registration_payment_pending_copy' => 'The programme fee is still being confirmed. Checkout will open once SRC publishes a payable fee for this programme.', 'registration_ask_about_label' => 'Ask SRC about this programme', 'registration_back_to_details' => 'Back to your details',
        'registration_confirmation_kicker' => 'Programme registration', 'registration_checking_payment' => 'Checking your payment', 'registration_waiting_payment_copy' => 'We are waiting for your payment provider to confirm the transaction. Please complete the M-PESA prompt on your phone.', 'registration_order_label' => 'Order', 'registration_receipt_label' => 'M-PESA receipt', 'registration_retry_label' => 'Retry payment', 'registration_return_home_label' => 'Return to homepage', 'registration_rolling_dates_label' => 'Rolling applications', 'registration_fee_tbc_label' => '[TBC]',
        'registration_paid_heading' => 'Your payment is confirmed', 'registration_paid_message' => 'Thank you for registering. Your place has been confirmed and our team will follow up with programme details.', 'registration_paid_email_message' => 'We have sent your order confirmation to %s. Please check your inbox and spam folder.', 'registration_failed_heading' => 'Payment was not completed', 'registration_failed_message' => 'Your payment was not completed. You can try again.', 'registration_pending_email_message' => 'Your confirmation email will be sent after the payment is confirmed.', 'registration_waiting_heading' => 'Waiting for payment confirmation', 'registration_waiting_message' => 'Complete the M-PESA prompt on your phone. This window will update when your payment is confirmed.', 'registration_waiting_email_message' => 'Your confirmation email will be sent to %s after payment.', 'registration_phone_error' => 'Enter 9 digits starting with 7 or 1.', 'registration_email_error' => 'Enter a valid email address.', 'registration_consent_error' => 'Consent is required to continue.', 'registration_preparing_checkout' => 'Preparing checkout…', 'registration_checkout_error' => 'Checkout could not be started. Please try again.',
    ];
}
function src_settings_fields() {
    return array_merge([
        'hero_meta' => ['Hero meta line', 'text'], 'hero_title' => ['Hero title, use a new line for each line', 'textarea'], 'hero_accent_line' => ['Hero accent line', 'text'], 'hero_copy' => ['Hero description', 'textarea'], 'hero_image_url' => ['Hero image URL', 'url'], 'header_cta_kicker' => ['Header CTA kicker', 'text'], 'header_cta_label' => ['Header CTA label', 'text'], 'header_cta_url' => ['Header CTA destination URL', 'url'],
        'hero_programmes_label' => ['Homepage primary action', 'text'], 'hero_story_label' => ['Homepage story action', 'text'], 'hero_enrolment_label' => ['Homepage enrolment strip label', 'text'], 'hero_enrolling_badge' => ['Enrolment badge label', 'text'], 'hero_enrolment_action' => ['Enrolment action label', 'text'], 'hero_dates_tbc_label' => ['Undated cohort label', 'text'], 'hero_countdown_days_label' => ['Countdown days label', 'text'], 'hero_countdown_hours_label' => ['Countdown hours label', 'text'], 'hero_countdown_minutes_label' => ['Countdown minutes label', 'text'], 'hero_countdown_seconds_label' => ['Countdown seconds label', 'text'],
        'impact_eyebrow' => ['Impact section eyebrow', 'text'], 'impact_heading' => ['Impact section heading', 'text'], 'impact_details_label' => ['Impact tile detail action', 'text'], 'impact_programmes_label' => ['Impact programmes link label', 'text'],
        'footprint_intro' => ['Footprint introduction', 'textarea'], 'map_view_aria' => ['Map view accessible label', 'text'], 'map_africa_label' => ['Africa map tab label', 'text'], 'map_kenya_label' => ['Kenya map tab label', 'text'], 'map_hq_label' => ['Map headquarters label', 'text'], 'map_country_programmes_label' => ['Map country action label (use %s for the country)', 'text'], 'map_county_count_label' => ['County count label (use %d for the count)', 'text'],
        'projects_all_label' => ['Projects list link label', 'text'], 'projects_open_label' => ['Project tile action label', 'text'], 'projects_count_label' => ['Project programme count (use %d for count)', 'text'], 'project_folder_goals_label' => ['Project folder goals tab description', 'text'], 'project_folder_stories_label' => ['Project folder stories tab label', 'text'], 'project_folder_involve_label' => ['Project folder involvement tab label', 'text'], 'project_folder_close_label' => ['Project folder close accessible label', 'text'], 'project_folder_view_programme_label' => ['Project folder programme link label', 'text'], 'project_folder_learn_label' => ['Project folder learn more label', 'text'],
        'home_partners_eyebrow' => ['Homepage partners eyebrow', 'text'], 'home_partners_heading' => ['Homepage partners heading', 'text'], 'home_programmes_eyebrow' => ['Homepage programmes eyebrow', 'text'], 'home_programmes_heading' => ['Homepage programmes heading', 'text'], 'home_all_programmes_label' => ['Homepage all programmes link', 'text'], 'home_programme_register_label' => ['Homepage programme registration label', 'text'], 'home_programme_coming_soon_label' => ['Homepage upcoming programme label', 'text'],
        'home_testimonial_eyebrow' => ['Homepage testimonial eyebrow', 'text'], 'home_testimonial_video_coming_label' => ['Testimonial video fallback label', 'text'], 'home_testimonial_story_label' => ['Testimonial story action label', 'text'], 'home_testimonial_prev_label' => ['Previous testimonial label', 'text'], 'home_testimonial_next_label' => ['Next testimonial label', 'text'],
        'home_news_eyebrow' => ['Homepage news eyebrow', 'text'], 'home_news_heading' => ['Homepage news heading', 'text'], 'home_all_news_label' => ['Homepage all news link', 'text'], 'home_news_card_link_label' => ['Homepage news card link', 'text'],
        'home_report_eyebrow' => ['Homepage report eyebrow', 'text'], 'home_report_download_label' => ['Homepage report download label', 'text'], 'home_report_cover_label' => ['Homepage report cover label', 'text'], 'home_report_cover_caption' => ['Homepage report cover caption', 'text'],
        'newsletter_intro' => ['Homepage newsletter introduction', 'textarea'], 'newsletter_link_label' => ['Homepage newsletter link label', 'text'], 'footer_story_title' => ['Footer Our Story column heading', 'text'], 'footer_programmes_title' => ['Footer Programmes column heading', 'text'], 'footer_more_title' => ['Footer More column heading', 'text'], 'footer_legal_title' => ['Footer Legal column heading', 'text'], 'footer_contact_title' => ['Footer contact column heading', 'text'], 'footer_company_name' => ['Footer company name', 'text'],
        'footer_leadership_label' => ['Footer Leadership archive link label', 'text'],
        'commerce_hero_eyebrow' => ['Shop and checkout brand eyebrow', 'text'], 'checkout_step_label' => ['Checkout step label', 'text'], 'checkout_step_intro' => ['Checkout instructions', 'textarea'], 'checkout_confirmation_label' => ['Registration confirmation label', 'text'], 'checkout_confirmation_intro' => ['Registration confirmation instructions', 'textarea'],
        'shop_catalog_eyebrow' => ['Shop catalogue eyebrow', 'text'], 'shop_catalog_heading' => ['Shop catalogue heading', 'text'], 'shop_catalog_intro' => ['Shop catalogue introduction', 'textarea'], 'shop_empty_heading' => ['Shop empty state heading', 'text'], 'shop_empty_intro' => ['Shop empty state introduction', 'textarea'], 'shop_empty_action' => ['Shop empty state link label', 'text'],
        'sector_eyebrow' => ['Sectors eyebrow', 'text'], 'sector_heading' => ['Sectors heading', 'text'], 'sector_intro' => ['Sectors introduction', 'textarea'],
        'audience_eyebrow' => ['Audience eyebrow', 'text'], 'audience_heading' => ['Audience heading', 'text'], 'footprint_eyebrow' => ['Footprint eyebrow', 'text'], 'footprint_heading' => ['Footprint heading', 'text'], 'partners_eyebrow' => ['Partners eyebrow', 'text'], 'partners_heading' => ['Partners heading', 'text'],
        'projects_eyebrow' => ['Projects eyebrow', 'text'], 'projects_heading' => ['Projects heading', 'text'], 'project_archive_eyebrow' => ['Our Work archive eyebrow', 'text'], 'project_archive_heading' => ['Our Work archive heading', 'text'], 'project_archive_intro' => ['Our Work archive introduction', 'textarea'], 'team_eyebrow' => ['The Team archive eyebrow', 'text'], 'team_heading' => ['The Team archive heading', 'text'],
        'programmes_eyebrow' => ['Programmes eyebrow', 'text'], 'programmes_heading' => ['Programmes heading', 'text'], 'quote_eyebrow' => ['Quote eyebrow', 'text'], 'quote_text' => ['Quote text', 'textarea'], 'quote_attribution' => ['Quote attribution', 'text'], 'quote_summary' => ['Quote summary', 'textarea'],
        'programmes_archive_image_url' => ['Programmes archive hero image URL', 'url'], 'programmes_archive_eyebrow' => ['Programmes archive hero eyebrow', 'text'], 'programmes_archive_heading' => ['Programmes archive hero heading', 'text'], 'programmes_archive_emphasis' => ['Programmes archive heading emphasis', 'text'], 'programmes_archive_intro' => ['Programmes archive introduction', 'textarea'], 'programmes_archive_primary_action' => ['Programmes archive primary action', 'text'], 'programmes_archive_secondary_action' => ['Programmes archive secondary action', 'text'],
        'programmes_filter_aria' => ['Programme filter navigation label', 'text'], 'programmes_filter_status_aria' => ['Programme status filter label', 'text'], 'programmes_filter_all' => ['All programmes filter label', 'text'], 'programmes_filter_current' => ['Current programmes filter label', 'text'], 'programmes_filter_upcoming' => ['Upcoming programmes filter label', 'text'], 'programmes_filter_past' => ['Past programmes filter label', 'text'], 'programmes_filter_signature' => ['Signature programme filter label', 'text'],
        'programmes_current_eyebrow' => ['Current programmes section eyebrow', 'text'], 'programmes_current_heading' => ['Current programmes section heading', 'text'], 'programmes_current_intro' => ['Current programmes section introduction', 'textarea'], 'programmes_upcoming_eyebrow' => ['Upcoming programmes section eyebrow', 'text'], 'programmes_upcoming_heading' => ['Upcoming programmes section heading', 'text'], 'programmes_upcoming_intro' => ['Upcoming programmes section introduction', 'textarea'], 'programmes_past_eyebrow' => ['Past programmes section eyebrow', 'text'], 'programmes_past_heading' => ['Past programmes section heading', 'text'], 'programmes_past_intro' => ['Past programmes section introduction', 'textarea'], 'programmes_empty_message' => ['No matching programmes message', 'text'],
        'news_archive_eyebrow' => ['News archive eyebrow', 'text'], 'news_archive_heading' => ['News archive heading', 'text'], 'news_archive_emphasis' => ['News archive heading emphasis', 'text'], 'news_archive_intro' => ['News archive introduction', 'textarea'], 'news_archive_action' => ['News archive action label', 'text'], 'news_archive_index_label' => ['News archive side label', 'text'], 'news_archive_empty_message' => ['News archive empty message', 'text'], 'news_card_link_label' => ['News card link label', 'text'],
        'newsletter_eyebrow' => ['Newsletter eyebrow', 'text'], 'newsletter_heading' => ['Newsletter heading', 'text'], 'newsletter_email_label' => ['Newsletter email label', 'text'], 'newsletter_placeholder' => ['Newsletter email placeholder', 'text'], 'newsletter_button_label' => ['Newsletter button label', 'text'], 'site_description' => ['Footer description', 'textarea'], 'county_footprint' => ['County footprint', 'textarea'], 'contact_city' => ['Contact city', 'text'], 'contact_email' => ['Contact email', 'email'], 'contact_phone' => ['Contact phone', 'text'],
        'social_linkedin' => ['LinkedIn URL', 'url'], 'social_x' => ['X URL', 'url'], 'social_facebook' => ['Facebook URL', 'url'], 'social_youtube' => ['YouTube URL', 'url'],
    ], src_registration_copy_fields(), src_leadership_settings_schema());
}
function src_settings_defaults() {
    return array_merge([
        'hero_meta' => 'Est. 2012 · Nairobi, Kenya · 15+ Counties', 'hero_title' => "Building Enterprises\nFrom Good\nto Great.", 'hero_accent_line' => 'From Good', 'hero_copy' => 'SRC partners with donors, financial institutions and commercial enterprises to unlock growth for SMEs and cooperatives across Kenya and East Africa.', 'hero_image_url' => 'https://images.unsplash.com/photo-1687422808311-a776f467a468?w=1800&h=1000&fit=crop&auto=format', 'header_cta_kicker' => 'Start a conversation', 'header_cta_label' => 'Partner with us', 'header_cta_url' => '/get-involved/',
        'hero_programmes_label' => 'Our Programmes', 'hero_story_label' => 'Watch Our Story', 'hero_enrolment_label' => 'Now Enrolling', 'hero_enrolling_badge' => 'Enrolling now', 'hero_enrolment_action' => 'Enroll now', 'hero_dates_tbc_label' => 'Dates to be announced', 'hero_countdown_days_label' => 'Days', 'hero_countdown_hours_label' => 'Hrs', 'hero_countdown_minutes_label' => 'Min', 'hero_countdown_seconds_label' => 'Sec',
        'impact_eyebrow' => 'Our Impact', 'impact_heading' => "Growth you can see.\nChange you can measure.", 'impact_details_label' => 'More about %s', 'impact_programmes_label' => 'See the programmes →',
        'footprint_intro' => 'SRC is headquartered in Nairobi with active programmes across Kenya and six other African countries.', 'map_view_aria' => 'Map view', 'map_africa_label' => 'Africa', 'map_kenya_label' => 'Kenya', 'map_hq_label' => 'HQ', 'map_country_programmes_label' => 'View %s programmes', 'map_county_count_label' => '%d counties',
        'projects_all_label' => 'See all case studies →', 'projects_open_label' => 'Open project →', 'projects_count_label' => '%d programmes', 'project_folder_goals_label' => 'Goals, results and stories', 'project_folder_stories_label' => 'See more about this project', 'project_folder_involve_label' => 'Get involved', 'project_folder_close_label' => 'Close programme details', 'project_folder_view_programme_label' => 'View programme →', 'project_folder_learn_label' => 'Learn more →',
        'home_partners_eyebrow' => 'Trusted By', 'home_partners_heading' => 'Our Partners', 'home_programmes_eyebrow' => 'Applications Open', 'home_programmes_heading' => 'Now Enrolling', 'home_all_programmes_label' => 'All programmes', 'home_programme_register_label' => 'Register', 'home_programme_coming_soon_label' => 'Coming soon',
        'home_testimonial_eyebrow' => 'Voices From Our Entrepreneurs', 'home_testimonial_video_coming_label' => 'Story video coming soon', 'home_testimonial_story_label' => 'See their story', 'home_testimonial_prev_label' => '← Prev', 'home_testimonial_next_label' => 'Next →',
        'home_news_eyebrow' => 'From SRC', 'home_news_heading' => 'Latest News & Updates', 'home_all_news_label' => 'All news', 'home_news_card_link_label' => 'Read more',
        'home_report_eyebrow' => 'Annual Publication', 'home_report_download_label' => 'Download PDF', 'home_report_cover_label' => 'Impact report', 'home_report_cover_caption' => 'Enterprises · Partnerships · Growth',
        'newsletter_intro' => 'Stories, updates and practical insights from our work with enterprises across the region.', 'newsletter_link_label' => 'Explore latest news', 'footer_story_title' => 'Our Story', 'footer_programmes_title' => 'Programmes', 'footer_more_title' => 'More', 'footer_legal_title' => 'Legal', 'footer_contact_title' => 'Contact', 'footer_company_name' => 'Smart Regional Consultants Ltd.',
        'footer_leadership_label' => 'Leadership',
        'commerce_hero_eyebrow' => 'Smart Regional Consultants', 'checkout_step_label' => 'Registration · Final step', 'checkout_step_intro' => 'Confirm your contact details, review the programme, and complete your payment securely.', 'checkout_confirmation_label' => 'Registration · Confirmation', 'checkout_confirmation_intro' => 'Your payment status is shown in the registration window. Keep this page open while you complete the M-PESA prompt.',
        'shop_catalog_eyebrow' => 'Open enrolment', 'shop_catalog_heading' => 'Choose a programme', 'shop_catalog_intro' => 'Explore current programmes and start your registration. Programme fees and payment options are confirmed in the registration flow.', 'shop_empty_heading' => 'There are no programmes open for enrolment right now.', 'shop_empty_intro' => 'Browse all programmes to see what is running, coming soon or recently completed.', 'shop_empty_action' => 'Browse all programmes',
        'sector_eyebrow' => 'Value chains', 'sector_heading' => 'Sectors We\nWork Across', 'sector_intro' => "From dairy and poultry to livestock markets — SRC brings deep sector knowledge to enterprise development across Kenya's arid and semi-arid counties.",
        'audience_eyebrow' => 'Thematic focus', 'audience_heading' => 'Who We Serve', 'footprint_eyebrow' => 'Where we work', 'footprint_heading' => 'Our Footprint', 'partners_eyebrow' => 'Trusted by', 'partners_heading' => 'Leading donors, NGOs and enterprises', 'projects_eyebrow' => 'Impact in action', 'projects_heading' => 'Our Projects', 'project_archive_eyebrow' => 'Smart Regional Consultants', 'project_archive_heading' => 'Our work', 'project_archive_intro' => 'Ideas into measurable impact.', 'team_eyebrow' => 'The people behind SRC', 'team_heading' => 'Meet the Team', 'programmes_eyebrow' => 'Build your business', 'programmes_heading' => "Ongoing & Upcoming\nProgrammes", 'quote_eyebrow' => 'From our programmes', 'quote_text' => '“SRC helped us go from a struggling cooperative to a bankable enterprise — in 18 months.”', 'quote_attribution' => 'Women-led dairy cooperative, Isiolo County', 'quote_summary' => 'SRC has supported 2,500+ women entrepreneurs, unlocked $2M+ in SME financing, and operated across 5 African countries.',
        'newsletter_eyebrow' => 'Stay connected', 'newsletter_heading' => 'News worth opening.', 'newsletter_email_label' => 'Email address', 'newsletter_placeholder' => 'you@example.com', 'newsletter_button_label' => 'Subscribe', 'site_description' => 'Nairobi-based enterprise support organisation. Building SMEs from Good to Great across Kenya and East Africa.', 'county_footprint' => 'Nairobi · Mombasa · Kisumu · Nakuru · Garissa · Marsabit · Turkana · Wajir · Mandera · Isiolo · Samburu · Laikipia · Tana River · Kwale · Kajiado', 'contact_city' => 'Nairobi, Kenya', 'contact_email' => 'info@srctech.co.ke', 'contact_phone' => '', 'social_linkedin' => '', 'social_x' => '', 'social_facebook' => '', 'social_youtube' => '',
        'programmes_archive_image_url' => 'https://images.unsplash.com/photo-1776039325240-02916820bfeb?w=2000&q=85&auto=format', 'programmes_archive_eyebrow' => 'Enrolling Now · Upcoming', 'programmes_archive_heading' => 'Grow your business', 'programmes_archive_emphasis' => 'with us.', 'programmes_archive_intro' => 'Training, bootcamps and advisory designed for entrepreneurs ready for their next stage.', 'programmes_archive_primary_action' => 'View Programmes', 'programmes_archive_secondary_action' => 'How registration works',
        'programmes_filter_aria' => 'Programme filters', 'programmes_filter_status_aria' => 'Filter by status', 'programmes_filter_all' => 'All', 'programmes_filter_current' => 'Current', 'programmes_filter_upcoming' => 'Upcoming', 'programmes_filter_past' => 'Past', 'programmes_filter_signature' => 'SRC Signature only',
        'programmes_current_eyebrow' => 'Active Now', 'programmes_current_heading' => 'Current Programmes', 'programmes_current_intro' => 'Programmes currently enrolling or being delivered with our partners.', 'programmes_upcoming_eyebrow' => 'Returning Soon', 'programmes_upcoming_heading' => 'Upcoming', 'programmes_upcoming_intro' => 'Proven programmes preparing for their next cohort.', 'programmes_past_eyebrow' => 'Our Track Record', 'programmes_past_heading' => 'Past Programmes', 'programmes_past_intro' => 'Programmes SRC has delivered with its partners.', 'programmes_empty_message' => 'No programmes match these filters.',
        'news_archive_eyebrow' => 'Ideas from the field', 'news_archive_heading' => 'Latest News', 'news_archive_emphasis' => '& Updates', 'news_archive_intro' => 'Stories, practical insights and programme updates from SRC’s work with enterprises and cooperatives across the region.', 'news_archive_action' => 'Explore stories', 'news_archive_index_label' => 'SRC / JOURNAL', 'news_archive_empty_message' => 'There are no news stories to show yet.', 'news_card_link_label' => 'Read more',
    ], src_registration_copy_defaults(), src_leadership_settings_defaults());
}
function src_register_settings() { register_setting('src_settings', 'src_settings', ['sanitize_callback' => static function ($input) { $clean = []; foreach (src_settings_fields() as $key => $definition) { $value = isset($input[$key]) ? wp_unslash($input[$key]) : ''; $clean[$key] = in_array($definition[1], ['textarea'], true) ? sanitize_textarea_field($value) : ($definition[1] === 'url' ? esc_url_raw($value) : sanitize_text_field($value)); } return $clean; }]); }
add_action('admin_init', 'src_register_settings');
function src_add_settings_page() { add_theme_page('SRC Content Settings', 'SRC Content Settings', 'manage_options', 'src-content-settings', 'src_render_settings_page'); }
add_action('admin_menu', 'src_add_settings_page');
function src_settings_media_picker($hook) {
    if ($hook !== 'appearance_page_src-content-settings') return;
    wp_enqueue_media();
    $script = <<<'JS'
jQuery(function($){$(document).on('click','.src-select-setting-image',function(e){e.preventDefault();var input=$(this).siblings('input');var picker=wp.media({title:'Choose an image',button:{text:'Use image'},library:{type:'image'},multiple:false});picker.on('select',function(){input.val(picker.state().get('selection').first().toJSON().url).trigger('change');});picker.open();});});
JS;
    wp_add_inline_script('jquery-core', $script);
}
add_action('admin_enqueue_scripts', 'src_settings_media_picker');
function src_render_settings_page() {
    if (!current_user_can('manage_options')) return;
    $fields = src_settings_fields(); $settings = array_merge(src_settings_defaults(), get_option('src_settings', []));
    echo '<div class="wrap"><h1>SRC Content Settings</h1><p>Manage homepage, archive, registration dialog and footer copy here. Page-specific content is on each Page form; Team, Programme, Partner, Expert, Job and News records are managed in their own WordPress admin screens.</p><form method="post" action="options.php">';
    settings_fields('src_settings');
    echo '<h2>Registration dialog copy</h2><table class="form-table" role="presentation">';
    foreach (array_keys(src_registration_copy_fields()) as $key) { [$label, $type] = $fields[$key]; $value = $settings[$key] ?? ''; echo '<tr><th scope="row"><label for="src-setting-' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td>'; if ($type === 'textarea') printf('<textarea class="large-text" rows="3" id="src-setting-%1$s" name="src_settings[%1$s]">%2$s</textarea>', esc_attr($key), esc_textarea($value)); else printf('<input class="regular-text" type="text" id="src-setting-%1$s" name="src_settings[%1$s]" value="%2$s">', esc_attr($key), esc_attr($value)); echo '</td></tr>'; }
    echo '</table>';
    echo '<h2>Our Work archive</h2><table class="form-table" role="presentation">';
    foreach (['project_archive_eyebrow','project_archive_heading','project_archive_intro'] as $key) { [$label, $type] = $fields[$key]; $value = $settings[$key] ?? ''; echo '<tr><th scope="row"><label for="src-setting-' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td>'; if ($type === 'textarea') printf('<textarea class="large-text" rows="3" id="src-setting-%1$s" name="src_settings[%1$s]">%2$s</textarea>', esc_attr($key), esc_textarea($value)); else printf('<input class="regular-text" type="text" id="src-setting-%1$s" name="src_settings[%1$s]" value="%2$s">', esc_attr($key), esc_attr($value)); echo '</td></tr>'; }
    echo '</table>';
    foreach (array_merge([['Homepage hero and header', ['hero_meta','hero_title','hero_accent_line','hero_copy','hero_image_url','header_cta_kicker','header_cta_label','header_cta_url','hero_programmes_label','hero_story_label']], ['Homepage section headings', ['sector_eyebrow','sector_heading','sector_intro','audience_eyebrow','audience_heading','footprint_eyebrow','footprint_heading','partners_eyebrow','partners_heading','projects_eyebrow','projects_heading','team_eyebrow','team_heading','programmes_eyebrow','programmes_heading']], ['Homepage enrolment strip', ['hero_enrolment_label','hero_enrolling_badge','hero_enrolment_action','hero_dates_tbc_label','hero_countdown_days_label','hero_countdown_hours_label','hero_countdown_minutes_label','hero_countdown_seconds_label']], ['Homepage impact section', ['impact_eyebrow','impact_heading','impact_details_label','impact_programmes_label']], ['Homepage footprint map', ['footprint_intro','map_view_aria','map_africa_label','map_kenya_label','map_hq_label','map_country_programmes_label','map_county_count_label']], ['Homepage project folders', ['projects_all_label','projects_open_label','projects_count_label','project_folder_goals_label','project_folder_stories_label','project_folder_involve_label','project_folder_close_label','project_folder_view_programme_label','project_folder_learn_label']], ['Homepage partners and programmes', ['home_partners_eyebrow','home_partners_heading','home_programmes_eyebrow','home_programmes_heading','home_all_programmes_label','home_programme_register_label','home_programme_coming_soon_label']], ['Homepage testimonials, news and report', ['home_testimonial_eyebrow','home_testimonial_video_coming_label','home_testimonial_story_label','home_testimonial_prev_label','home_testimonial_next_label','home_news_eyebrow','home_news_heading','home_all_news_label','home_news_card_link_label','home_report_eyebrow','home_report_download_label','home_report_cover_label','home_report_cover_caption']], ['Homepage quote', ['quote_eyebrow','quote_text','quote_attribution','quote_summary']], ['Programmes archive hero and filters', ['programmes_archive_image_url','programmes_archive_eyebrow','programmes_archive_heading','programmes_archive_emphasis','programmes_archive_intro','programmes_archive_primary_action','programmes_archive_secondary_action','programmes_filter_aria','programmes_filter_status_aria','programmes_filter_all','programmes_filter_current','programmes_filter_upcoming','programmes_filter_past','programmes_filter_signature']], ['Programmes archive sections', ['programmes_current_eyebrow','programmes_current_heading','programmes_current_intro','programmes_upcoming_eyebrow','programmes_upcoming_heading','programmes_upcoming_intro','programmes_past_eyebrow','programmes_past_heading','programmes_past_intro','programmes_empty_message']], ['News archive', ['news_archive_eyebrow','news_archive_heading','news_archive_emphasis','news_archive_intro','news_archive_action','news_archive_index_label','news_archive_empty_message','news_card_link_label']], ['Shop and checkout', ['commerce_hero_eyebrow','checkout_step_label','checkout_step_intro','checkout_confirmation_label','checkout_confirmation_intro','shop_catalog_eyebrow','shop_catalog_heading','shop_catalog_intro','shop_empty_heading','shop_empty_intro','shop_empty_action']], ['Newsletter and footer', ['newsletter_eyebrow','newsletter_heading','newsletter_intro','newsletter_link_label','newsletter_email_label','newsletter_placeholder','newsletter_button_label','site_description','county_footprint','contact_city','contact_email','contact_phone','footer_story_title','footer_programmes_title','footer_more_title','footer_legal_title','footer_contact_title','footer_company_name','footer_leadership_label','social_linkedin','social_x','social_facebook','social_youtube']]], src_leadership_settings_groups()) as [$heading, $keys]) {
        echo '<h2>' . esc_html($heading) . '</h2><table class="form-table" role="presentation">';
        foreach ($keys as $key) { [$label, $type] = $fields[$key]; $value = $settings[$key] ?? ''; echo '<tr><th scope="row"><label for="src-setting-' . esc_attr($key) . '">' . esc_html($label) . '</label></th><td>'; if ($type === 'textarea') printf('<textarea class="large-text" rows="3" id="src-setting-%1$s" name="src_settings[%1$s]">%2$s</textarea>', esc_attr($key), esc_textarea($value)); elseif ($type === 'url' && (str_ends_with($key, '_image_url') || $key === 'hero_image_url')) printf('<span class="src-media-field"><input class="regular-text" type="url" id="src-setting-%1$s" name="src_settings[%1$s]" value="%2$s"><button type="button" class="button src-select-setting-image">Choose image</button></span>', esc_attr($key), esc_attr($value)); else printf('<input class="regular-text" type="%1$s" id="src-setting-%2$s" name="src_settings[%2$s]" value="%3$s">', esc_attr($type), esc_attr($key), esc_attr($value)); echo '</td></tr>'; }
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

function src_migrate_footprint_summaries() {
    if (get_option('src_footprint_summaries_migrated')) return;
    $replacements = [
        'KE' => ['HQ Country · 15+ Counties · Active since 2012' => 'HQ Country · Active since 2012'],
        'TZ' => ['Active since 2018 · Women economic empowerment' => 'Pan-African virtual programme reach'],
        'MW' => ['Active since 2019 · Enterprise development' => 'Regional programme reach'],
        'ZM' => ['Active since 2019 · Regional enterprise support' => 'Regional programme reach'],
        'ZA' => ['Active since 2018 · Women economic empowerment' => 'Pan-African virtual programme reach'],
        'SN' => ['Active since 2018 · Women-led enterprise focus' => 'Pan-African virtual programme reach'],
    ];
    $terms = get_terms(['taxonomy' => 'programme_country', 'hide_empty' => false]);
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $code = strtoupper((string) get_term_meta($term->term_id, 'src_country_code', true));
            $current = (string) get_term_meta($term->term_id, 'src_country_summary', true);
            if (isset($replacements[$code][$current])) update_term_meta($term->term_id, 'src_country_summary', $replacements[$code][$current]);
        }
    }
    update_option('src_footprint_summaries_migrated', '1', false);
}
add_action('init', 'src_migrate_footprint_summaries', 21);

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

/** Put the HQ country first while preserving any display order set by an admin. */
function src_migrate_uganda_footprint_order() {
    if (get_option('src_footprint_uganda_order_migrated')) return;
    $uganda = get_term_by('slug', 'ug', 'programme_country');
    if ($uganda && !is_wp_error($uganda) && get_term_meta($uganda->term_id, 'src_country_order', true) === '') {
        update_term_meta($uganda->term_id, 'src_country_order', 15);
    }
    update_option('src_footprint_uganda_order_migrated', '1');
}
add_action('init', 'src_migrate_uganda_footprint_order', 30);

function src_get_footprint_countries() {
    $terms = get_terms(['taxonomy' => 'programme_country', 'hide_empty' => false]);
    if (is_wp_error($terms)) return [];
    $countries = [];
    $regional_codes = ['TZ', 'MW', 'ZM', 'ZA', 'SN'];
    $source_programme_order = array_flip(['sheconnects', 'casha', 'kcb-mifugo-ni-mali', 'investor-readiness', 'export-readiness', 'webinar-series', 'herventure', 'road-to-markets', 'road-to-growth', 'lift-nk', 'kuza-jamii', 'seed', 'ande-circularity', 'graca-machel-investor-readiness', 'road-to-leadership', 'palladium-usaid', 'giz-sme-loop', 'giz-business-scouts', 'village-capital-bds', 'usaid-acdi-voca', 'ntf-iii-it-ites']);
    $country_summaries = [
        'KE' => 'HQ Country · Active since 2012',
        'UG' => 'Regional programme delivery',
        'TZ' => 'Pan-African virtual programme reach',
        'MW' => 'Regional programme reach',
        'ZM' => 'Regional programme reach',
        'ZA' => 'Pan-African virtual programme reach',
        'SN' => 'Pan-African virtual programme reach',
    ];
    $sort_programmes = static function ($programmes) use ($source_programme_order) {
        usort($programmes, static function ($a, $b) use ($source_programme_order) {
            $a_order = $source_programme_order[$a->post_name] ?? PHP_INT_MAX;
            $b_order = $source_programme_order[$b->post_name] ?? PHP_INT_MAX;
            return ($a_order <=> $b_order) ?: strcasecmp($a->post_title, $b->post_title);
        });
        return $programmes;
    };
    $all_v3_programmes = get_posts([
        'post_type' => 'programme', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC',
        'meta_query' => [['key' => '_src_v3_imported', 'value' => '1']],
    ]);
    $all_v3_programmes = $sort_programmes($all_v3_programmes);
    $regional_programmes = array_values(array_filter($all_v3_programmes, static fn($programme) => strpos((string) get_post_meta($programme->ID, '_programme_reach', true), 'Africa') === 0));
    foreach ($terms as $term) {
        $code = strtoupper((string) get_term_meta($term->term_id, 'src_country_code', true));
        if (!preg_match('/^[A-Z]{2}$/', $code)) continue;
        $programmes = ($code === 'KE') ? $all_v3_programmes : get_posts([
            'post_type' => 'programme', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC',
            'meta_query' => [['key' => '_src_v3_imported', 'value' => '1']],
            'tax_query' => [['taxonomy' => 'programme_country', 'field' => 'term_id', 'terms' => $term->term_id]],
        ]);
        $programmes = $sort_programmes($programmes);
        if (!$programmes && in_array($code, $regional_codes, true)) $programmes = $regional_programmes;
        $programme_rows = array_map(static function ($programme) {
            $partners = array_values(array_filter((array) src_field('programme_partners', $programme->ID, [])));
            $funders = array_values(array_filter((array) src_field('programme_funders', $programme->ID, [])));
            $title = html_entity_decode(get_the_title($programme), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return [
                'title' => html_entity_decode(src_field('programme_hero_title', $programme->ID, src_field('programme_short_name', $programme->ID, $title)), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'partner' => (string) ($funders[0] ?? $partners[0] ?? src_field('programme_partner', $programme->ID, 'SRC')),
                'status' => (string) src_field('programme_status', $programme->ID, 'past'),
                'dates' => (string) src_field('programme_dates', $programme->ID, src_field('programme_start', $programme->ID, '')),
                'image' => src_image_url($programme->ID, ''),
                'url' => get_permalink($programme),
            ];
        }, $programmes);
        $count = count($programme_rows);
        if (!$count) continue;
        $countries[$code] = [
            'name' => html_entity_decode($term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'count' => sprintf(_n('%d programme', '%d programmes', $count, 'src'), $count),
            'meta' => get_term_meta($term->term_id, 'src_country_summary', true) ?: ($term->description ?: ($country_summaries[$code] ?? 'Regional programme reach')),
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

/** Country-level map of the active Kenya counties represented in the v3 design. */
function src_get_footprint_counties() {
    $county_slugs = ['nairobi', 'garissa', 'isiolo', 'kajiado', 'kisumu', 'kwale', 'laikipia', 'lamu', 'mandera', 'marsabit', 'mombasa', 'nakuru', 'samburu', 'tana-river', 'turkana', 'wajir', 'machakos', 'kitui', 'west-pokot', 'taita-taveta', 'baringo', 'makueni'];
    $nairobi_slugs = ['investor-readiness', 'export-readiness', 'webinar-series', 'herventure', 'road-to-markets', 'road-to-growth', 'ande-circularity'];
    $counties = [];
    foreach ($county_slugs as $order => $slug) {
        $term = get_term_by('slug', $slug, 'programme_county');
        if (!$term || is_wp_error($term)) continue;
        if ($slug === 'nairobi') {
            $programmes = [];
            foreach ($nairobi_slugs as $programme_slug) {
                $programme = get_page_by_path($programme_slug, OBJECT, 'programme');
                if ($programme && get_post_status($programme) === 'publish' && get_post_meta($programme->ID, '_src_v3_imported', true) === '1') $programmes[] = $programme;
            }
        } else {
            $programmes = get_posts([
                'post_type' => 'programme', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC',
                'meta_query' => [['key' => '_src_v3_imported', 'value' => '1']],
                'tax_query' => [['taxonomy' => 'programme_county', 'field' => 'term_id', 'terms' => $term->term_id]],
            ]);
        }
        $source_programme_order = array_flip(['sheconnects', 'casha', 'kcb-mifugo-ni-mali', 'investor-readiness', 'export-readiness', 'webinar-series', 'herventure', 'road-to-markets', 'road-to-growth', 'lift-nk', 'kuza-jamii', 'seed', 'ande-circularity', 'graca-machel-investor-readiness', 'road-to-leadership', 'palladium-usaid', 'giz-sme-loop', 'giz-business-scouts', 'village-capital-bds', 'usaid-acdi-voca', 'ntf-iii-it-ites']);
        usort($programmes, static function ($a, $b) use ($source_programme_order) {
            $a_order = $source_programme_order[$a->post_name] ?? PHP_INT_MAX;
            $b_order = $source_programme_order[$b->post_name] ?? PHP_INT_MAX;
            return ($a_order <=> $b_order) ?: strcasecmp($a->post_title, $b->post_title);
        });
        $rows = array_map(static function ($programme) {
            $partners = (array) src_field('programme_partners', $programme->ID, []);
            $funders = (array) src_field('programme_funders', $programme->ID, []);
            return [
                'title' => html_entity_decode(src_field('programme_hero_title', $programme->ID, src_field('programme_short_name', $programme->ID, get_the_title($programme))), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'partner' => (string) ($funders[0] ?? $partners[0] ?? src_field('programme_partner', $programme->ID, 'SRC')),
                'status' => (string) src_field('programme_status', $programme->ID, 'past'),
                'dates' => (string) src_field('programme_dates', $programme->ID, src_field('programme_start', $programme->ID, '')),
                'image' => src_image_url($programme->ID, ''), 'url' => get_permalink($programme),
            ];
        }, $programmes);
        $county_name = $slug === 'taita-taveta' && $term->name === 'Taita-Taveta' ? 'Taita Taveta' : $term->name;
        $counties[strtoupper('county-' . $slug)] = [
            'name' => html_entity_decode($county_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'count' => sprintf(_n('%d programme', '%d programmes', count($rows), 'src'), count($rows)),
            'meta' => $slug === 'nairobi' ? 'SRC headquarters · Nairobi' : sprintf(_n('%d active or completed programme', '%d active or completed programmes', count($rows), 'src'), count($rows)),
            'scale' => (float) (get_term_meta($term->term_id, 'src_country_scale', true) ?: 1.55), 'x' => '0%', 'y' => '0%',
            'hq' => $slug === 'nairobi', 'order' => $order, 'programmes' => $rows,
        ];
    }
    return $counties;
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
    if (get_option('src_footprint_seed_version') === '2') return;

    $countries = [
        'kenya' => ['Kenya', 'KE', 'HQ Country · Active since 2012', 10, 1, 1.56, '-12%', '7%'],
        'tanzania' => ['Tanzania', 'TZ', 'Pan-African virtual programme reach', 20, 0, 1.62, '-10%', '-1%'],
        'malawi' => ['Malawi', 'MW', 'Regional programme reach', 30, 0, 1.72, '-8%', '-8%'],
        'zambia' => ['Zambia', 'ZM', 'Regional programme reach', 40, 0, 1.66, '-2%', '-8%'],
        'south-africa' => ['South Africa', 'ZA', 'Pan-African virtual programme reach', 50, 0, 1.62, '1%', '-18%'],
        'senegal' => ['Senegal', 'SN', 'Pan-African virtual programme reach', 60, 0, 1.68, '18%', '13%'],
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

    update_option('src_footprint_seed_version', '2');
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

/** Seed the v3 reference records once; editors own all values after creation. */
function src_seed_v3_reference_records() {
    if (get_option('src_v3_reference_records_seeded')) return;

    $testimonials = [
        ['amina-hassan', 'Amina Hassan', 'Hodari Foods · Nairobi', 'A stronger business begins with the confidence to lead it.', "Amina joined Road to Growth with a loyal customer base and an ambition to supply retailers beyond her neighbourhood.\nWith sharper financial records and a focused route-to-market plan, she grew her team and secured her first formal supply agreement.", 'photo-1637997840862-9aafaf835eed'],
        ['mercy-wanjiku', 'Mercy Wanjiku', 'Wema Dairy · Laikipia', 'From informal producer to a trusted market supplier.', "Mercy’s cooperative had strong production but little negotiating power and inconsistent access to buyers.\nSRC helped the members strengthen governance, improve quality systems and approach the market as one enterprise.", 'photo-1746401809231-4c1ad2c1e161'],
        ['fatuma-noor', 'Fatuma Noor', 'Nuru Livestock · Isiolo', 'Local knowledge, connected to new opportunity.', "Fatuma knew the livestock trade. What she needed was the structure to price, plan and access working capital.\nToday she buys from more pastoralist households and supplies a broader network of formal traders.", 'photo-1736967327745-b66c0e2a609d'],
    ];
    foreach ($testimonials as $index => [$slug, $person, $business, $headline, $paragraphs, $image]) {
        if (get_page_by_path($slug, OBJECT, 'src_testimonial')) continue;
        $post_id = wp_insert_post(['post_type' => 'src_testimonial', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $person, 'post_content' => $paragraphs]);
        if (!$post_id || is_wp_error($post_id)) continue;
        update_post_meta($post_id, '_story_person', $person);
        update_post_meta($post_id, '_story_business', $business);
        update_post_meta($post_id, '_story_headline', $headline);
        update_post_meta($post_id, '_story_paragraphs', preg_split('/\R/', $paragraphs));
        update_post_meta($post_id, '_story_image_url', 'https://images.unsplash.com/' . $image . '?w=1000&h=700&fit=crop&auto=format&q=82');
        update_post_meta($post_id, '_story_order', $index + 1);
        update_post_meta($post_id, '_story_featured', '1');
    }

    $news = [
        ['road-to-growth-returning-soon', 'Road to Growth returning soon', 'Programme News', '12 Sep 2026', 'The next Road to Growth cohort for women founders is preparing to open.', 'photo-1637997840862-9aafaf835eed', 'road-to-growth'],
        ['what-investment-readiness-means', 'What investment readiness really means', 'Insights', '28 Aug 2026', 'Five practical signals that show an enterprise is prepared to engage growth capital.', 'photo-1573167507387-6b4b98cb7c13', ''],
        ['cooperatives-stronger-markets', 'Cooperatives creating stronger markets', 'Field Notes', '04 Aug 2026', 'Lessons from producer groups building trust, better governance and reliable buyer relationships.', 'photo-1746401809231-4c1ad2c1e161', ''],
    ];
    foreach ($news as $index => [$slug, $title, $category, $date, $excerpt, $image, $programme_slug]) {
        if (get_page_by_path($slug, OBJECT, 'src_news_item')) continue;
        $post_id = wp_insert_post(['post_type' => 'src_news_item', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $excerpt, 'post_excerpt' => $excerpt]);
        if (!$post_id || is_wp_error($post_id)) continue;
        update_post_meta($post_id, '_news_category', $category);
        update_post_meta($post_id, '_news_date_label', $date);
        update_post_meta($post_id, '_news_image_url', 'https://images.unsplash.com/' . $image . '?w=850&h=560&fit=crop&auto=format&q=82');
        update_post_meta($post_id, '_news_order', $index + 1);
        update_post_meta($post_id, '_news_featured', '1');
        update_post_meta($post_id, '_news_programme_slug', $programme_slug);
    }

    $companies = [
        ['mavuno-harvests', 'Mavuno Harvests', 'Climate-smart agribusiness', 'MH'], ['pesalink-africa', 'PesaLink Africa', 'Inclusive digital finance', 'PA'],
        ['jua-kali-works', 'Jua Kali Works', 'Light manufacturing', 'JK'], ['safari-hauliers', 'Safari Hauliers', 'Last-mile logistics', 'SH'],
        ['zuri-market', 'Zuri Market', 'Consumer retail', 'ZM'], ['afya-bora-clinics', 'Afya Bora Clinics', 'Community health services', 'AB'],
        ['tamu-foods-kenya', 'Tamu Foods Kenya', 'Food processing and agribusiness', 'TF'], ['imara-pay', 'Imara Pay', 'SME financial technology', 'IP'],
        ['chuma-craft', 'Chuma Craft', 'Sustainable manufacturing', 'CC'], ['twende-express', 'Twende Express', 'Regional delivery and logistics', 'TE'],
        ['soko-yetu', 'Soko Yetu', 'Neighbourhood retail', 'SY'], ['uzima-health', 'Uzima Health', 'Digital health services', 'UH'],
    ];
    foreach ($companies as $index => [$slug, $name, $sector, $monogram]) {
        if (get_page_by_path($slug, OBJECT, 'portfolio_company')) continue;
        $post_id = wp_insert_post(['post_type' => 'portfolio_company', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $name]);
        if (!$post_id || is_wp_error($post_id)) continue;
        update_post_meta($post_id, '_portfolio_sector', $sector);
        update_post_meta($post_id, '_portfolio_abbreviation', $monogram);
        update_post_meta($post_id, '_portfolio_order', $index + 1);
        update_post_meta($post_id, '_portfolio_featured', '1');
    }

    $partners = [
        ['cbf', 'Cherie Blair Foundation for Women', 'cherie-blair-foundation.png'], ['dhl', 'DHL', 'dhl.png'],
        ['boma', 'BOMA Project', 'boma-project.png'], ['herventure', 'HerVenture', 'her-venture-transparent.svg'],
        ['care', 'CARE', 'care.svg'], ['mastercard-foundation', 'Mastercard Foundation', ''], ['ande', 'ANDE', 'ande.png'],
        ['graca-machel-trust', 'Graça Machel Trust', ''], ['afrishela', 'Afrishela', ''], ['sheconnects', 'SheConnects', ''],
        ['fcdo', 'FCDO', ''], ['fao', 'FAO', 'fao.svg'], ['kcb-foundation', 'KCB Foundation', ''], ['snv', 'SNV', 'snv.svg'],
        ['ilri', 'ILRI', ''], ['village-enterprise', 'Village Enterprise', 'village-enterprise.svg'], ['giz', 'GIZ', 'giz.png'],
        ['goal', 'GOAL', 'goal.png'], ['kcdf', 'KCDF', 'kcdf.png'], ['united-nations', 'United Nations', 'united-nations.png'],
        ['usaid', 'USAID', 'usaid.png'], ['village-capital', 'Village Capital', 'village-capital.png'],
        ['world-bank', 'World Bank', 'world-bank.png'], ['wwf', 'WWF', 'wwf.png'],
    ];
    // Keep seeded theme-owned logos origin-relative so they work over both
    // Valet HTTP and production HTTPS, including when this seed runs under WP-CLI.
    $partner_asset_uri = trailingslashit(wp_make_link_relative(get_template_directory_uri())) . 'assets/partner-logos/';
    foreach ($partners as $index => [$slug, $name, $logo]) {
        $partner = get_page_by_path($slug, OBJECT, 'src_partner_item');
        if (!$partner) {
            $partner_id = wp_insert_post(['post_type' => 'src_partner_item', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $name]);
            if (!$partner_id || is_wp_error($partner_id)) continue;
        } else {
            $partner_id = (int) $partner->ID;
            if ($partner->post_title !== $name) wp_update_post(['ID' => $partner_id, 'post_title' => $name]);
        }
        update_post_meta($partner_id, '_partner_name', $name);
        update_post_meta($partner_id, '_partner_logo_url', $logo ? $partner_asset_uri . $logo : '');
        update_post_meta($partner_id, '_partner_order', $index + 1);
        update_post_meta($partner_id, '_partner_marquee', '1');
    }

    if (!get_page_by_path('portfolio', OBJECT, 'page')) {
        wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'portfolio', 'post_title' => 'Portfolio']);
    }
    if (!get_page_by_path('impact-report-2025', OBJECT, 'src_publication')) {
        $publication_id = wp_insert_post(['post_type' => 'src_publication', 'post_status' => 'publish', 'post_name' => 'impact-report-2025', 'post_title' => 'Impact Report 2025', 'post_content' => 'Explore the enterprises, partnerships and ideas that shaped a year of measurable growth across our region.']);
        if ($publication_id && !is_wp_error($publication_id)) {
            update_post_meta($publication_id, '_publication_year', 2025);
            update_post_meta($publication_id, '_publication_featured', '1');
        }
    }

    update_option('src_v3_reference_records_seeded', '1');
}
add_action('init', 'src_seed_v3_reference_records', 33);

function src_seed_page_content() {
    if (get_option('src_page_content_seed_version') === '20') return;
    $pages = [
        'about' => [
            'page_hero_title' => "Built in Kenya, for Africa's enterprises.", 'page_hero_emphasis' => 'for', 'page_hero_eyebrow' => 'About SRC', 'page_hero_intro' => 'For over a decade, SRC has helped SMEs and cooperatives grow from good to great through strategy, finance and markets.', 'page_hero_image_url' => 'https://images.unsplash.com/photo-1637149014802-d917e6255c2d?auto=format&fit=crop&w=2000&q=85', 'page_story_eyebrow' => 'Our Story', 'page_story_heading' => 'A firm built on proximity to the entrepreneur.', 'page_story_body' => "SRC was founded in 2012 by Catherine Gitonga, a development professional with more than 20 years of enterprise development experience across Kenya and East Africa. The firm grew from a conviction that SMEs — particularly in agriculture, livestock and cooperatives — were underserved by both the market and the donor system.\n\nOver 12 years, SRC has grown from a boutique advisory firm into a 51-strong team operating across 15+ Kenyan counties and 5 additional African countries, delivering multi-million-dollar programmes for some of the world's leading development funders.\n\nBut the mission has never changed: unlock real growth, for real businesses, in real communities.", 'page_story_image_url' => 'https://images.unsplash.com/photo-1776039213401-f95bfd9c516a?w=800&h=1000&fit=crop&auto=format', 'page_story_stat' => '2012', 'page_story_stat_label' => 'Founded', 'page_pillars' => "Vision | A thriving SME ecosystem across Kenya and East Africa — inclusive, innovative and globally competitive.\nMission | To provide high-quality, context-specific business support services that enable SMEs and cooperatives to achieve sustainable growth.\nPurpose | Building Enterprises from Good to Great — every intervention, every county, every entrepreneur.", 'page_values_eyebrow' => 'What We Stand For', 'page_values_heading' => 'Core Values', 'page_values' => "Integrity & Safeguarding | We hold ourselves to the highest ethical standards in all our work with clients, partners and communities.\nExcellence | We deliver measurable, lasting results — not outputs. Quality is non-negotiable in every engagement.\nInnovation | We bring creative, context-specific solutions to enterprise development challenges across diverse geographies.\nTeaming | We believe the best outcomes emerge from genuine collaboration — with clients, partners and the communities we serve.", 'page_theory_eyebrow' => 'How Change Happens', 'page_theory_heading' => 'Theory of Change', 'page_theory_steps' => "01 | Diagnosis | Assess enterprise health, market position and growth barriers\n02 | Intervention Design | Co-design bespoke support programmes with clients and donors\n03 | Delivery | Deploy technical assistance, training and market linkage services\n04 | Linkage to Finance | Connect investable enterprises to appropriate capital sources\n05 | Market Access | Facilitate off-take agreements and formal market entry\n06 | Scale & Sustain | Embed systems and skills for long-term enterprise growth", 'page_about_stats' => "51 | Staff\n15+ | Kenyan Counties\n25+ | Associate Consultants\n5 | African Countries\n12+ | Years of Impact", 'page_about_cta_eyebrow' => 'Meet the team', 'page_about_cta_heading' => 'The people behind SRC', 'page_about_cta_button' => 'Meet Our Leadership',
        ],
        'what-we-do' => [
            'page_hero_title' => 'Building SMEs from good to great', 'page_hero_emphasis' => 'from good to great', 'page_hero_eyebrow' => 'WHAT WE DO', 'page_hero_intro' => 'We strengthen how enterprises run, prepare them for capital and connect them to buyers.', 'page_hero_image_url' => 'https://images.unsplash.com/photo-1687422808248-f807f4ea2a2e?auto=format&fit=crop&w=2000&q=85',
            'page_service_pillars' => "bms | 01 | Business Model Strengthening | We fix the fundamentals | Diagnosing and strengthening the financial, operational and market fundamentals of SMEs and cooperatives, so they are built to last and ready to grow. | SMEs, cooperatives and producer groups | #B3262C | #8B0005 | #B3262C\nirf | 02 | Investor Readiness & Linkage to Finance | We make enterprises bankable | Preparing enterprises for debt and equity investment through bankability, governance and pitch-readiness, then connecting them directly to financiers. | Growth-stage SMEs and cooperatives seeking finance | #6E7073 | #55575A | #626467\nml | 03 | Market Linkages | We open the market door | Connecting SMEs to buyers, off-takers, formal markets and trade networks, both domestic and regional. | Producers, cooperatives, processors and off-takers | #F58220 | #B85D10 | #F58220",
            'page_service_delivery_eyebrow' => 'How we deliver', 'page_service_delivery_heading' => 'Practical support, close to the enterprise.', 'page_service_delivery_methods' => "Training & bootcamps\nAdvisory\nInvestor matchmaking\nDigital learning\nField teams in 13 counties",
            'page_service_cta_eyebrow' => 'Partner with us', 'page_service_cta_heading' => 'Build the next generation of resilient, investable enterprises.', 'page_service_cta_button' => 'Start a partnership →',
        ],
        'contact' => [
            'page_hero_title' => "Let's build something great.", 'page_hero_emphasis' => 'something great', 'page_hero_eyebrow' => 'Nairobi, Kenya', 'page_hero_intro' => "Tell us about your enterprise, programme or partnership and we'll get back to you within two working days.", 'page_hero_image_url' => 'https://images.unsplash.com/photo-1664181220731-06219378d8c7?auto=format&fit=crop&w=2000&q=85',
            'page_contact_form_eyebrow' => 'Send a Message', 'page_contact_offices_eyebrow' => 'Our Offices', 'page_contact_offices' => "Nairobi HQ | Nairobi, Kenya | info@srctech.co.ke | red\nField Operations | SRC county programme teams across Kenya | field@srctech.co.ke | grey",
            'page_contact_social_eyebrow' => 'Follow SRC', 'page_contact_socials' => "social_linkedin | LinkedIn | /company/smart-regional-consultants\nsocial_x | Twitter / X | @SRCKenya\nsocial_facebook | Facebook | /SmartRegionalConsultants\nsocial_youtube | YouTube | SRC Kenya",
            'page_contact_subject_options' => "general | General enquiry\nprogrammes | Programmes & registration\npartnerships | Partnerships\ncareers | Careers\nmedia | Media\nfield-operations | Field operations", 'page_contact_submit_label' => 'Send Message', 'page_contact_success_heading' => 'Message received', 'page_contact_success_message' => 'Thank you for reaching out. We respond to all enquiries within 2 working days.', 'page_contact_error_message' => 'We could not send your message just now. Please try again or email SRC directly.',
            'page_contact_email' => 'info@srctech.co.ke', 'page_contact_city' => 'Nairobi, Kenya', 'page_contact_coverage' => 'SRC county programme teams across Kenya',
        ],
        'careers' => [
            'page_hero_title' => 'Do work that moves enterprises.',
            'page_hero_emphasis' => 'moves',
            'page_hero_eyebrow' => 'Careers',
            'page_hero_intro' => "Join our team or our Pool of Experts and help build Africa's next generation of businesses.",
            'page_hero_image_url' => 'https://images.unsplash.com/photo-1573164574511-73c773193279?auto=format&fit=crop&w=2000&q=85',
            'page_careers_eyebrow' => 'Current Openings',
            'page_careers_heading' => 'Open Positions',
            'page_careers_positions_label' => 'positions',
            'page_careers_requirements_label' => 'Requirements',
            'page_careers_apply_label' => 'Apply Now',
            'page_careers_close_label' => 'Close',
            'page_careers_form_label' => 'Apply for',
            'page_careers_name_placeholder' => 'Full name',
            'page_careers_email_placeholder' => 'Email address',
            'page_careers_phone_placeholder' => 'Phone number',
            'page_careers_cover_placeholder' => "Cover letter — tell us why you're the right fit (max 300 words)",
            'page_careers_cv_label' => '+ Attach CV / Resume (PDF preferred)',
            'page_careers_submit_label' => 'Submit Application',
            'page_careers_success_heading' => 'Application received',
            'page_careers_success_message' => 'Thank you for applying to %s. We will be in touch within 5 working days.',
            'page_careers_error_message' => 'We could not send your application just now. Please try again.',
            'page_careers_empty_message' => 'There are no open positions right now. Please check back soon.',
            'page_careers_eoe_eyebrow' => 'Equal Opportunity',
            'page_careers_eoe_heading' => 'SRC is an equal opportunity employer.',
            'page_careers_eoe_copy' => "We are committed to creating an inclusive, diverse and equitable workplace. We actively encourage applications from women, young people, persons with disabilities and candidates from Kenya's marginalised communities.",
            'page_careers_stats' => "51 | Team Members\n60% | Women on Staff\n15+ | Counties Represented",
            'page_application_email' => 'hello@smartregionalconsultants.org',
        ],
        'get-involved' => [
            'page_hero_title' => 'Get Involved',
            'page_hero_eyebrow' => 'Get Involved',
            'page_hero_intro' => "Whether you're a donor, a commercial enterprise or a collaborator — there is a way to work with SRC.",
            'page_hero_image_url' => 'https://images.unsplash.com/photo-1637149014802-d917e6255c2d?auto=format&fit=crop&w=2000&q=85',
            'page_involvement_tracks' => "donor | Partner With Us | For Donors & NGOs | SRC works with bilateral donors, foundations and NGOs to design, staff and deliver enterprise development programmes across Kenya and East Africa. We bring technical depth, county-level relationships and a proven track record with USAID, FCDO, World Bank, Mastercard Foundation and more. | #B3262C\nenterprise | Work With Us | For Commercial Enterprises | SRC supports commercial enterprises — processors, retailers, financial institutions and agribusinesses — to build supplier bases, develop SME customer segments and enter new East African markets. We bridge the gap between corporate ambition and smallholder reality. | #4A0E12\ncollaborate | Support & Collaborate | Research, Academia & Media | SRC collaborates with researchers, academic institutions, media organisations and think-tanks on enterprise development, livestock systems and women's economic empowerment in East Africa. We are open to data sharing, co-publication and thought leadership partnerships. | #F58220",
            'page_involvement_capabilities' => "donor | Programme design and technical scoping\ndonor | Staffing — team leaders and technical experts\ndonor | Monitoring, evaluation and learning\ndonor | County-level relationships and community trust\ndonor | Full programme management capability\nenterprise | Supplier and SME customer base development\nenterprise | Market entry and distribution strategy\nenterprise | Smallholder aggregation and quality systems\nenterprise | Last-mile linkage facilitation\nenterprise | Partnership with county governments\ncollaborate | Research partnerships and data sharing\ncollaborate | Co-publication and thought leadership\ncollaborate | Field access and practitioner insight\ncollaborate | Media and documentary collaboration\ncollaborate | Academic and policy engagement",
            'page_involvement_form_eyebrow' => 'Get in touch',
            'page_involvement_form_heading' => 'Start a conversation',
            'page_involvement_form_intro' => 'We respond to all enquiries within 2 working days.',
            'page_involvement_name_placeholder' => 'Your name',
            'page_involvement_role_placeholder' => 'Role / Title',
            'page_involvement_email_placeholder' => 'Email address',
            'page_involvement_organisation_placeholder' => 'Organisation',
            'page_involvement_message_placeholder' => 'Tell us about your interest or programme idea...',
            'page_involvement_submit_label' => 'Send Message',
            'page_involvement_success_message' => "Thank you — we'll be in touch shortly.",
            'page_involvement_error_message' => 'We could not send your message just now. Please try again or email SRC directly.',
            'page_involvement_email' => '',
        ],
        'pool-of-experts' => [
            'page_hero_title' => 'Pool of Experts',
            'page_hero_eyebrow' => 'Pool of Experts',
            'page_hero_intro' => "SRC's Pay-As-You-Go consultant network gives enterprises and programmes access to specialist knowledge — on demand, without overhead.",
            'page_hero_image_url' => 'https://images.unsplash.com/photo-1637149014802-d917e6255c2d?auto=format&fit=crop&w=2000&q=85',
            'page_experts_all_label' => 'All Categories',
            'page_experts_cta_eyebrow' => 'Flexible expertise',
            'page_experts_cta_heading' => 'Need a specialist for your programme?',
            'page_experts_cta_copy' => 'Our Pay-As-You-Go model means you get the exact expertise you need, for exactly as long as you need it.',
            'page_experts_cta_button' => 'Engage Our Experts',
            'page_experts_cta_url' => '',
        ],
        'partners' => ['page_hero_title' => 'Clients & Partners', 'page_hero_eyebrow' => 'Smart Regional Consultants', 'page_hero_intro' => 'Leading donors, NGOs and enterprises building stronger ecosystems with SRC.', 'page_partners_directory_eyebrow' => 'Working together', 'page_partners_directory_heading' => 'A shared commitment to enterprise growth', 'page_partners_directory_intro' => '', 'page_partners_empty_message' => 'Partner profiles will be added here.'],
        'portfolio' => ['page_hero_title' => 'Our Portfolio', 'page_hero_eyebrow' => 'Our Work', 'page_hero_intro' => 'Programmes designed to help enterprises and cooperatives grow across Kenya and East Africa.', 'page_portfolio_card_link' => 'Explore programme'],
    ];
    $legacy_seed_values = [
        'what-we-do' => [
            'page_hero_title' => 'What we do',
            'page_hero_eyebrow' => 'Smart Regional Consultants',
            'page_hero_intro' => 'We turn enterprise potential into practical, sustainable growth.',
        ],
        'contact' => [
            'page_hero_title' => 'Contact',
            'page_hero_eyebrow' => 'Smart Regional Consultants',
            'page_hero_intro' => 'Let’s talk about the challenge you are trying to solve.',
            'page_contact_email' => 'hello@smartregionalconsultants.org',
            'page_contact_coverage' => 'Working across 15+ counties and 5 African countries',
        ],
        'get-involved' => [
            'page_hero_title' => 'Get involved',
            'page_hero_eyebrow' => 'Smart Regional Consultants',
            'page_hero_intro' => 'Stronger enterprises need stronger partnerships.',
        ],
        'pool-of-experts' => [
            'page_hero_title' => 'Pool of experts',
            'page_hero_eyebrow' => 'Smart Regional Consultants',
            'page_hero_intro' => 'The right expertise, close to the work.',
        ],
        'careers' => [
            'page_hero_title' => 'Careers',
            'page_hero_eyebrow' => 'Smart Regional Consultants',
            'page_hero_intro' => 'Bring your perspective. Build something that matters.',
            'page_careers_eyebrow' => 'Join the team',
            'page_careers_heading' => 'Open positions',
            'page_careers_success_message' => 'Thank you for applying. We will be in touch within 5 working days.',
        ],
    ];
    foreach ($pages as $slug => $fields) {
        $career_page_id = $slug === 'careers' ? absint(get_option('src_careers_page_id_v3')) : 0;
        $page = $career_page_id ? get_post($career_page_id) : get_page_by_path($slug);
        if (!$page) continue;
        foreach ($fields as $key => $value) {
            $current = get_post_meta($page->ID, '_' . $key, true);
            $can_migrate_legacy = isset($legacy_seed_values[$slug][$key]) && $current === $legacy_seed_values[$slug][$key];
            if ($current === '' || $can_migrate_legacy) update_post_meta($page->ID, '_' . $key, $value);
        }
    }
    $new_fields = [
        'about' => ['page_story_image_alt' => 'SRC team training session', 'page_about_cta_url' => '/leadership/'],
        'what-we-do' => ['page_service_cta_url' => '/get-involved/'],
        'contact' => ['page_contact_name_placeholder' => 'Full name', 'page_contact_organisation_placeholder' => 'Organisation', 'page_contact_email_placeholder' => 'Email address', 'page_contact_subject_placeholder' => 'Subject / Enquiry type', 'page_contact_message_placeholder' => 'Your message...'],
        'careers' => ['page_careers_cv_help' => 'Click to browse or drag and drop a PDF, DOC or DOCX file (max 10 MB)'],
    ];
    foreach ($new_fields as $slug => $fields) {
        $page = get_page_by_path($slug);
        if (!$page) continue;
        foreach ($fields as $key => $value) {
            if (get_post_meta($page->ID, '_' . $key, true) === '') update_post_meta($page->ID, '_' . $key, $value);
        }
    }
    $service_page = get_page_by_path('what-we-do');
    if ($service_page) {
        $service_defaults = [
            'page_service_pillar_aria_label' => 'Three pillars of enterprise growth',
            'page_service_who_label' => "Who it's for",
            'page_service_explore_label' => 'Explore programmes',
            'page_service_overview_label' => 'Overview',
            'page_service_pillar_programmes_label' => 'Programmes in this pillar',
            'page_service_audience_label' => 'Audience',
            'page_service_duration_label' => 'Duration',
            'page_service_delivery_label' => 'Delivery',
            'page_service_journey_label' => 'Learning journey',
            'page_service_journey_empty_label' => 'Journey details coming soon.',
            'page_service_close_label' => 'Close programme cabinet',
            'page_service_view_programme_label' => 'View programme',
            'page_service_all_programmes_label' => 'See all programmes',
            'page_service_current_programmes_label' => 'Explore current programmes',
            'page_service_get_notified_label' => 'Get notified',
            'page_service_register_label' => 'Register',
            'page_service_partner_label' => 'Partner with us',
            'page_service_get_involved_label' => 'Get involved',
            'page_service_status_enrolling' => 'Enrolling now',
            'page_service_status_upcoming' => 'Upcoming',
            'page_service_status_delivery' => 'In delivery',
            'page_service_status_completed' => 'Completed',
        ];
        foreach ($service_defaults as $key => $value) {
            if (get_post_meta($service_page->ID, '_' . $key, true) === '') update_post_meta($service_page->ID, '_' . $key, $value);
        }
    }
    update_option('src_page_content_seed_version', '20');
}
add_action('init', 'src_seed_page_content', 32);

function src_seed_project_detail_copy_fields() {
    if (get_option('src_project_detail_copy_seeded') === '1') return;
    foreach (get_posts(['post_type' => 'project', 'post_status' => 'publish', 'posts_per_page' => -1]) as $project) {
        if (get_post_meta($project->ID, '_project_detail_eyebrow', true) === '') update_post_meta($project->ID, '_project_detail_eyebrow', 'Selected work');
        if (get_post_meta($project->ID, '_project_detail_heading', true) === '') update_post_meta($project->ID, '_project_detail_heading', 'The work behind the outcome.');
    }
    update_option('src_project_detail_copy_seeded', '1');
}
add_action('init', 'src_seed_project_detail_copy_fields', 35);

function src_seed_programme_pillar_order() {
    if (get_option('src_programme_pillar_order_seeded') === '1') return;
    $orders = [
        'bms' => ['seed', 'lift-nk', 'kuza-jamii', 'road-to-growth', 'road-to-leadership', 'herventure', 'webinar-series', 'giz-sme-loop', 'village-capital-bds'],
        'irf' => ['investor-readiness', 'ande-circularity', 'graca-machel-investor-readiness', 'kcb-mifugo-ni-mali', 'palladium-usaid'],
        'ml' => ['export-readiness', 'road-to-markets', 'casha', 'sheconnects', 'giz-business-scouts', 'usaid-acdi-voca', 'ntf-iii-it-ites'],
    ];
    foreach ($orders as $pillar => $slugs) {
        foreach ($slugs as $index => $slug) {
            $programme = get_page_by_path($slug, OBJECT, 'programme');
            if (!$programme || get_post_meta($programme->ID, '_programme_pillar_order', true) !== '') continue;
            update_post_meta($programme->ID, '_programme_pillar_order', $index + 1);
            if (!has_term($pillar, 'programme_pillar', $programme)) wp_set_object_terms($programme->ID, [$pillar], 'programme_pillar', true);
        }
    }
    update_option('src_programme_pillar_order_seeded', '1');
}
add_action('init', 'src_seed_programme_pillar_order', 34);

function src_admin_notice() { if (!get_option('src_seeded')) echo '<div class="notice notice-info"><p>Smart Regional Consultants theme is ready. Activate it to create the starter content and navigation.</p></div>'; }
add_action('admin_notices', 'src_admin_notice');

function src_contact_handler() {
    $contact_page = get_page_by_path('contact');
    $redirect = $contact_page ? get_permalink($contact_page) : home_url('/contact/');
    $post_value = static function ($key) {
        $value = $_POST[$key] ?? '';
        return is_scalar($value) ? wp_unslash((string) $value) : '';
    };
    if (!wp_verify_nonce(sanitize_text_field($post_value('src_contact_nonce')), 'src_contact_submit')) {
        wp_safe_redirect(add_query_arg('contact_status', 'error', $redirect));
        exit;
    }
    $name = sanitize_text_field($post_value('name'));
    $email = sanitize_email($post_value('email'));
    $organisation = sanitize_text_field($post_value('organisation'));
    $subject_value = sanitize_title($post_value('subject'));
    $message = sanitize_textarea_field($post_value('message'));
    $subject_options = $contact_page ? src_parse_pairs(get_post_meta($contact_page->ID, '_page_contact_subject_options', true)) : [];
    $allowed_subjects = array_column($subject_options, 0);
    $subject_label = '';
    foreach ($subject_options as [$value, $label]) if ($value === $subject_value) $subject_label = $label;
    $subject_valid = $subject_value === '' || in_array($subject_value, $allowed_subjects, true);
    $recipient = $contact_page ? sanitize_email(get_post_meta($contact_page->ID, '_page_contact_email', true)) : '';
    if (!is_email($recipient)) $recipient = sanitize_email(get_option('admin_email'));
    $sent = false;
    if ($name !== '' && is_email($email) && $message !== '' && $subject_valid && is_email($recipient)) {
        $mail_subject = $subject_label !== '' ? 'SRC website enquiry: ' . $subject_label : 'SRC website enquiry from ' . $name;
        $mail_body = "Name: {$name}\nEmail: {$email}\nOrganisation: {$organisation}\nEnquiry: " . ($subject_label ?: 'Not selected') . "\n\n{$message}";
        $sent = wp_mail($recipient, $mail_subject, $mail_body, ['Reply-To: ' . $name . ' <' . $email . '>']);
    }
    wp_safe_redirect(add_query_arg('contact_status', $sent ? 'sent' : 'error', $redirect));
    exit;
}
add_action('admin_post_src_contact', 'src_contact_handler');
add_action('admin_post_nopriv_src_contact', 'src_contact_handler');

require_once get_template_directory() . '/inc/v3-leadership.php';
