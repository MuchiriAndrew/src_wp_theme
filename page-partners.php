<?php
if (!defined('ABSPATH')) exit;
get_header();
$page_id = get_queried_object_id();
$partners = src_ordered_posts('src_partner_item', 'partner_order');
$partners = array_values(array_filter($partners, static fn($partner) => src_field('partner_marquee', $partner->ID, '1') === '1'));
?>
<main class="partners-page">
  <section class="page-hero page-hero--inner partners-hero">
    <div class="partners-hero-content">
      <nav class="partners-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span aria-hidden="true">/</span><span aria-current="page">Clients &amp; Partners</span></nav>
      <p class="eyebrow eyebrow-light"><?php echo esc_html(src_page_field('page_hero_eyebrow', $page_id, 'Our Network')); ?></p>
      <h1 class="display display-lg"><?php echo esc_html(src_page_field('page_hero_title', $page_id, 'Clients & Partners')); ?></h1>
      <p class="lede lede-light"><?php echo esc_html(src_page_field('page_hero_intro', $page_id, 'We work with organisations committed to building resilient enterprises and inclusive economies.')); ?></p>
    </div>
  </section>
  <section class="partners-directory" aria-labelledby="partners-directory-title">
    <div class="partners-directory-heading"><p class="eyebrow"><?php echo esc_html(src_page_field('page_partners_directory_eyebrow', $page_id, 'Working together')); ?></p><h2 id="partners-directory-title" class="display display-md"><?php echo esc_html(src_page_field('page_partners_directory_heading', $page_id, 'A shared commitment to enterprise growth')); ?></h2><?php $directory_intro = (string) src_page_field('page_partners_directory_intro', $page_id, ''); if ($directory_intro !== '') : ?><p><?php echo esc_html($directory_intro); ?></p><?php endif; ?></div>
    <?php if ($partners) : ?><ul class="partners-directory-grid">
      <?php foreach ($partners as $partner) : $name = src_field('partner_name', $partner->ID, $partner->post_title); $logo = src_field('partner_logo_url', $partner->ID, ''); $link = src_field('partner_link', $partner->ID, ''); ?>
        <li class="partners-directory-item">
          <?php if ($link) : ?><a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($name); ?> (opens in a new tab)"><?php endif; ?>
          <?php if ($logo) : ?><img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy"><?php else : ?><span class="partners-directory-name"><?php echo esc_html($name); ?></span><?php endif; ?>
          <?php if ($link) : ?></a><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul><?php else : ?><p class="partners-directory-empty"><?php echo esc_html(src_page_field('page_partners_empty_message', $page_id, 'Partner profiles will be added here.')); ?></p><?php endif; ?>
  </section>
</main>
<?php get_footer(); ?>
