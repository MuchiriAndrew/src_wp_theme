<?php
if (!defined('ABSPATH')) exit;

$programme_archive = get_post_type_archive_link('programme');
if (!$programme_archive) $programme_archive = home_url('/programmes/');
$footer_page_title = static function ($slug, $fallback) {
    $page = get_page_by_path($slug);
    return $page ? get_the_title($page) : $fallback;
};

$footer_columns = [
    [
        'location' => 'footer_our_story',
        'title' => src_option('footer_story_title', 'Our Story'),
        'links' => [
            [$footer_page_title('about', 'About SRC'), src_page_url('about')],
            [$footer_page_title('what-we-do', 'What We Do'), src_page_url('what-we-do')],
            [src_option('footer_leadership_label', 'Leadership'), get_post_type_archive_link('team_member') ?: src_page_url('leadership')],
            [$footer_page_title('pool-of-experts', 'Pool of Experts'), src_page_url('pool-of-experts')],
            [$footer_page_title('careers', 'Careers'), src_page_url('careers')],
        ],
    ],
    [
        'location' => 'footer_programmes',
        'title' => src_option('footer_programmes_title', 'Programmes'),
        'links' => [
            [src_option('programmes_current_heading', 'Current Programmes'), add_query_arg('status', 'current', $programme_archive)],
            [src_option('programmes_upcoming_heading', 'Upcoming'), add_query_arg('status', 'upcoming', $programme_archive)],
            [src_option('programmes_past_heading', 'Past Programmes'), add_query_arg('status', 'past', $programme_archive)],
        ],
    ],
    [
        'location' => 'footer_more',
        'title' => src_option('footer_more_title', 'More'),
        'links' => [
            [$footer_page_title('portfolio', 'Portfolio'), src_page_url('portfolio')],
            [$footer_page_title('get-involved', 'Get Involved'), src_page_url('get-involved')],
            [$footer_page_title('contact', 'Contact'), src_page_url('contact')],
        ],
    ],
    [
        'location' => 'footer_legal',
        'title' => src_option('footer_legal_title', 'Legal'),
        'links' => array_values(array_filter([
            ($page = get_page_by_path('privacy-policy', OBJECT, 'page')) && $page->post_status === 'publish' ? [get_the_title($page), get_permalink($page)] : null,
            ($page = get_page_by_path('terms-of-use', OBJECT, 'page')) && $page->post_status === 'publish' ? [get_the_title($page), get_permalink($page)] : null,
            ($page = get_page_by_path('cookie-policy', OBJECT, 'page')) && $page->post_status === 'publish' ? [get_the_title($page), get_permalink($page)] : null,
        ], static function ($link) { return $link !== null; })),
    ],
];

$footer_socials = [
    'social_linkedin' => [
        'label' => 'LinkedIn',
        'path' => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286ZM5.337 7.433a2.062 2.062 0 1 1 0-4.123 2.062 2.062 0 0 1 0 4.123ZM7.119 20.452H3.555V9h3.564v11.452Z',
    ],
    'social_x' => [
        'label' => 'X',
        'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231Z',
    ],
    'social_facebook' => [
        'label' => 'Facebook',
        'path' => 'M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.414c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.972H15.83c-1.49 0-1.955.931-1.955 1.887v2.261h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073Z',
    ],
    'social_youtube' => [
        'label' => 'YouTube',
        'path' => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814ZM9.545 15.568V8.432L15.818 12l-6.273 3.568Z',
    ],
];
?>

<?php if (is_front_page()) : ?>
  <section class="stay-connected brand-decor-section" aria-labelledby="stay-connected-heading">
    <svg class="stay-connected-ring" viewBox="0 0 520 520" aria-hidden="true" focusable="false"><circle cx="260" cy="260" r="228" fill="none" stroke="currentColor" stroke-width="64" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg>
    <div class="stay-connected-grain" aria-hidden="true"></div>
    <div class="stay-connected-topography" aria-hidden="true"></div>
    <div class="stay-connected-grid cx">
      <div class="stay-connected-column">
        <p class="stay-connected-kicker"><?php echo esc_html(src_option('newsletter_eyebrow', 'Stay connected')); ?></p>
        <h2 id="stay-connected-heading" class="stay-connected-headline"><?php echo esc_html(src_option('newsletter_heading', 'News worth opening.')); ?></h2>
      </div>
      <div class="stay-connected-column stay-connected-action"><p><?php echo esc_html(src_option('newsletter_intro', 'Stories, updates and practical insights from our work with enterprises across the region.')); ?></p><a class="stay-connected-pill" href="<?php echo esc_url(home_url('/news/')); ?>"<?php echo esc_html(src_option('newsletter_link_label', 'Explore latest news')); ?> <span aria-hidden="true">→</span></a></div>
    </div>
  </section>
<?php endif; ?>

<footer class="site-footer">
  <img class="site-footer-watermark" src="<?php echo esc_url(get_template_directory_uri() . '/assets/src-logo-footer.png'); ?>" alt="" aria-hidden="true">
  <div class="site-footer-grid cx">
    <?php foreach ($footer_columns as $column) : ?>
      <?php if (empty($column['links']) && !has_nav_menu($column['location'])) continue; ?>
      <nav class="site-footer-column" aria-label="<?php echo esc_attr($column['title']); ?>">
        <h2 class="site-footer-heading"><?php echo esc_html($column['title']); ?></h2>
        <?php if (has_nav_menu($column['location'])) : ?>
          <?php wp_nav_menu(['theme_location' => $column['location'], 'container' => false, 'menu_class' => 'site-footer-links', 'depth' => 1, 'fallback_cb' => false]); ?>
        <?php else : ?>
          <ul class="site-footer-links">
            <?php foreach ($column['links'] as [$label, $url]) : ?>
              <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </nav>
    <?php endforeach; ?>

    <div class="site-footer-column site-footer-contact">
      <h2 class="site-footer-heading"><?php echo esc_html(src_option('footer_contact_title', 'Contact')); ?></h2>
      <div class="site-footer-contact-details">
        <a href="mailto:<?php echo esc_attr(src_option('contact_email', 'info@srctech.co.ke')); ?>"><?php echo esc_html(src_option('contact_email', 'info@srctech.co.ke')); ?></a>
        <p><?php echo esc_html(src_option('footer_company_name', 'Smart Regional Consultants Ltd.')); ?><br><?php echo esc_html(src_option('contact_city', 'Nairobi, Kenya')); ?></p>
      </div>
    </div>
  </div>

  <div class="site-footer-copyright">
    <div class="site-footer-copyright-inner cx">
      <p>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(src_option('footer_company_name', 'Smart Regional Consultants Ltd.')); ?></p>
      <div class="site-footer-socials">
        <?php foreach ($footer_socials as $option => $social) : $url = src_option($option, ''); ?>
          <?php if ($url) : ?>
            <a class="site-footer-social" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($social['label']); ?>">
              <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="<?php echo esc_attr($social['path']); ?>"></path></svg>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</footer>

<?php get_template_part('template-parts/registration-modal'); wp_footer(); ?></body></html>
