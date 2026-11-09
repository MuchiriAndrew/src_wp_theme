<?php if (!defined('ABSPATH')) exit; $src_logo_id = absint(get_theme_mod('custom_logo')); $src_logo_url = $src_logo_id ? wp_get_attachment_image_url($src_logo_id, 'full') : ''; if (!$src_logo_url) $src_logo_url = get_template_directory_uri() . '/assets/src-logo.png'; $src_site_name = get_bloginfo('name'); ?><!doctype html>
<html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($src_site_name); ?> home"><img class="brand-logo" src="<?php echo esc_url($src_logo_url); ?>" alt="<?php echo esc_attr($src_site_name); ?>"></a>
    <button class="nav-toggle" type="button" aria-label="Open navigation" aria-controls="primary-navigation" aria-expanded="false"><span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span></button>
    <button class="nav-scrim" type="button" aria-label="Close navigation" tabindex="-1" aria-hidden="true"></button>
    <nav id="primary-navigation" class="primary-nav" aria-label="Primary navigation" aria-hidden="false">
      <div class="mobile-nav-head">
        <img src="<?php echo esc_url($src_logo_url); ?>" alt="<?php echo esc_attr($src_site_name); ?>" width="116" height="48">
        <button class="mobile-nav-close" type="button" aria-label="Close navigation"><span aria-hidden="true">×</span></button>
      </div>
      <?php
      if (has_nav_menu('primary')) {
        wp_nav_menu([
          'theme_location' => 'primary',
          'container' => false,
          'menu_class' => 'primary-menu',
          'items_wrap' => '<ul class="%2$s">%3$s</ul>',
          'walker' => new SRC_Reference_Nav_Walker(),
          'depth' => 2,
        ]);
      } else {
        src_menu_fallback();
      }
      ?>
      <a class="header-cta" href="<?php echo esc_url(src_option('header_cta_url', src_page_url('get-involved'))); ?>"><span class="header-cta-copy"><small><?php echo esc_html(src_option('header_cta_kicker', 'Let’s make an impact')); ?></small><strong><?php echo esc_html(src_option('header_cta_label', 'Partner With Us')); ?></strong></span><i aria-hidden="true">↗</i></a>
    </nav>
  </div>
</header>
