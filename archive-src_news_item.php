<?php
if (!defined('ABSPATH')) exit;
get_header();
$items = src_ordered_posts('src_news_item', 'news_order');
$featured = $items ? $items[0] : null;
$featured_image = $featured ? src_field('news_image_url', $featured->ID, '') : '';
?>
<main>
  <section class="news-archive-hero" <?php if ($featured_image) : ?>style="--news-archive-image:url('<?php echo esc_url($featured_image); ?>')"<?php endif; ?>>
    <div class="news-archive-shade"></div><div class="news-archive-inner">
      <p class="portfolio-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span>News</p>
      <p class="page-hero-eyebrow">Ideas from the field</p><h1>Latest News<br><em>&amp; Updates</em></h1>
      <p class="news-archive-intro">Stories, practical insights and programme updates from SRC’s work with enterprises and cooperatives across the region.</p>
      <a class="news-archive-link" href="#news-stories">Explore stories <span aria-hidden="true">↓</span></a>
    </div><p class="news-archive-index" aria-hidden="true">SRC / JOURNAL</p>
  </section>
  <section class="portfolio-companies news-archive-list" id="news-stories"><div class="home-news-grid">
    <?php foreach ($items as $item) : $image = src_field('news_image_url', $item->ID, ''); ?>
      <article class="home-news-card"><a class="home-news-image" href="<?php echo esc_url(get_permalink($item)); ?>"><img src="<?php echo esc_url($image); ?>" alt="" loading="lazy"></a><div><p class="home-news-meta"><?php echo esc_html(src_field('news_date_label', $item->ID, '')); ?> <span>·</span> <?php echo esc_html(src_field('news_category', $item->ID, '')); ?></p><h2><a href="<?php echo esc_url(get_permalink($item)); ?>"><?php echo esc_html(get_the_title($item)); ?></a></h2><p class="home-news-excerpt"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($item->post_excerpt ?: $item->post_content), 26)); ?></p><a class="home-news-link" href="<?php echo esc_url(get_permalink($item)); ?>">Read more <span aria-hidden="true">→</span></a></div></article>
    <?php endforeach; ?>
  </div></section>
</main>
<?php get_footer(); ?>
