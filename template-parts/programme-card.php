<?php
$programme = $args['programme'] ?? get_post();
if (!$programme instanceof WP_Post) return;

$id = $programme->ID;
$status = get_post_meta($id, '_programme_status', true) ?: 'upcoming';
$enrolment = get_post_meta($id, '_programme_enrolment', true);
$title = get_post_meta($id, '_programme_hero_title', true) ?: get_the_title($id);
$summary = get_post_meta($id, '_programme_audience', true) ?: get_the_excerpt($id);
$image_id = get_post_thumbnail_id($id);
$image_key = get_post_meta($id, '_programme_image_id', true);
$image = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
if (!$image && $image_key) $image = 'https://images.unsplash.com/' . rawurlencode($image_key) . '?w=900&h=600&fit=crop&auto=format&q=82';
if (!$image) $image = 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=900&h=600&fit=crop&auto=format&q=82';

$themes = wp_get_post_terms($id, 'programme_theme', ['fields' => 'names']);
$theme = !is_wp_error($themes) && $themes ? $themes[0] : '';
$theme_slugs = wp_get_post_terms($id, 'programme_theme', ['fields' => 'slugs']);
$theme_slug = !is_wp_error($theme_slugs) && $theme_slugs ? $theme_slugs[0] : '';
$duration = get_post_meta($id, '_programme_duration', true);
$reach = get_post_meta($id, '_programme_reach', true) ?: 'Kenya';
$tags = get_post_meta($id, '_programme_tags', true);
$tags = is_array($tags) ? array_slice($tags, 0, 3) : [];
$funders = get_post_meta($id, '_programme_funders', true);
$funders = is_array($funders) ? array_values(array_filter($funders)) : [];
$partners = get_post_meta($id, '_programme_partners', true);
$partners = is_array($partners) ? array_values(array_filter($partners)) : [];
$logo_keys = get_post_meta($id, '_programme_logos', true);
$logo_keys = is_array($logo_keys) ? array_values(array_filter($logo_keys)) : [];
if (!$logo_keys && $funders) $logo_keys = [$funders[0]];
$logo_assets = [
    'src' => 'src-logo.png',
    'herventure' => 'her-venture-transparent.svg',
    'boma-project' => 'partner-logos/boma-project.png',
    'cherie-blair-foundation' => 'partner-logos/cherie-blair-foundation.png',
    'dhl' => 'partner-logos/dhl.png',
    'usaid' => 'partner-logos/usaid-programme.png',
    'village-capital' => 'partner-logos/village-capital.png',
    'giz' => 'partner-logos/giz.png',
    'goal' => 'partner-logos/goal.png',
    'united-nations' => 'partner-logos/united-nations.png',
    'world-bank' => 'partner-logos/world-bank.png',
    'wwf' => 'partner-logos/wwf.png',
    'kcdf' => 'partner-logos/kcdf.png',
];
$logo_labels = [
    'ande' => 'ANDE', 'boma-project' => 'BOMA Project', 'afrishela' => 'Afrishela',
    'graca-machel-trust' => 'Graça Machel Trust', 'sheconnects' => 'SheConnects',
    'care' => 'CARE', 'mastercard-foundation' => 'Mastercard Foundation',
    'herventure' => 'HerVenture', 'cherie-blair-foundation' => 'Cherie Blair Foundation',
    'dhl' => 'DHL', 'src' => 'SRC', 'kcb-foundation' => 'KCB Foundation',
    'usaid' => 'USAID', 'village-capital' => 'Village Capital', 'giz' => 'GIZ',
    'goal' => 'GOAL', 'united-nations' => 'United Nations', 'world-bank' => 'World Bank',
    'wwf' => 'WWF', 'kcdf' => 'KCDF',
];
$status_label = $status === 'past' ? 'Past' : ($status === 'upcoming' ? 'Upcoming' : ($enrolment === 'open' ? 'Enrolling now' : 'Running'));
$eyebrow = $status === 'past' ? ($funders[0] ?? $partners[0] ?? $theme) : $theme;
$note = get_post_meta($id, '_programme_note', true);
$dates = get_post_meta($id, '_programme_dates', true);
if (!$dates || stripos($dates, 'tbc') !== false || stripos($dates, 'coming soon') !== false || stripos($dates, 'announced') !== false) $dates = 'Dates TBC';
$purchase = src_v3_programme_purchase_state($id);
$contact_url = add_query_arg('programme', $programme->post_name, src_page_url('contact'));
$register_url = $purchase['available']
    ? add_query_arg(['add-to-cart' => $purchase['product_id'], 'quantity' => 1], wc_get_checkout_url())
    : $contact_url;
$span_class = $args['span_class'] ?? '';
?>
<article <?php post_class('src-v3-programme-card src-v3-programme-card-' . sanitize_html_class($status) . $span_class, $programme); ?> data-programme-card data-programme-status="<?php echo esc_attr($status); ?>" data-programme-theme="<?php echo esc_attr($theme_slug); ?>" data-programme-signature="<?php echo get_post_meta($id, '_programme_type', true) === 'signature' ? '1' : '0'; ?>">
    <a class="src-v3-programme-image" href="<?php the_permalink($id); ?>" aria-label="View <?php echo esc_attr($title); ?>">
        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async">
        <span class="src-v3-status src-v3-status-<?php echo esc_attr($status); ?>"><?php echo esc_html($status_label); ?></span>
        <?php if ($status === 'past' && $tags) : ?><span class="src-v3-image-tags"><?php foreach (array_slice($tags, 0, 2) as $tag) : ?><span><?php echo esc_html($tag); ?></span><?php endforeach; ?></span><?php endif; ?>
    </a>
    <div class="src-v3-programme-card-body">
        <?php if ($logo_keys) : ?>
            <div class="src-v3-programme-logos">
                <?php foreach ($logo_keys as $logo_key) :
                    $logo_key = (string) $logo_key;
                    $logo_slug = sanitize_title($logo_key);
                    $logo_label = $logo_labels[$logo_slug] ?? $logo_key;
                    $logo_asset = $logo_assets[$logo_slug] ?? '';
                    $logo_url = $logo_asset ? get_template_directory_uri() . '/assets/' . $logo_asset : '';
                    ?>
                    <span class="src-v3-programme-logo" title="<?php echo esc_attr($logo_label); ?>">
                        <?php if ($logo_url) : ?><img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_label); ?>" loading="lazy" decoding="async">
                        <?php else : ?><span><?php echo esc_html($logo_label); ?></span><?php endif; ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <p class="src-v3-programme-theme"><?php echo esc_html($eyebrow); ?></p>
        <h3><a href="<?php the_permalink($id); ?>"><?php echo esc_html($title); ?></a></h3>
        <p class="src-v3-programme-summary"><?php echo esc_html(wp_trim_words($summary, 24)); ?></p>
        <?php if ($status === 'past') : ?>
            <p class="src-v3-programme-past-fact"><span aria-hidden="true">●</span><span><?php echo esc_html($reach . ($duration ? ' · ' . $duration : '')); ?></span></p>
            <?php if ($note) : ?><p class="src-v3-programme-note"><?php echo esc_html($note); ?></p><?php endif; ?>
        <?php else : ?>
            <dl class="src-v3-programme-facts">
                <?php if ($reach) : ?><div><dt>Reach</dt><dd><span aria-hidden="true">●</span> <?php echo esc_html($reach); ?></dd></div><?php endif; ?>
                <?php if ($duration) : ?><div><dt>Duration</dt><dd>Duration: <?php echo esc_html($duration); ?></dd></div><?php endif; ?>
                <?php if ($status === 'upcoming') : ?><div><dt>Dates</dt><dd><?php echo esc_html($dates); ?></dd></div><?php endif; ?>
            </dl>
            <?php if ($tags) : ?><div class="src-v3-programme-tags"><?php foreach ($tags as $tag) : ?><span><?php echo esc_html($tag); ?></span><?php endforeach; ?></div><?php endif; ?>
        <?php endif; ?>
        <div class="src-v3-programme-actions">
            <?php if ($status === 'past') : ?>
                <a class="src-v3-link" href="<?php the_permalink($id); ?>">View programme <span aria-hidden="true">→</span></a>
            <?php else : ?>
                <?php if ($status === 'upcoming' || get_post_meta($id, '_programme_detail_available', true) !== '0' && $enrolment !== 'partner') : ?>
                    <a class="src-v3-button src-v3-button-outline" href="<?php the_permalink($id); ?>">View programme</a>
                <?php endif; ?>
                <?php if ($status === 'upcoming') : ?>
                    <a class="src-v3-button" href="<?php echo esc_url($contact_url); ?>">Get notified</a>
                <?php elseif ($enrolment === 'open') : ?>
                    <button class="src-v3-button" type="button" data-src-open-registration="<?php echo esc_attr($programme->post_name); ?>" aria-controls="src-registration-dialog">Register</button>
                <?php elseif ($enrolment === 'partner') : ?>
                    <a class="src-v3-button" href="<?php the_permalink($id); ?>">View programme</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</article>
