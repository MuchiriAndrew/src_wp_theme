<?php
/**
 * SRC v3 leadership directory: export-backed defaults, editable team records,
 * department/county taxonomies, and accessible card markup.
 */

function src_leadership_department_defaults() {
    return [
        'senior-management' => ['name' => 'Senior Management Team', 'nickname' => 'The Round Table', 'tagline' => 'Where big decisions are made, together.', 'order' => 1, 'background' => 'cream', 'illustration' => 'the-round-table'],
        'finance-operations' => ['name' => 'Finance & Operations', 'nickname' => 'The Vault', 'tagline' => 'Every shilling accounted for.', 'order' => 2, 'background' => 'cream', 'illustration' => 'the-vault'],
        'data-quality' => ['name' => 'Data Quality, M&E', 'nickname' => 'The Dashboard', 'tagline' => 'Turning field data into evidence.', 'order' => 3, 'background' => 'white', 'illustration' => 'the-dashboard'],
        'strategy-consulting' => ['name' => 'Strategy & Consulting', 'nickname' => 'The Think Tank', 'tagline' => 'The strategy behind every programme.', 'order' => 4, 'background' => 'cream', 'illustration' => 'the-think-tank'],
        'programmes-team' => ['name' => 'Programmes', 'nickname' => 'The Brainstorm Studio', 'tagline' => 'Our people on the ground across 13 counties.', 'order' => 5, 'background' => 'white', 'illustration' => 'the-brainstorm-studio'],
        'drivers' => ['name' => 'Drivers', 'nickname' => 'The Pilots', 'tagline' => 'Getting the team to the field safely.', 'order' => 6, 'background' => 'cream', 'illustration' => 'the-pilots'],
    ];
}

function src_leadership_settings_schema() {
    return [
        'leadership_hero_eyebrow' => ['Leadership hero eyebrow', 'text'],
        'leadership_hero_title_before' => ['Leadership hero title before emphasis', 'text'],
        'leadership_hero_emphasis' => ['Leadership hero emphasized title', 'text'],
        'leadership_hero_image_url' => ['Leadership hero image URL', 'url'],
        'leadership_meet_button' => ['Leadership hero primary button', 'text'],
        'leadership_join_button' => ['Leadership hero secondary button', 'text'],
        'leadership_board_placeholders' => ['Board profile placeholders, one label per line', 'textarea'],
        'leadership_board_formal' => ['Board section formal heading', 'text'],
        'leadership_board_nickname' => ['Board section nickname', 'text'],
        'leadership_board_tagline' => ['Board section tagline', 'text'],
        'leadership_board_placeholder_copy' => ['Board profile placeholder copy', 'text'],
        'leadership_programmes_filter_heading' => ['County team filter heading', 'text'],
        'leadership_programmes_scroll_hint' => ['County filter scroll hint', 'text'],
        'leadership_county_filter_aria' => ['County filter accessible label', 'text'],
        'leadership_county_all_label' => ['All counties filter label', 'text'],
        'leadership_cta_heading' => ['Leadership closing heading', 'text'],
        'leadership_cta_button' => ['Leadership closing button', 'text'],
        'leadership_profile_eyebrow' => ['Leadership profile eyebrow', 'text'],
        'leadership_profile_back_label' => ['Leadership profile return link', 'text'],
    ];
}

function src_leadership_settings_defaults() {
    return [
        'leadership_hero_eyebrow' => 'OUR PEOPLE',
        'leadership_hero_title_before' => 'The team behind',
        'leadership_hero_emphasis' => 'the growth',
        'leadership_hero_image_url' => 'https://images.unsplash.com/photo-1573164574511-73c773193279?auto=format&fit=crop&w=2000&q=85',
        'leadership_meet_button' => 'Meet the team ↓',
        'leadership_join_button' => 'Join us → Careers',
        'leadership_board_placeholders' => "Director seat 01\nDirector seat 02\nDirector seat 03\nDirector seat 04",
        'leadership_board_formal' => 'Board of Directors',
        'leadership_board_nickname' => 'The Compass',
        'leadership_board_tagline' => "Setting SRC's direction.",
        'leadership_board_placeholder_copy' => 'Profile forthcoming',
        'leadership_programmes_filter_heading' => 'County Program Manager/Officer',
        'leadership_programmes_scroll_hint' => 'Swipe to see all counties →',
        'leadership_county_filter_aria' => 'Filter by county',
        'leadership_county_all_label' => 'All',
        'leadership_cta_heading' => 'Want to work with this team?',
        'leadership_cta_button' => 'See open positions',
        'leadership_profile_eyebrow' => 'Leadership profile',
        'leadership_profile_back_label' => 'Back to Leadership',
    ];
}

function src_leadership_settings_groups() {
    return [
        ['Leadership hero and actions', ['leadership_hero_eyebrow','leadership_hero_title_before','leadership_hero_emphasis','leadership_hero_image_url','leadership_meet_button','leadership_join_button']],
        ['Leadership board and county filter', ['leadership_board_formal','leadership_board_nickname','leadership_board_tagline','leadership_board_placeholders','leadership_board_placeholder_copy','leadership_programmes_filter_heading','leadership_programmes_scroll_hint','leadership_county_filter_aria','leadership_county_all_label']],
        ['Leadership closing call to action', ['leadership_cta_heading','leadership_cta_button']],
        ['Leadership profile pages', ['leadership_profile_eyebrow','leadership_profile_back_label']],
    ];
}

function src_leadership_register_department_fields() {
    register_term_meta('team_department', '_team_department_nickname', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => static fn() => current_user_can('manage_categories')]);
    register_term_meta('team_department', '_team_department_nav_label', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => static fn() => current_user_can('manage_categories')]);
    register_term_meta('team_department', '_team_department_order', ['type' => 'integer', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'absint', 'auth_callback' => static fn() => current_user_can('manage_categories')]);
    register_term_meta('team_department', '_team_department_background', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_key', 'auth_callback' => static fn() => current_user_can('manage_categories')]);
    register_term_meta('team_department', '_team_department_illustration', ['type' => 'string', 'single' => true, 'show_in_rest' => true, 'sanitize_callback' => 'sanitize_file_name', 'auth_callback' => static fn() => current_user_can('manage_categories')]);
}
add_action('init', 'src_leadership_register_department_fields', 11);

function src_leadership_department_fields_markup($term = null) {
    $term_id = $term instanceof WP_Term ? $term->term_id : 0;
    $fields = [
        '_team_department_nickname' => ['Section nickname', get_term_meta($term_id, '_team_department_nickname', true), 'For example: The Round Table.'],
        '_team_department_nav_label' => ['Sticky navigation label', get_term_meta($term_id, '_team_department_nav_label', true), 'Defaults to the department name.'],
        '_team_department_order' => ['Section order', get_term_meta($term_id, '_team_department_order', true), 'Lower numbers appear first. The Board section is always first.'],
        '_team_department_background' => ['Section background', get_term_meta($term_id, '_team_department_background', true) ?: 'white', 'Use white or cream.'],
        '_team_department_illustration' => ['Section illustration file', get_term_meta($term_id, '_team_department_illustration', true), 'Optional theme SVG name without .svg, from assets/leadership-v3.'],
    ];
    foreach ($fields as $key => [$label, $value, $hint]) {
        $type = $key === '_team_department_order' ? 'number' : 'text';
        if ($term_id) {
            printf('<tr class="form-field"><th scope="row"><label for="%1$s">%2$s</label></th><td><input id="%1$s" name="%1$s" type="%3$s" value="%4$s"><p class="description">%5$s</p></td></tr>', esc_attr($key), esc_html($label), esc_attr($type), esc_attr($value), esc_html($hint));
        } else {
            printf('<div class="form-field"><label for="%1$s">%2$s</label><input id="%1$s" name="%1$s" type="%3$s" value="%4$s"><p class="description">%5$s</p></div>', esc_attr($key), esc_html($label), esc_attr($type), esc_attr($value), esc_html($hint));
        }
    }
}
add_action('team_department_add_form_fields', static function () { src_leadership_department_fields_markup(); });
add_action('team_department_edit_form_fields', static function ($term) { src_leadership_department_fields_markup($term); });

function src_leadership_save_department_fields($term_id) {
    if (!current_user_can('manage_categories')) return;
    $text_keys = ['_team_department_nickname', '_team_department_nav_label'];
    foreach ($text_keys as $key) {
        if (isset($_POST[$key])) update_term_meta($term_id, $key, sanitize_text_field(wp_unslash($_POST[$key])));
    }
    if (isset($_POST['_team_department_order'])) update_term_meta($term_id, '_team_department_order', absint($_POST['_team_department_order']));
    if (isset($_POST['_team_department_background'])) {
        $background = sanitize_key(wp_unslash($_POST['_team_department_background']));
        update_term_meta($term_id, '_team_department_background', in_array($background, ['white', 'cream'], true) ? $background : 'white');
    }
    if (isset($_POST['_team_department_illustration'])) update_term_meta($term_id, '_team_department_illustration', sanitize_file_name(wp_unslash($_POST['_team_department_illustration'])));
}
add_action('created_team_department', 'src_leadership_save_department_fields');
add_action('edited_team_department', 'src_leadership_save_department_fields');

function src_seed_leadership_v3() {
    if (get_option('src_leadership_v3_seed_version') === '1') return;
    if (!add_option('src_leadership_v3_seed_lock', time(), '', false)) return;
    try {
    $data_path = get_template_directory() . '/inc/data/leadership-v3.json';
    if (!is_readable($data_path)) return;
    $profiles = json_decode(file_get_contents($data_path), true);
    if (!is_array($profiles) || !$profiles) return;

    foreach (src_leadership_department_defaults() as $slug => $department) {
        $term = get_term_by('slug', $slug, 'team_department');
        if (!$term) {
            $created = wp_insert_term($department['name'], 'team_department', ['slug' => $slug, 'description' => $department['tagline']]);
            if (is_wp_error($created)) continue;
            $term = get_term($created['term_id'], 'team_department');
        } else {
            wp_update_term($term->term_id, 'team_department', ['name' => $department['name'], 'description' => $department['tagline']]);
        }
        update_term_meta($term->term_id, '_team_department_nickname', $department['nickname']);
        update_term_meta($term->term_id, '_team_department_nav_label', $department['nickname']);
        update_term_meta($term->term_id, '_team_department_order', $department['order']);
        update_term_meta($term->term_id, '_team_department_background', $department['background']);
        update_term_meta($term->term_id, '_team_department_illustration', $department['illustration']);
    }

    foreach ($profiles as $profile) {
        $slug = sanitize_title($profile['slug'] ?? $profile['name'] ?? '');
        if ($slug === '') continue;
        $existing = get_page_by_path($slug, OBJECT, 'team_member');
        $post_data = [
            'post_title' => sanitize_text_field($profile['name'] ?? ''),
            'post_name' => $slug,
            'post_excerpt' => sanitize_textarea_field($profile['bioShort'] ?? ''),
            'post_content' => wp_kses_post($profile['bio'] ?? ''),
            'post_status' => 'publish',
            'post_type' => 'team_member',
        ];
        if ($existing) $post_data['ID'] = $existing->ID;
        $post_id = wp_insert_post($post_data, true);
        if (is_wp_error($post_id) || !$post_id) continue;
        update_post_meta($post_id, '_team_role', sanitize_text_field($profile['role'] ?? ''));
        update_post_meta($post_id, '_team_quote', sanitize_textarea_field($profile['bioShort'] ?? ''));
        update_post_meta($post_id, '_team_bio_short', sanitize_textarea_field($profile['bioShort'] ?? ''));
        update_post_meta($post_id, '_team_linkedin', esc_url_raw($profile['linkedin'] ?? ''));
        update_post_meta($post_id, '_team_county', sanitize_text_field($profile['county'] ?? ''));
        update_post_meta($post_id, '_team_county_level', sanitize_text_field($profile['county_level'] ?? ''));
        update_post_meta($post_id, '_team_image_url', sanitize_text_field($profile['image'] ?? ''));
        update_post_meta($post_id, '_team_category', in_array('senior-management', $profile['departments'] ?? [], true) ? 'leadership' : 'staff');
        update_post_meta($post_id, '_team_panel_color', in_array('senior-management', $profile['departments'] ?? [], true) ? 'red' : 'charcoal');
        foreach ($profile['department_order'] ?? [] as $department => $order) {
            $meta_key = 'team_order_' . str_replace('-', '_', sanitize_key($department));
            update_post_meta($post_id, '_' . $meta_key, absint($order));
        }
        wp_set_object_terms($post_id, array_map('sanitize_title', $profile['departments'] ?? []), 'team_department', false);
        if (!empty($profile['county'])) wp_set_object_terms($post_id, [sanitize_text_field($profile['county'])], 'team_county', false);
        else wp_set_object_terms($post_id, [], 'team_county', false);
        update_post_meta($post_id, '_src_leadership_v3_imported', '1');
    }
    update_option('src_leadership_v3_seed_version', '1');
    } finally {
        delete_option('src_leadership_v3_seed_lock');
    }
}
add_action('init', 'src_seed_leadership_v3', 40);

function src_leadership_draft_legacy_board_seeds() {
    if (get_option('src_leadership_legacy_board_cleanup') === '1') return;
    foreach (['samuel-wamae', 'rosemary-munyiri', 'evelyn-okwach', 'dr-samuel-muriu'] as $slug) {
        $post = get_page_by_path($slug, OBJECT, 'team_member');
        if (!$post || get_post_meta($post->ID, '_src_leadership_v3_imported', true) === '1') continue;
        if (get_post_meta($post->ID, '_team_category', true) === 'board') {
            wp_update_post(['ID' => $post->ID, 'post_status' => 'draft']);
        }
    }
    update_option('src_leadership_legacy_board_cleanup', '1');
}
add_action('init', 'src_leadership_draft_legacy_board_seeds', 41);

function src_leadership_departments() {
    $terms = get_terms(['taxonomy' => 'team_department', 'hide_empty' => false]);
    if (is_wp_error($terms)) return [];
    usort($terms, static function ($a, $b) {
        $a_order = (int) get_term_meta($a->term_id, '_team_department_order', true);
        $b_order = (int) get_term_meta($b->term_id, '_team_department_order', true);
        return ($a_order <=> $b_order) ?: strcasecmp($a->name, $b->name);
    });
    return array_map(static function ($term) {
        return [
            'id' => $term->slug,
            'term_id' => $term->term_id,
            'formal' => $term->name,
            'nickname' => get_term_meta($term->term_id, '_team_department_nickname', true) ?: $term->name,
            'nav_label' => get_term_meta($term->term_id, '_team_department_nav_label', true) ?: $term->name,
            'tagline' => $term->description,
            'order' => (int) get_term_meta($term->term_id, '_team_department_order', true),
            'background' => get_term_meta($term->term_id, '_team_department_background', true) === 'cream' ? 'cream' : 'white',
            'illustration' => sanitize_file_name(get_term_meta($term->term_id, '_team_department_illustration', true)),
        ];
    }, $terms);
}

function src_leadership_team_members($department_slug) {
    $posts = get_posts([
        'post_type' => 'team_member', 'post_status' => 'publish', 'posts_per_page' => -1,
        'tax_query' => [['taxonomy' => 'team_department', 'field' => 'slug', 'terms' => sanitize_title($department_slug)]],
    ]);
    $meta_key = '_team_order_' . str_replace('-', '_', sanitize_key($department_slug));
    usort($posts, static function ($a, $b) use ($meta_key) {
        $a_order = (int) get_post_meta($a->ID, $meta_key, true);
        $b_order = (int) get_post_meta($b->ID, $meta_key, true);
        return ($a_order <=> $b_order) ?: strcasecmp($a->post_title, $b->post_title);
    });
    return array_map('src_leadership_member_data', $posts);
}

function src_leadership_member_data($post) {
    $post = get_post($post);
    if (!$post) return [];
    $image = get_the_post_thumbnail_url($post, 'large');
    if (!$image) {
        $stored_image = trim((string) get_post_meta($post->ID, '_team_image_url', true));
        if (preg_match('#^https?://#i', $stored_image)) $image = $stored_image;
        elseif ($stored_image !== '') $image = get_theme_file_uri(ltrim($stored_image, '/'));
    }
    $terms = wp_get_object_terms($post->ID, 'team_department', ['fields' => 'slugs']);
    $county_terms = wp_get_object_terms($post->ID, 'team_county', ['fields' => 'names']);
    $county = is_array($county_terms) && $county_terms ? (string) $county_terms[0] : (string) get_post_meta($post->ID, '_team_county', true);
    $bio_markup = preg_replace('#</(?:p|div|li|h[1-6])>|<br\s*/?>#i', "\n\n", (string) $post->post_content);
    $bio = trim(html_entity_decode(wp_strip_all_tags($bio_markup), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $bio_short = trim((string) get_post_meta($post->ID, '_team_bio_short', true));
    if ($bio !== '' && $bio_short === '') $bio_short = wp_trim_words($bio, 40, '…');
    return [
        'id' => $post->ID,
        'name' => get_the_title($post),
        'role' => (string) get_post_meta($post->ID, '_team_role', true),
        'image' => $image ?: '',
        'county' => $county,
        'county_level' => (string) get_post_meta($post->ID, '_team_county_level', true),
        'bio_short' => $bio_short,
        'bio' => $bio,
        'linkedin' => esc_url((string) get_post_meta($post->ID, '_team_linkedin', true)),
        'departments' => is_array($terms) ? $terms : [],
    ];
}

function src_leadership_team_tone($member, $index, $ordered_members, $brainstorm = false) {
    if (in_array('senior-management', $member['departments'], true)) return 'red';
    $earlier_non_senior = 0;
    foreach (array_slice($ordered_members, 0, $index) as $earlier) {
        if (!in_array('senior-management', $earlier['departments'], true)) $earlier_non_senior++;
    }
    return $earlier_non_senior % 2 === 0 ? 'yellow' : 'grey';
}

function src_leadership_render_portrait($member, $tone) {
    $colours = [
        'red' => ['#B3262C', '#C53B41', '#9F2026'],
        'grey' => ['#8A8C8F', '#9C9EA1', '#76787B'],
        'yellow' => ['#F2A33A', '#F6B65C', '#D99025'],
    ][$tone] ?? ['#B3262C', '#C53B41', '#9F2026'];
    ?>
    <svg viewBox="0 0 100 125" preserveAspectRatio="none" class="leadership-person-pattern" aria-hidden="true">
      <rect width="100" height="125" fill="<?php echo esc_attr($colours[0]); ?>" />
      <g opacity="0.72">
        <path d="M0 0h28L14 24Z" fill="<?php echo esc_attr($colours[1]); ?>" /><path d="M28 0h28L42 24Z" fill="<?php echo esc_attr($colours[2]); ?>" />
        <path d="M0 24h28L14 48Z" fill="<?php echo esc_attr($colours[2]); ?>" /><path d="M28 24h28L42 48Z" fill="<?php echo esc_attr($colours[1]); ?>" />
        <path d="M56 0h22L67 19Z" fill="<?php echo esc_attr($colours[1]); ?>" /><path d="M78 0h22L89 19Z" fill="<?php echo esc_attr($colours[2]); ?>" />
        <path d="M56 19h22L67 38Z" fill="<?php echo esc_attr($colours[2]); ?>" /><path d="M78 19h22L89 38Z" fill="<?php echo esc_attr($colours[1]); ?>" />
        <path d="M67 38h16L75 52Z" fill="<?php echo esc_attr($colours[1]); ?>" /><path d="M83 38h17L91.5 52Z" fill="<?php echo esc_attr($colours[2]); ?>" />
        <path d="M76 52h12L82 63Z" fill="<?php echo esc_attr($colours[2]); ?>" /><path d="M88 52h12L94 63Z" fill="<?php echo esc_attr($colours[1]); ?>" />
      </g>
    </svg>
    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="leadership-portrait-outline" aria-hidden="true"><path d="M18 58V16H82V46Q82 58 70 58Z" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="1.5" vector-effect="non-scaling-stroke" /></svg>
    <?php if (!empty($member['image'])) : ?><img src="<?php echo esc_url($member['image']); ?>" alt="<?php echo esc_attr($member['name']); ?>" class="leadership-portrait-photo" loading="lazy"><?php else : ?><div class="leadership-person-initials" aria-hidden="true"><?php echo esc_html(implode('', array_map(static fn($part) => substr($part, 0, 1), array_slice(preg_split('/\s+/', $member['name']), 0, 2)))); ?></div><?php endif; ?>
    <div class="leadership-portrait-bottom leadership-portrait-bottom--<?php echo esc_attr($tone); ?>"></div>
    <?php
}

function src_leadership_render_team_card($member, $tone, $directory_sized = true, $lead = false, $badge = '', $brainstorm = false) {
    $has_bio = $member['bio'] !== '';
    $classes = ['leadership-person-card', 'leadership-person-card--' . $tone];
    if ($has_bio) $classes[] = 'leadership-person-card--has-bio';
    if ($directory_sized) $classes[] = 'team-directory-card';
    if ($brainstorm) $classes[] = 'brainstorm-team-card';
    $label = $badge ?: ($lead ? 'Leads the team' : $member['county']);
    ?>
    <article class="<?php echo esc_attr(implode(' ', $classes)); ?>"<?php if ($has_bio) : ?> tabindex="0" aria-expanded="false" data-team-card<?php else : ?> data-team-card<?php endif; ?> data-name="<?php echo esc_attr($member['name']); ?>" data-role="<?php echo esc_attr($member['role']); ?>" data-image="<?php echo esc_url($member['image']); ?>" data-tone="<?php echo esc_attr($tone); ?>" data-bio-short="<?php echo esc_attr($member['bio_short']); ?>" data-bio="<?php echo esc_attr($member['bio']); ?>" data-linkedin="<?php echo esc_url($member['linkedin']); ?>" data-county="<?php echo esc_attr($member['county']); ?>">
      <?php src_leadership_render_portrait($member, $tone); ?>
      <div class="leadership-card-copy">
        <h3><?php echo esc_html($member['name']); ?></h3><p><?php echo esc_html($member['role']); ?></p>
        <?php if ($label !== '') : ?><span class="leadership-card-badge"><?php echo esc_html($label); ?></span><?php endif; ?>
      </div>
      <?php if ($has_bio) : ?>
        <div class="leadership-card-reveal leadership-card-reveal--<?php echo esc_attr($tone); ?>">
          <h3><?php echo esc_html($member['name']); ?></h3><p class="leadership-card-reveal-role"><?php echo esc_html($member['role']); ?></p>
          <p class="leadership-card-reveal-bio"><?php echo esc_html($member['bio_short']); ?></p>
          <div class="leadership-card-reveal-actions">
            <?php if ($member['bio'] !== '') : ?><button type="button" data-open-team-bio>Read full bio</button><?php endif; ?>
            <?php if ($member['linkedin'] !== '') : ?><a href="<?php echo esc_url($member['linkedin']); ?>" target="_blank" rel="noreferrer" aria-label="<?php echo esc_attr($member['name']); ?> on LinkedIn" data-team-linkedin><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286ZM5.337 7.433a2.062 2.062 0 1 1 0-4.123 2.062 2.062 0 0 1 0 4.123ZM7.119 20.452H3.555V9h3.564v11.452Z" /></svg></a><?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </article>
    <?php
}

function src_leadership_render_section_heading($formal, $nickname, $tagline, $illustration = '') {
    ?>
    <div class="leadership-section-heading">
      <div class="leadership-section-heading-copy"><p class="leadership-section-formal"><?php echo esc_html($formal); ?></p><h2><?php echo esc_html($nickname); ?></h2><p class="leadership-section-tagline"><?php echo esc_html($tagline); ?></p></div>
      <?php if ($illustration !== '') : $path = get_theme_file_path('assets/leadership-v3/' . sanitize_file_name($illustration) . '.svg'); if (is_readable($path)) : ?><div class="leadership-section-illustration" aria-hidden="true"><?php echo file_get_contents($path); // Theme-owned exported SVG asset. ?></div><?php endif; endif; ?>
    </div>
    <?php
}

