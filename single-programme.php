<?php
if (get_post_meta(get_the_ID(), '_src_v3_imported', true) !== '1') {
    include get_template_directory() . '/single.php';
    return;
}
get_header();
the_post();
$id = get_the_ID();
$title = get_post_meta($id, '_programme_hero_title', true) ?: get_the_title();
$status = get_post_meta($id, '_programme_status', true) ?: 'current';
$image_id = get_post_thumbnail_id($id);
$image_key = get_post_meta($id, '_programme_image_id', true);
$image = $image_id ? wp_get_attachment_image_url($image_id, 'full') : ($image_key ? 'https://images.unsplash.com/' . rawurlencode($image_key) . '?w=2000&h=1100&fit=crop&auto=format&q=88' : '');
$purchase = src_v3_programme_purchase_state($id);
$contact_url = add_query_arg('programme', get_post_field('post_name', $id), src_page_url('contact'));
$register_url = $purchase['available'] ? add_query_arg(['add-to-cart' => $purchase['product_id'], 'quantity' => 1], wc_get_checkout_url()) : $contact_url;
$enrolment = get_post_meta($id, '_programme_enrolment', true);
$cta_url = $register_url;
$cta_label = $purchase['available'] ? 'Enrol & pay' : 'Register interest';
if ($status === 'upcoming') { $cta_url = $contact_url; $cta_label = 'Get notified'; }
elseif ($status === 'past') { $cta_url = add_query_arg('status', 'current', get_post_type_archive_link('programme')); $cta_label = 'Explore current programmes'; }
elseif ($enrolment === 'partner') { $cta_url = src_page_url('get-involved'); $cta_label = 'Partner with us'; }
elseif ($enrolment !== 'open') { $cta_url = $contact_url; $cta_label = 'Enquire about this programme'; }
$county_terms = wp_get_post_terms($id, 'programme_county');
$county_slugs = !is_wp_error($county_terms) ? wp_list_pluck($county_terms, 'slug') : [];
$county_names = !is_wp_error($county_terms) ? wp_list_pluck($county_terms, 'name') : [];
$county_svg = file_get_contents(get_template_directory() . '/assets/kenya-counties.svg');
if ($county_svg) {
    $county_svg = preg_replace_callback('/<path\b[^>]*>/', static function ($match) use ($county_slugs) {
        if (!preg_match('/id="county-([^"]+)"/', $match[0], $path_id)) return $match[0];
        $classes = 'county';
        if (!$county_slugs) $classes .= ' is-all-selected';
        elseif (in_array($path_id[1], $county_slugs, true)) $classes .= ' is-selected';
        return preg_replace('/class="county"/', 'class="' . $classes . '"', $match[0], 1);
    }, $county_svg);
    $county_svg = preg_replace('/<svg\b/', '<svg class="src-v3-county-svg" focusable="false"', $county_svg, 1);
}
$meta_rows = [
    'Duration' => get_post_meta($id, '_programme_duration', true),
    'Delivery' => get_post_meta($id, '_programme_delivery', true),
    'Dates' => get_post_meta($id, '_programme_dates', true),
    'Reach' => get_post_meta($id, '_programme_reach', true),
];
$list_fields = [
    'Who this is for' => get_post_meta($id, '_programme_audience', true),
    'Programme outcomes' => get_post_meta($id, '_programme_outcomes', true),
];
$journey = get_post_meta($id, '_programme_journey', true);
$journey = is_array($journey) ? $journey : [];
$funders = get_post_meta($id, '_programme_funders', true);
$partners = get_post_meta($id, '_programme_partners', true);
$tags = get_post_meta($id, '_programme_tags', true);
$themes = wp_get_post_terms($id, 'programme_theme', ['fields' => 'names']);
?>
<main class="src-v3-programme-detail">
    <section class="src-v3-detail-hero" <?php if ($image) : ?>style="--programme-cover:url('<?php echo esc_url($image); ?>')"<?php endif; ?>>
        <div class="container">
            <a class="src-v3-detail-back" href="<?php echo esc_url(get_post_type_archive_link('programme')); ?>">← All programmes</a>
            <p class="eyebrow eyebrow-light"><?php echo esc_html($themes && !is_wp_error($themes) ? $themes[0] : 'SRC Programmes'); ?></p>
            <span class="src-v3-status src-v3-status-<?php echo esc_attr($status); ?>"><?php echo esc_html($status === 'current' ? 'Current programme' : ucfirst($status)); ?></span>
            <h1 class="display display-lg"><?php echo esc_html($title); ?></h1>
            <?php if ($subtitle = get_post_meta($id, '_programme_hero_subtitle', true)) : ?><p class="lede lede-light"><?php echo esc_html($subtitle); ?></p><?php endif; ?>
            <div class="hero-actions">
                <?php if ($status === 'current' && $enrolment === 'open') : ?><button class="button" type="button" data-src-open-registration="<?php echo esc_attr($post->post_name); ?>" aria-controls="src-registration-dialog"><?php echo esc_html($purchase['available'] ? 'Register' : 'Register interest'); ?></button><?php else : ?><a class="button" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a><?php endif; ?>
            </div>
        </div>
    </section>
    <section class="src-v3-detail-facts">
        <div class="container src-v3-detail-facts-grid">
            <?php foreach ($meta_rows as $label => $value) : if (!$value) continue; ?><div><span><?php echo esc_html($label); ?></span><strong><?php echo esc_html($value); ?></strong></div><?php endforeach; ?>
            <?php if ($purchase['available']) : ?><div><span>Programme fee</span><strong><?php echo wp_kses_post(wc_price($purchase['price'], ['currency' => $purchase['currency']])); ?></strong></div>
            <?php elseif ($fee = get_post_meta($id, '_programme_fee', true)) : ?><div><span>Programme fee</span><strong><?php echo esc_html($fee); ?></strong></div><?php endif; ?>
        </div>
    </section>
    <section class="section cream">
        <div class="container src-v3-detail-columns">
            <div class="src-v3-detail-main">
                <?php foreach ($list_fields as $heading => $copy) : if (!$copy) continue; ?><section class="src-v3-detail-block"><p class="eyebrow"><?php echo esc_html($heading); ?></p><div class="src-v3-detail-copy"><?php echo wpautop(esc_html($copy)); ?></div></section><?php endforeach; ?>
                <?php if ($journey) : ?><section class="src-v3-detail-block"><p class="eyebrow">Programme journey</p><ol class="src-v3-journey"><?php foreach ($journey as $step => $label) : ?><li><span><?php echo esc_html(str_pad((string) ($step + 1), 2, '0', STR_PAD_LEFT)); ?></span><strong><?php echo esc_html($label); ?></strong></li><?php endforeach; ?></ol></section><?php endif; ?>
                <?php $body_content = trim((string) get_post_field('post_content', $id)); $outcome_content = trim((string) get_post_meta($id, '_programme_outcomes', true)); if ($body_content !== '' && $body_content !== $outcome_content) : ?><section class="src-v3-detail-block"><p class="eyebrow">About the programme</p><div class="src-v3-detail-copy"><?php the_content(); ?></div></section><?php endif; ?>
            </div>
            <aside class="src-v3-detail-aside">
                <div class="src-v3-detail-block src-v3-county-map-card"><p class="eyebrow"><?php echo $county_names ? esc_html(count($county_names) . ' counties') : 'Where we work'; ?></p><div class="src-v3-county-map" role="img" aria-label="Map of Kenya highlighting programme counties"><?php echo $county_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php if ($county_names) : ?><div class="src-v3-county-tags"><?php foreach ($county_names as $county_name) : ?><span><?php echo esc_html($county_name); ?></span><?php endforeach; ?></div><?php else : ?><p class="src-v3-county-label">Open to participants across Kenya</p><?php endif; ?></div>
                <?php if ($tags && is_array($tags)) : ?><div class="src-v3-detail-block"><p class="eyebrow">Focus areas</p><div class="src-v3-programme-tags"><?php foreach ($tags as $tag) : ?><span><?php echo esc_html($tag); ?></span><?php endforeach; ?></div></div><?php endif; ?>
                <?php foreach (['Funders' => $funders, 'Delivery partners' => $partners] as $heading => $items) : if (!$items || !is_array($items)) continue; ?><div class="src-v3-detail-block"><p class="eyebrow"><?php echo esc_html($heading); ?></p><ul class="src-v3-plain-list"><?php foreach ($items as $item) : ?><li><?php echo esc_html($item); ?></li><?php endforeach; ?></ul></div><?php endforeach; ?>
                <?php if (get_post_meta($id, '_programme_type', true) === 'signature') : ?><div class="src-v3-detail-block"><p class="eyebrow">Programme type</p><strong>An SRC Signature Programme</strong></div><?php endif; ?>
                <div class="src-v3-detail-cta"><p class="eyebrow eyebrow-light">Take the next step</p><h2 class="display display-md"><?php echo esc_html(get_post_meta($id, '_programme_next_cohort_label', true) ?: 'Interested in this programme?'); ?></h2><?php if ($status === 'current' && $enrolment === 'open') : ?><button class="button" type="button" data-src-open-registration="<?php echo esc_attr($post->post_name); ?>" aria-controls="src-registration-dialog">Register</button><?php else : ?><a class="button" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a><?php endif; ?><small><?php echo $purchase['available'] ? 'Secure checkout powered by WooCommerce.' : 'Programme fee and enrolment details will be confirmed by our team.'; ?></small></div>
            </aside>
        </div>
    </section>
    <?php
    $theme_terms = wp_get_post_terms($id, 'programme_theme');
    $theme_ids = !is_wp_error($theme_terms) ? wp_list_pluck($theme_terms, 'term_id') : [];
    $related = $theme_ids ? get_posts(['post_type' => 'programme', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => [$id], 'orderby' => 'menu_order', 'order' => 'ASC', 'meta_key' => '_src_v3_imported', 'meta_value' => '1', 'tax_query' => [['taxonomy' => 'programme_theme', 'field' => 'term_id', 'terms' => $theme_ids]]]) : [];
    ?>
    <?php if ($related) : ?><section class="src-programme-section"><div class="container"><header class="src-programme-section-heading"><p class="eyebrow">Related Programmes</p><h2 class="display display-md">More in <?php echo esc_html($themes && !is_wp_error($themes) ? $themes[0] : 'this area'); ?></h2></header><div class="src-v3-programme-grid"><?php foreach ($related as $related_programme) get_template_part('template-parts/programme-card', null, ['programme' => $related_programme]); ?></div></div></section><?php endif; ?>
</main>
<?php get_footer(); ?>
