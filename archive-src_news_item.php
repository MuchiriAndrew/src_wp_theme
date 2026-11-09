<?php
if (!defined('ABSPATH')) exit;
get_header();
$items = src_ordered_posts('src_news_item', 'news_order');
$featured = $items ? $items[0] : null;
$featured_image = $featured ? src_field('news_image_url', $featured->ID, '') : '';
$archive_heading = src_option('news_archive_heading', 'Latest News');
?>
<main>
  <section class="news-archive-hero" <?php if ($featured_image) : ?>style="--news-archive-image:url('<?php echo esc_url($featured_image); ?>')"<?php endif; ?>>
    <div class="news-archive-shade"></div><div class="news-archive-inner">
      <p class="portfolio-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span>News</p>
      <p class="page-hero-eyebrow"><?php echo esc_html(src_option('news_archive_eyebrow', 'Ideas from the field')); ?></p><h1><?php echo esc_html($archive_heading); ?><br><em><?php echo esc_html(src_option('news_archive_emphasis', '& Updates')); ?></em></h1>
      <p class="news-archive-intro"><?php echo esc_html(src_option('news_archive_intro', 'Stories, practical insights and programme updates from SRC’s work with enterprises and cooperatives across the region.')); ?></p>
      <a class="news-archive-link" href="#news-stories"><?php echo esc_html(src_option('news_archive_action', 'Explore stories')); ?> <span aria-hidden="true">↓</span></a>
    </div><p class="news-archive-index" aria-hidden="true"><?php echo esc_html(src_option('news_archive_index_label', 'SRC / JOURNAL')); ?></p>
  </section>
  <section class="portfolio-companies news-archive-list" id="news-stories"><div class="home-news-grid">
    <?php foreach ($items as $item) : $image = src_field('news_image_url', $item->ID, ''); ?>
      <article class="home-news-card"><a class="home-news-image" href="<?php echo esc_url(get_permalink($item)); ?>"><img src="<?php echo esc_url($image); ?>" alt="" loading="lazy"></a><div><p class="home-news-meta"><?php echo esc_html(src_field('news_date_label', $item->ID, '')); ?> <span>·</span> <?php echo esc_html(src_field('news_category', $item->ID, '')); ?></p><h2><a href="<?php echo esc_url(get_permalink($item)); ?>"><?php echo esc_html(get_the_title($item)); ?></a></h2><p class="home-news-excerpt"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($item->post_excerpt ?: $item->post_content), 26)); ?></p><a class="home-news-link" href="<?php echo esc_url(get_permalink($item)); ?>"><?php echo esc_html(src_option('news_card_link_label', 'Read more')); ?> <span aria-hidden="true">→</span></a></div></article>
    <?php endforeach; ?>
  </div><?php if (!$items) : ?><p class="news-archive-empty"><?php echo esc_html(src_option('news_archive_empty_message', 'There are no news stories to show yet.')); ?></p><?php endif; ?></section>
</main>
<?php get_footer(); ?>
