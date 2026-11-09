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
        <?php if (function_exists('is_checkout') && is_checkout() && !is_wc_endpoint_url('order-received') && !is_wc_endpoint_url('order-pay')) : ?>
            <div class="src-checkout-intro"><span class="src-checkout-step">Registration · Final step</span><p>Confirm your contact details, review the programme, and complete your payment securely.</p></div>
        <?php endif; ?>
        <?php if (function_exists('is_shop') && is_shop()) : ?>
            <?php get_template_part('template-parts/commerce-catalog'); ?>
        <?php else : ?>
            <?php if (function_exists('src_v3_registration_return_order') && src_v3_registration_return_order()) : ?>
                <div class="src-checkout-intro"><span class="src-checkout-step">Registration · Confirmation</span><p>Your payment status is shown in the registration window. Keep this page open while you complete the M-PESA prompt.</p></div>
            <?php else : ?>
                <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
