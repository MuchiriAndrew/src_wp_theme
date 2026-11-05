<?php
if (!defined('ABSPATH')) exit;
get_header();
$companies = get_posts([
    'post_type' => 'portfolio_company',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_key' => '_portfolio_order',
    'orderby' => 'meta_value_num',
    'order' => 'ASC',
    'meta_query' => [['key' => '_portfolio_featured', 'value' => '1']],
]);
?>
<main>
  <section class="page-hero page-hero--inner portfolio-hero">
    <div class="portfolio-hero-backdrop" aria-hidden="true">
      <img class="portfolio-hero-image" src="https://images.unsplash.com/photo-1637149014802-d917e6255c2d?auto=format&amp;fit=crop&amp;w=2000&amp;q=85" alt="" fetchpriority="high">
      <div class="portfolio-hero-gradient"></div>
    </div>
    <div class="portfolio-hero-ring-layer" aria-hidden="true">
      <svg class="portfolio-brand-ring" viewBox="0 0 900 900" focusable="false"><circle cx="450" cy="450" r="405" fill="none" stroke="currentColor" stroke-width="90" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg>
    </div>
    <div class="container portfolio-hero-content">
      <div class="portfolio-hero-copy">
        <nav class="portfolio-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span aria-hidden="true">/</span><span aria-current="page">Portfolio</span></nav>
        <p class="portfolio-hero-eyebrow">Portfolio</p>
        <h1 class="portfolio-hero-headline">Our Portfolio</h1>
        <p class="portfolio-hero-intro">SMEs we have partnered with to grow and scale.</p>
      </div>
    </div>
  </section>
  <section class="portfolio-companies" aria-label="SRC portfolio companies">
    <div class="portfolio-companies-grid">
      <?php foreach ($companies as $company) : $initials = src_field('portfolio_abbreviation', $company->ID, strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $company->post_title), 0, 2))); ?>
        <article class="portfolio-company-card">
          <div class="portfolio-company-mark" aria-hidden="true"><?php echo esc_html($initials); ?></div>
          <h2><?php echo esc_html(get_the_title($company)); ?></h2>
          <p><?php echo esc_html(src_field('portfolio_sector', $company->ID, 'Enterprise')); ?></p>
          <?php $url = src_field('portfolio_url', $company->ID, ''); if ($url) : ?><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer">Visit company <span aria-hidden="true">↗</span></a><?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php get_footer(); ?>
