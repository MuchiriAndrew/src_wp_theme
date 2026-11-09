<?php
if (!defined('ABSPATH')) exit;
get_header();
?>
<main class="src-woocommerce-product">
    <div class="src-woocommerce-product-shell">
        <?php if (function_exists('woocommerce_content')) : ?>
            <?php woocommerce_content(); ?>
        <?php else : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article><h1><?php the_title(); ?></h1><?php the_content(); ?></article>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
