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
$duration = get_post_meta($id, '_programme_duration', true);
$reach = get_post_meta($id, '_programme_reach', true) ?: 'Kenya';
$tags = get_post_meta($id, '_programme_tags', true);
$tags = is_array($tags) ? array_slice($tags, 0, 3) : [];
$purchase = src_v3_programme_purchase_state($id);
$contact_url = add_query_arg('programme', $programme->post_name, src_page_url('contact'));
$register_url = $purchase['available']
    ? add_query_arg(['add-to-cart' => $purchase['product_id'], 'quantity' => 1], wc_get_checkout_url())
    : $contact_url;
$status_label = $status === 'past' ? 'Past' : ($status === 'upcoming' ? 'Upcoming' : ($enrolment === 'open' ? 'Enrolling now' : 'Running'));
?>
<article <?php post_class('src-v3-programme-card src-v3-programme-card-' . sanitize_html_class($status), $programme); ?> data-reveal>
    <a class="src-v3-programme-image" href="<?php the_permalink($id); ?>" aria-label="View <?php echo esc_attr($title); ?>">
        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" decoding="async">
        <span class="src-v3-status src-v3-status-<?php echo esc_attr($status); ?>"><?php echo esc_html($status_label); ?></span>
        <?php if ($status === 'past' && $tags) : ?><span class="src-v3-image-tags"><?php foreach ($tags as $tag) : ?><span><?php echo esc_html($tag); ?></span><?php endforeach; ?></span><?php endif; ?>
    </a>
    <div class="src-v3-programme-card-body">
        <?php if ($theme) : ?><p class="src-v3-programme-theme"><?php echo esc_html($theme); ?></p><?php endif; ?>
        <h3><a href="<?php the_permalink($id); ?>"><?php echo esc_html($title); ?></a></h3>
        <p class="src-v3-programme-summary"><?php echo esc_html(wp_trim_words($summary, 24)); ?></p>
        <dl class="src-v3-programme-facts">
            <?php if ($reach) : ?><div><dt>Reach</dt><dd><?php echo esc_html($reach); ?></dd></div><?php endif; ?>
            <?php if ($duration) : ?><div><dt>Duration</dt><dd><?php echo esc_html($duration); ?></dd></div><?php endif; ?>
            <?php if ($status === 'upcoming') : ?><div><dt>Dates</dt><dd><?php echo esc_html(get_post_meta($id, '_programme_dates', true) ?: 'Dates to be announced'); ?></dd></div><?php endif; ?>
        </dl>
        <?php if ($tags && $status !== 'past') : ?><div class="src-v3-programme-tags"><?php foreach ($tags as $tag) : ?><span><?php echo esc_html($tag); ?></span><?php endforeach; ?></div><?php endif; ?>
        <div class="src-v3-programme-actions">
            <?php if ($status === 'past') : ?>
                <a class="src-v3-link" href="<?php the_permalink($id); ?>">View programme <span aria-hidden="true">→</span></a>
            <?php else : ?>
                <a class="src-v3-button src-v3-button-outline" href="<?php the_permalink($id); ?>">View programme</a>
                <?php if ($status === 'upcoming') : ?>
                    <a class="src-v3-button" href="<?php echo esc_url($contact_url); ?>">Get notified</a>
                <?php elseif ($enrolment === 'open') : ?>
                    <a class="src-v3-button" href="<?php echo esc_url($register_url); ?>"><?php echo $purchase['available'] ? 'Enrol & pay' : 'Register interest'; ?></a>
                <?php else : ?>
                    <a class="src-v3-button" href="<?php echo esc_url($contact_url); ?>">Contact us</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</article>
