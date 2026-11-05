<?php
$commerce_title = $args['title'] ?? get_the_title();
get_header();
?>
<main class="src-commerce-main src-commerce-main-blocks">
    <section class="src-commerce-hero">
        <div class="container">
            <p class="eyebrow eyebrow-light">Smart Regional Consultants</p>
            <h1 class="display display-md"><?php echo esc_html($commerce_title); ?></h1>
        </div>
    </section>
    <div class="src-commerce-content container">
        <?php if (function_exists('is_shop') && is_shop()) : ?>
            <?php get_template_part('template-parts/commerce-catalog'); ?>
        <?php else : ?>
            <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
