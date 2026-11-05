<?php
if (!defined('ABSPATH')) exit;
get_header();
the_post();
$image = src_field('news_image_url', get_the_ID(), '');
?>
<main>
  <article class="news-story">
    <header class="news-story-header"><p class="portfolio-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><a href="<?php echo esc_url(home_url('/news/')); ?>">News</a><span>/</span><?php the_title(); ?></p><p class="page-hero-eyebrow"><?php echo esc_html(src_field('news_category', get_the_ID(), 'From SRC')); ?></p><h1><?php the_title(); ?></h1><p><?php echo esc_html(src_field('news_date_label', get_the_ID(), '')); ?></p></header>
    <?php if ($image) : ?><img class="news-story-image" src="<?php echo esc_url($image); ?>" alt=""><?php endif; ?>
    <div class="news-story-content"><?php the_content(); ?></div>
  </article>
</main>
<?php get_footer(); ?>
