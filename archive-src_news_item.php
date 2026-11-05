<?php
if (!defined('ABSPATH')) exit;
get_header();
$items = src_ordered_posts('src_news_item', 'news_order');
?>
<main>
  <section class="page-hero page-hero--inner portfolio-hero"><div class="portfolio-hero-backdrop" aria-hidden="true"></div><div class="cx page-hero-content portfolio-hero-content"><div class="portfolio-hero-copy"><p class="portfolio-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span>News</p><p class="page-hero-eyebrow">From SRC</p><h1 class="page-hero-headline">Latest News &amp; Updates</h1><p class="page-hero-intro">News, insights and field notes from SRC.</p></div></div></section>
  <section class="portfolio-companies"><div class="home-news-grid">
    <?php foreach ($items as $item) : $image = src_field('news_image_url', $item->ID, ''); ?>
      <article class="home-news-card"><a class="home-news-image" href="<?php echo esc_url(get_permalink($item)); ?>"><img src="<?php echo esc_url($image); ?>" alt="" loading="lazy"></a><div><p class="home-news-meta"><?php echo esc_html(src_field('news_date_label', $item->ID, '')); ?> <span>·</span> <?php echo esc_html(src_field('news_category', $item->ID, '')); ?></p><h2><a href="<?php echo esc_url(get_permalink($item)); ?>"><?php echo esc_html(get_the_title($item)); ?></a></h2><p class="home-news-excerpt"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($item->post_excerpt ?: $item->post_content), 26)); ?></p><a class="home-news-link" href="<?php echo esc_url(get_permalink($item)); ?>">Read more <span aria-hidden="true">→</span></a></div></article>
    <?php endforeach; ?>
  </div></section>
</main>
<?php get_footer(); ?>
