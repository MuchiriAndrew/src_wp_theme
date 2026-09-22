<?php if (!defined('ABSPATH')) exit; ?><!doctype html>
<html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<header class="site-header"><div class="container header-inner">
  <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Smart Regional Consultants home"><span class="brand-mark">SRC</span><span class="brand-copy">Smart Regional<small>Consultants</small></span></a>
  <button class="nav-toggle" type="button" aria-label="Open navigation" aria-controls="primary-navigation" aria-expanded="false"><span aria-hidden="true">☰</span></button>
  <button class="nav-scrim" type="button" aria-label="Close navigation" tabindex="-1"></button>
  <nav id="primary-navigation" class="primary-nav" aria-label="Primary navigation" aria-hidden="false">
    <div class="mobile-nav-head"><span>Menu</span><button class="mobile-nav-close" type="button" aria-label="Close navigation"><i aria-hidden="true"></i></button></div>
    <?php if (has_nav_menu('primary')) wp_nav_menu(['theme_location'=>'primary','container'=>false,'items_wrap'=>'%3$s']); else src_menu_fallback(); ?>
    <a class="header-cta" href="<?php echo esc_url(src_page_url('get-involved')); ?>"><span class="header-cta-copy"><small><?php echo esc_html(src_option('header_cta_kicker', 'Start a conversation')); ?></small><strong><?php echo esc_html(src_option('header_cta_label', 'Partner with us')); ?></strong></span><i aria-hidden="true">↗</i></a>
  </nav>
</div></header>
