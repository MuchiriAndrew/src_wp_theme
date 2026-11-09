<?php
$programmes = get_posts([
    'post_type' => 'programme',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_key' => '_programme_home_order',
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
    'meta_query' => [
        'relation' => 'AND',
        ['key' => '_src_v3_imported', 'value' => '1'],
        ['key' => '_programme_status', 'value' => 'current'],
        ['key' => '_programme_enrolment', 'value' => 'open'],
    ],
]);
$programme_archive = get_post_type_archive_link('programme') ?: home_url('/programmes/');
?>
<section class="src-commerce-catalog" aria-labelledby="src-commerce-catalog-title">
    <header class="src-commerce-catalog-heading">
        <p class="eyebrow"><?php echo esc_html(src_option('shop_catalog_eyebrow', 'Open enrolment')); ?></p>
        <h2 id="src-commerce-catalog-title"><?php echo esc_html(src_option('shop_catalog_heading', 'Choose a programme')); ?></h2>
        <p><?php echo esc_html(src_option('shop_catalog_intro', 'Explore current programmes and start your registration. Programme fees and payment options are confirmed in the registration flow.')); ?></p>
    </header>

    <?php if ($programmes) : ?>
        <div class="src-v3-programme-grid">
            <?php foreach ($programmes as $programme) get_template_part('template-parts/programme-card', null, ['programme' => $programme]); ?>
        </div>
    <?php else : ?>
        <div class="src-commerce-empty">
            <h3><?php echo esc_html(src_option('shop_empty_heading', 'There are no programmes open for enrolment right now.')); ?></h3>
            <p><?php echo esc_html(src_option('shop_empty_intro', 'Browse all programmes to see what is running, coming soon or recently completed.')); ?></p>
            <a class="src-v3-button" href="<?php echo esc_url($programme_archive); ?>"><?php echo esc_html(src_option('shop_empty_action', 'Browse all programmes')); ?> <span aria-hidden="true">→</span></a>
        </div>
    <?php endif; ?>
</section>
