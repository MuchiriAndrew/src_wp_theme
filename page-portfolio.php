<?php
if (!defined('ABSPATH')) exit;
get_header();
$page_id = get_queried_object_id();
$hero_image = (string) src_page_field('page_hero_image_url', $page_id, 'https://images.unsplash.com/photo-1637149014802-d917e6255c2d?auto=format&fit=crop&w=2000&q=85');
$companies = get_posts([
    'post_type' => 'programme',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_key' => '_programme_home_order',
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
    'meta_query' => [['key' => '_src_v3_imported', 'value' => '1']],
]);
?>
<main>
  <section class="page-hero page-hero--inner portfolio-hero">
    <div class="portfolio-hero-backdrop" aria-hidden="true">
      <img class="portfolio-hero-image" src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high">
      <div class="portfolio-hero-gradient"></div>
    </div>
    <div class="portfolio-hero-ring-layer" aria-hidden="true">
      <svg class="portfolio-brand-ring" viewBox="0 0 900 900" focusable="false"><circle cx="450" cy="450" r="405" fill="none" stroke="currentColor" stroke-width="90" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg>
    </div>
    <div class="container portfolio-hero-content">
      <div class="portfolio-hero-copy">
        <nav class="portfolio-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span aria-hidden="true">/</span><span aria-current="page">Portfolio</span></nav>
        <p class="portfolio-hero-eyebrow"><?php echo esc_html(src_page_field('page_hero_eyebrow', $page_id, 'Our Work')); ?></p>
        <h1 class="portfolio-hero-headline"><?php echo esc_html(src_page_field('page_hero_title', $page_id, 'Our Portfolio')); ?></h1>
        <p class="portfolio-hero-intro"><?php echo esc_html(src_page_field('page_hero_intro', $page_id, 'Programmes designed to help enterprises and cooperatives grow across Kenya and East Africa.')); ?></p>
      </div>
    </div>
  </section>
  <section class="portfolio-companies" aria-label="SRC programmes and case studies">
    <div class="portfolio-companies-grid">
      <?php foreach ($companies as $company) : $initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', get_the_title($company)), 0, 2)); ?>
        <article class="portfolio-company-card">
          <div class="portfolio-company-mark" aria-hidden="true"><?php echo esc_html($initials); ?></div>
          <h2><?php echo esc_html(src_field('programme_hero_title', $company->ID, get_the_title($company))); ?></h2>
          <p><?php echo esc_html(src_field('programme_audience', $company->ID, wp_trim_words(wp_strip_all_tags($company->post_excerpt ?: $company->post_content), 22))); ?></p>
          <a href="<?php echo esc_url(get_permalink($company)); ?>"><?php echo esc_html(src_page_field('page_portfolio_card_link', $page_id, 'Explore programme')); ?> <span aria-hidden="true">↗</span></a>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php get_footer(); ?>
