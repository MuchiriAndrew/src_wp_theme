<?php
if (!defined('ABSPATH')) exit;
get_header();
$theme_uri = get_template_directory_uri();
$get_content = static function ($type, $slug) { return get_page_by_path($slug, OBJECT, $type); };
$line_breaks = static function ($value) { return nl2br(esc_html($value)); };
$sectors = [];
foreach (src_ordered_posts('src_sector_item', 'sector_home_order') as $sector_post) $sectors[] = ['number' => src_field('sector_number', $sector_post->ID, sprintf('%02d', count($sectors) + 1)), 'title' => get_the_title($sector_post), 'description' => wp_trim_words(wp_strip_all_tags($sector_post->post_content), 28), 'image' => src_image_url($sector_post->ID, ''), 'link' => src_field('sector_link', $sector_post->ID, src_page_url('what-we-do'))];
$active = 0;
$pillars = src_ordered_posts('src_pillar_item', 'pillar_home_order');
$audiences = src_ordered_posts('src_audience_item', 'audience_home_order');
$stats = array_filter(src_ordered_posts('src_stat_item', 'stat_home_order'), static fn($post) => src_field('stat_home_enabled', $post->ID, '1') === '1');
$settings = array_merge(src_settings_defaults(), get_option('src_settings', []));
$hero_title = preg_split('/\R/', (string) ($settings['hero_title'] ?? "Building\nEnterprises\nFrom Good\nto Great."));
$country_details = src_get_footprint_countries();
$africa_svg = file_get_contents(get_template_directory() . '/assets/africa.svg');
$africa_svg = preg_replace('/^.*?<svg/s', '<svg', $africa_svg);
$africa_svg = preg_replace('/<svg/', '<svg class="africa-map-svg" aria-hidden="true" focusable="false"', $africa_svg, 1);
$hero_programmes_query = new WP_Query([
    'post_type' => 'programme', 'posts_per_page' => 4, 'orderby' => 'meta_value_num', 'meta_key' => '_programme_home_order', 'order' => 'ASC',
    'meta_query' => [['key' => '_src_v3_imported', 'value' => '1'], ['key' => '_programme_status', 'value' => 'current'], ['key' => '_programme_enrolment', 'value' => 'open']],
]);
$hero_programmes = $hero_programmes_query->posts;
$hero_priority = ['investor-readiness' => 1, 'export-readiness' => 2, 'herventure' => 3, 'webinar-series' => 4];
usort($hero_programmes, static function ($a, $b) use ($hero_priority) {
  $a_order = $hero_priority[$a->post_name] ?? (100 + (int) src_field('programme_home_order', $a->ID, 0));
  $b_order = $hero_priority[$b->post_name] ?? (100 + (int) src_field('programme_home_order', $b->ID, 0));
  return $a_order <=> $b_order;
});
$asset_uri = get_template_directory_uri() . '/assets/';
$programme_logo_url = static function ($post_id) use ($asset_uri) {
  $logos = (array) src_field('programme_logos', $post_id, []);
  $key = sanitize_key($logos[0] ?? 'src');
  if ($key === 'herventure') return $asset_uri . 'her-venture-transparent.svg';
  return $asset_uri . 'src-logo.png';
};
?>
<main>
  <section class="hero ref-hero v3-home-hero">
    <img class="hero-media v3-hero-poster" src="<?php echo esc_url($asset_uri . 'hero-man-with-camels-poster.png'); ?>" alt="Pastoral livestock crossing arid land">
    <video class="hero-media v3-hero-video" autoplay muted playsinline loop preload="metadata" poster="<?php echo esc_url($asset_uri . 'hero-man-with-camels-poster.png'); ?>" aria-hidden="true"><source src="<?php echo esc_url($asset_uri . 'hero-man-with-camels.mp4'); ?>" type="video/mp4"></video>
    <div class="v3-hero-watermark" aria-hidden="true"><img class="v3-watermark-mark" src="<?php echo esc_url($asset_uri . 'src-logo-mark.png'); ?>" alt=""><img class="v3-watermark-letters" src="<?php echo esc_url($asset_uri . 'src-logo-letters.png'); ?>" alt=""></div>
    <div class="container hero-content" data-reveal="left"><p class="hero-meta"><?php echo esc_html($settings['hero_meta']); ?></p><h1 class="display"><?php foreach ($hero_title as $line) : $line = trim($line); if ($line === '') continue; ?><span class="hero-title-line<?php echo strcasecmp($line, trim($settings['hero_accent_line'] ?? 'From Good')) === 0 ? ' hero-accent-line' : ''; ?>"><?php echo esc_html($line); ?></span><?php if (strcasecmp($line, trim($settings['hero_accent_line'] ?? 'From Good')) === 0) echo '&nbsp;'; ?><?php endforeach; ?></h1><p class="hero-copy"><?php echo esc_html($settings['hero_copy']); ?></p><div class="hero-actions"><a class="button" href="<?php echo esc_url(get_post_type_archive_link('programme')); ?>">Our Programmes</a><a class="watch-story" href="#our-story"><span class="watch-icon">▷</span><span>Watch Our Story</span></a></div></div>
    <?php if ($hero_programmes) : ?><div class="v3-hero-enrolments container"><p class="v3-enrolment-label">Now Enrolling</p><div class="v3-enrolment-grid"><?php foreach ($hero_programmes as $programme) : $cohort = src_field('programme_next_cohort_label', $programme->ID, 'TBC'); ?><a class="v3-enrol-card" href="<?php echo esc_url(get_permalink($programme)); ?>"><div class="v3-enrol-card-top"><span class="v3-enrol-logo"><img src="<?php echo esc_url($programme_logo_url($programme->ID)); ?>" alt=""></span><span class="v3-enrol-badge"><i></i> Enrolling now</span></div><h2><?php echo esc_html(src_field('programme_hero_title', $programme->ID, get_the_title($programme))); ?></h2><p class="v3-enrol-next">Next cohort <?php echo esc_html($cohort === 'TBC' ? 'opening soon' : $cohort); ?></p><div class="v3-countdown" aria-label="Cohort countdown not announced"><span><b>00</b><small>Days</small></span><span><b>00</b><small>Hrs</small></span><span><b>00</b><small>Min</small></span><span><b>00</b><small>Sec</small></span></div><span class="v3-enrol-cta">Enroll now</span></a><?php endforeach; ?></div></div><?php endif; ?>
  </section>
  <?php wp_reset_postdata(); ?>

  <?php $impact_metrics = src_ordered_posts('impact_metric', 'metric_order'); ?>
  <?php if ($impact_metrics) : ?><section id="impact" class="v3-impact-section"><div class="v3-impact-inner"><header class="v3-impact-heading"><p class="eyebrow">Our Impact</p><h2 class="display">Growth you can see.<br><em>Change you can measure.</em></h2></header><div class="impact-grid">
    <?php foreach ($impact_metrics as $metric) :
      $area = sanitize_html_class(src_field('metric_area', $metric->ID, 'money'));
      $value = max(0, (int) src_field('metric_value', $metric->ID, 0));
      $prefix = (string) src_field('metric_prefix', $metric->ID, '');
      $suffix = (string) src_field('metric_suffix', $metric->ID, '');
      $label = src_field('metric_label', $metric->ID, $metric->post_title);
      $detail = src_field('metric_detail', $metric->ID, '');
      $image_key = (string) src_field('metric_image', $metric->ID, '');
      $image = preg_match('/^photo-[a-zA-Z0-9-]+$/', $image_key) ? 'https://images.unsplash.com/' . rawurlencode($image_key) . '?w=1100&h=800&fit=crop&auto=format&q=82' : $image_key;
      $facts = (array) src_field('metric_facts', $metric->ID, []);
      $target_link = (string) src_field('metric_link', $metric->ID, get_post_type_archive_link('programme'));
      if (strpos($target_link, '#') === 0) $target_link = home_url('/') . $target_link;
      $panel_id = 'impact-panel-' . $area;
    ?>
      <article class="impact-tile impact-tile--<?php echo esc_attr($area); ?>">
        <button class="impact-tile-trigger" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr($panel_id); ?>" aria-label="More about <?php echo esc_attr($label); ?>"><img src="<?php echo esc_url($image); ?>" alt="" loading="lazy"><span class="impact-tile-shade"></span></button>
        <div class="impact-stat"><div class="impact-number"><span class="impact-prefix"><?php echo esc_html($prefix); ?></span><span data-impact-count="<?php echo esc_attr($value); ?>">0</span><span><?php echo esc_html($suffix); ?></span></div><div class="impact-label"><?php echo esc_html($label); ?></div><?php if ($detail !== '') : ?><div class="impact-detail"><?php echo esc_html($detail); ?></div><?php endif; ?></div>
        <div class="impact-panel" id="<?php echo esc_attr($panel_id); ?>" inert><p class="impact-panel-context"><?php echo esc_html(src_field('metric_context', $metric->ID, '')); ?></p><ul class="impact-panel-facts"><?php foreach ($facts as $fact) : ?><li><?php echo esc_html($fact); ?></li><?php endforeach; ?></ul><a class="impact-panel-link" href="<?php echo esc_url($target_link); ?>">See the programmes →</a></div>
      </article>
    <?php endforeach; ?>
  </div></div></section><?php endif; ?>

  <section id="our-story" class="sector-showcase"><div class="container"><div class="sector-layout"><div class="sector-intro" data-reveal="left"><p class="eyebrow"><?php echo esc_html($settings['sector_eyebrow']); ?></p><h2 class="display display-md"><?php echo $line_breaks($settings['sector_heading']); ?></h2><p><?php echo esc_html($settings['sector_intro']); ?></p><div class="sector-list"><?php foreach ($sectors as $i => $sector) : ?><button class="<?php echo $i === $active ? 'is-active' : ''; ?>" type="button" data-sector-index="<?php echo esc_attr($i); ?>"><span class="sector-index"><?php echo esc_html($sector['number']); ?></span><span class="sector-name"><?php echo esc_html($sector['title']); ?></span><span class="sector-rule"></span></button><?php endforeach; ?></div></div><div class="sector-stage" data-reveal="right"><div class="sector-visual"><img class="sector-image" id="sector-image" src="<?php echo esc_url($sectors[$active]['image']); ?>" alt="<?php echo esc_attr($sectors[$active]['title']); ?>"><span class="sector-tint"></span><span class="sector-number" id="sector-number"><?php echo esc_html($sectors[$active]['number']); ?></span></div><div class="sector-caption" id="sector-caption"><h3><?php echo esc_html($sectors[$active]['title']); ?></h3><p><?php echo esc_html($sectors[$active]['description']); ?></p><a class="text-cta" href="<?php echo esc_url($sectors[$active]['link']); ?>">Explore this sector →</a></div></div></div></div><script type="application/json" id="sector-data"><?php echo wp_json_encode($sectors); ?></script></section>

  <section class="pillar-grid"><?php foreach ($pillars as $pillar) : ?><div class="pillar" data-reveal><div><p class="eyebrow eyebrow-light"><?php echo esc_html(src_field('pillar_number', $pillar->ID, 'Pillar')); ?></p><h3><?php echo esc_html($pillar->post_title); ?></h3><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($pillar->post_content), 28)); ?></p></div><a class="text-cta" style="color:rgba(255,255,255,.62)" href="<?php echo esc_url(src_field('pillar_link', $pillar->ID, src_page_url('what-we-do'))); ?>">Learn more →</a></div><?php endforeach; ?></section>

  <section class="thematic-section"><div class="container"><div class="section-heading"><div><p class="eyebrow"><?php echo esc_html($settings['audience_eyebrow']); ?></p><h2 class="display display-md"><?php echo esc_html($settings['audience_heading']); ?></h2></div></div><div class="thematic-grid"><?php foreach ($audiences as $audience) : ?><a class="theme-chip" href="<?php echo esc_url(src_field('audience_link', $audience->ID, src_page_url('what-we-do'))); ?>"><b>→</b><span><?php echo esc_html($audience->post_title); ?></span></a><?php endforeach; ?></div></div></section>

  <section class="footprint-ref" data-footprint>
    <div class="footprint-heading"><p class="eyebrow"><?php echo esc_html($settings['footprint_eyebrow']); ?></p><h2 class="display display-md"><?php echo esc_html($settings['footprint_heading']); ?></h2></div>
    <div class="footprint-map-window">
      <div class="map-art" aria-label="Map of Africa showing SRC programme countries"><?php echo $africa_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <div class="map-hover-label" aria-hidden="true"><span></span><small>Click to explore</small></div>
        <?php foreach (array_keys($country_details) as $code) : ?><button class="map-dot" type="button" data-country="<?php echo esc_attr($code); ?>" aria-label="View <?php echo esc_attr($country_details[$code]['name']); ?> programmes"><span></span></button><?php endforeach; ?>
      </div>
    </div>
    <aside class="footprint-detail" id="footprint-detail" aria-live="polite" aria-hidden="true" inert>
      <button class="footprint-back" type="button" data-footprint-back>← Back to map</button>
      <p class="footprint-count"></p><h3></h3><p class="footprint-meta"></p><p class="footprint-detail-label">Programmes</p><div class="footprint-programmes"></div>
    </aside>
    <div class="map-legend"><?php foreach ($country_details as $code => $country) : ?><button type="button" data-country="<?php echo esc_attr($code); ?>"><i></i><span><?php echo esc_html($country['name']); ?></span><?php if (!empty($country['hq'])) : ?><b>HQ</b><?php endif; ?></button><?php endforeach; ?></div>
    <script type="application/json" id="footprint-data"><?php echo wp_json_encode($country_details); ?></script>
  </section>

  <?php
  $theme_order = ['womens-enterprise', 'investment-export-readiness', 'livestock-agribusiness', 'resilience-livelihoods', 'digital-learning'];
  $project_terms = get_terms(['taxonomy' => 'programme_theme', 'hide_empty' => true]);
  if (!is_wp_error($project_terms)) usort($project_terms, static function ($a, $b) use ($theme_order) { $ai = array_search($a->slug, $theme_order, true); $bi = array_search($b->slug, $theme_order, true); return ($ai === false ? 99 : $ai) <=> ($bi === false ? 99 : $bi); });
  $project_groups = [];
  foreach (is_wp_error($project_terms) ? [] : $project_terms as $term) {
    $items = get_posts(['post_type' => 'programme', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC', 'tax_query' => [['taxonomy' => 'programme_theme', 'field' => 'term_id', 'terms' => $term->term_id]]]);
    if ($items) $project_groups[] = [$term, $items];
  }
  $project_modal_data = [];
  ?>
  <section id="portfolio" class="project-ref"><div class="container"><div class="section-heading"><div><p class="eyebrow"><?php echo esc_html($settings['projects_eyebrow']); ?></p><h2 class="display display-md"><?php echo esc_html($settings['projects_heading']); ?></h2></div><a class="section-link" href="<?php echo esc_url(get_post_type_archive_link('programme')); ?>">See all case studies →</a></div><div class="project-mosaic project-theme-grid">
    <?php foreach ($project_groups as $index => [$term, $programmes]) :
      $lead = $programmes[0];
      $image = src_image_url($lead->ID, 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=900&h=600&fit=crop&auto=format&q=82');
      $funders = [];
      $impact_links = [];
      foreach ($programmes as $programme) {
        foreach ((array) src_field('programme_funders', $programme->ID, []) as $funder) if ($funder && !in_array($funder, $funders, true)) $funders[] = $funder;
        $impact_links[] = [get_the_title($programme), wp_trim_words(wp_strip_all_tags($programme->post_excerpt ?: $programme->post_content), 18), get_permalink($programme)];
      }
      $summary = $term->description ?: wp_trim_words(wp_strip_all_tags($lead->post_excerpt ?: $lead->post_content), 35);
      $project_modal_data[] = ['title' => $term->name, 'partner' => 'SRC Project Portfolio', 'funder' => implode(' · ', array_slice($funders, 0, 2)) ?: 'SRC', 'image' => $image, 'location' => src_field('programme_reach', $lead->ID, 'Across Africa'), 'description' => $summary, 'stats' => [[(string) count($programmes), 'programmes'], [count($funders) ? (string) count($funders) : 'SRC', 'delivery partners']], 'impacts' => array_map(static fn($entry) => [$entry[0], $entry[1]], $impact_links), 'programmes' => $impact_links, 'url' => get_post_type_archive_link('programme'), 'externalUrl' => ''];
    ?>
      <button class="project-tile <?php echo in_array($index, [0, 3], true) ? 'span-2' : ''; ?>" type="button" data-project-index="<?php echo esc_attr(count($project_modal_data) - 1); ?>" data-reveal aria-haspopup="dialog"><img src="<?php echo esc_url($image); ?>" alt=""><span class="project-tile-arrow">↗</span><span class="project-tile-content"><span class="project-tile-partner"><?php echo esc_html(implode(' · ', array_slice($funders, 0, 2)) ?: 'SRC'); ?></span><strong><?php echo esc_html(count($programmes) . ' programmes'); ?></strong><span class="project-theme-name"><?php echo esc_html($term->name); ?></span></span><span class="project-open-label">Open project →</span></button>
    <?php endforeach; ?>
  </div></div></section>

  <dialog class="project-dialog" id="project-dialog" aria-labelledby="project-modal-title">
    <div class="project-modal-shell">
      <aside class="project-modal-aside"><img class="project-modal-image" src="" alt=""><span class="project-modal-tint"></span><div class="project-modal-summary"><button class="dialog-close" type="button">← Back</button><p class="project-modal-kicker"></p><h2 id="project-modal-title"></h2><p class="project-modal-description"></p><div class="project-modal-location"><span>Geography</span><strong></strong></div></div></aside>
      <div class="project-modal-tabs" role="tablist" aria-label="Project details"><button id="project-tab-overview" type="button" role="tab" aria-controls="project-panel-overview" aria-selected="true" data-project-tab="overview">Overview</button><button id="project-tab-impact" type="button" role="tab" aria-controls="project-panel-impact" aria-selected="false" data-project-tab="impact">Impact &amp; Results</button><button id="project-tab-website" type="button" role="tab" aria-controls="project-panel-website" aria-selected="false" data-project-tab="website">Programmes</button><button id="project-tab-involved" type="button" role="tab" aria-controls="project-panel-involved" aria-selected="false" data-project-tab="involved">Get Involved</button></div>
      <div class="project-modal-main"><section id="project-panel-overview" class="project-tab-panel is-active" role="tabpanel" aria-labelledby="project-tab-overview" data-project-panel="overview"><h3></h3><p class="project-panel-location"></p><p class="project-panel-description"></p><div class="project-stats"></div></section><section id="project-panel-impact" class="project-tab-panel" role="tabpanel" aria-labelledby="project-tab-impact" data-project-panel="impact"><h3>Impact &amp; Results</h3><div class="project-impact-list"></div></section><section id="project-panel-website" class="project-tab-panel" role="tabpanel" aria-labelledby="project-tab-website" data-project-panel="website"><p class="project-panel-eyebrow">Project folders</p><h3>Programmes in this theme</h3><div class="project-impact-list project-programme-links"></div></section><section id="project-panel-involved" class="project-tab-panel" role="tabpanel" aria-labelledby="project-tab-involved" data-project-panel="involved"><p class="project-panel-eyebrow">Join this programme</p><h3>Get involved with <span class="project-inline-title"></span></h3><p>Whether as a donor, technical partner, or implementing organisation — there are several ways to contribute to this programme's goals.</p><a class="button" href="<?php echo esc_url(src_page_url('get-involved')); ?>">Explore how to get involved →</a></section></div>
    </div>
  </dialog>
  <script type="application/json" id="project-modal-data"><?php echo wp_json_encode($project_modal_data); ?></script>

  <section class="partners-ref">
    <div class="container partner-heading"><p class="eyebrow"><?php echo esc_html($settings['partners_eyebrow']); ?></p><h2 class="display"><?php echo esc_html($settings['partners_heading']); ?></h2></div>
    <div class="partner-grid">
      <?php foreach (src_ordered_posts('src_partner_item', 'partner_home_order') as $partner) :
        $type = src_field('partner_type', $partner->ID, 'logo');
        $span = src_field('partner_span', $partner->ID, '1');
        $name = src_field('partner_name', $partner->ID, $partner->post_title);
        $abbr = src_field('partner_abbreviation', $partner->ID, $partner->post_title);
        $color = src_field('partner_color', $partner->ID, 'red');
        $color = in_array($color, ['red', 'charcoal', 'orange'], true) ? $color : 'red';
        $image = src_field('partner_image_url', $partner->ID, '');
        $is_short = mb_strlen(strtok($abbr, "\n")) <= 4;
        $classes = 'partner-tile partner-color-' . $color . ($is_short ? ' partner-short' : '') . ($span === '2' ? ' span-2' : '') . ($type === 'image' ? ' photo' : '') . ($type === 'cta' ? ' cta' : '');
        if ($type === 'cta') : ?>
          <a href="<?php echo esc_url(src_field('partner_link', $partner->ID, src_page_url('partners'))); ?>" class="<?php echo esc_attr($classes); ?>"><span><?php echo esc_html($partner->post_title); ?></span><i aria-hidden="true">→</i></a>
        <?php elseif ($type === 'image') : ?>
          <div class="<?php echo esc_attr($classes); ?>"><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($name); ?>"></div>
        <?php else : ?>
          <div class="<?php echo esc_attr($classes); ?>"><div class="partner-tile-copy"><strong><?php echo nl2br(esc_html($abbr)); ?></strong><?php if (str_replace("\n", '', $abbr) !== $name) : ?><small><?php echo esc_html($name); ?></small><?php endif; ?></div></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>

  <?php $team_posts = array_values(array_filter(src_ordered_posts('team_member', 'team_home_order'), static fn($post) => src_field('team_home_featured', $post->ID, '0') === '1')); ?>
  <section class="team-ref" data-team-carousel>
    <div class="team-ref-header">
      <div><p class="eyebrow"><?php echo esc_html($settings['team_eyebrow']); ?></p><h2 class="display"><?php echo esc_html($settings['team_heading']); ?></h2></div>
      <div class="team-heading-actions"><a class="team-all-link team-all-link-desktop" href="<?php echo esc_url(get_post_type_archive_link('team_member')); ?>">View all team members <span aria-hidden="true">→</span></a><div class="team-ref-arrows"><button type="button" aria-label="Previous" data-team-prev disabled>←</button><button type="button" aria-label="Next" data-team-next>→</button></div></div>
    </div>
    <div class="team-ref-viewport"><div class="team-ref-grid" id="team-ref-track">
      <?php foreach ($team_posts as $index => $post) :
        $img = src_image_url($post->ID, 'https://images.unsplash.com/photo-1573497019418-b400bb3ab074?w=600&h=780&fit=crop&crop=faces,top&auto=format&q=80');
        $color = src_field('team_panel_color', $post->ID, 'charcoal');
        $color = in_array($color, ['red', 'charcoal', 'orange'], true) ? $color : 'charcoal'; ?>
        <article class="team-ref-card team-panel-<?php echo esc_attr($color); ?>" data-team-card data-team-index="<?php echo esc_attr($index); ?>" tabindex="0" role="button" aria-label="Animate <?php echo esc_attr(get_the_title($post)); ?> portrait"><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($post)); ?>" draggable="false"><span class="team-ref-vignette" aria-hidden="true"></span><span class="team-ref-bottom-gradient" aria-hidden="true"></span><div class="team-ref-card-content"><div class="team-ref-role"><?php echo esc_html(src_field('team_role', $post->ID, 'Team member')); ?></div><h3><?php echo esc_html(get_the_title($post)); ?></h3></div><p class="team-ref-bio"><?php echo esc_html(src_field('team_quote', $post->ID, wp_trim_words(wp_strip_all_tags($post->post_excerpt ?: $post->post_content), 24))); ?></p></article>
      <?php endforeach; ?>
    </div></div>
    <div class="team-ref-footer"><a class="team-all-link team-all-link-mobile" href="<?php echo esc_url(get_post_type_archive_link('team_member')); ?>">View all team members <span aria-hidden="true">→</span></a><div class="team-ref-dots"><?php foreach ($team_posts as $i => $post) : ?><button type="button" data-team-dot="<?php echo esc_attr($i); ?>" aria-label="Go to <?php echo esc_attr(get_the_title($post)); ?>"></button><?php endforeach; ?></div></div>
  </section>

  <section class="programmes-ref"><div class="container"><div class="section-heading"><div><p class="eyebrow"><?php echo esc_html($settings['programmes_eyebrow']); ?></p><h2 class="display display-md"><?php echo $line_breaks($settings['programmes_heading']); ?></h2></div><a class="section-link" href="<?php echo esc_url(get_post_type_archive_link('programme')); ?>">View all programmes →</a></div><div class="programme-ref-grid"><?php $programme_posts = array_values(array_filter(src_ordered_posts('programme', 'programme_home_order'), static fn($post) => src_field('src_v3_imported', $post->ID, '0') === '1' && src_field('programme_home_featured', $post->ID, '0') === '1')); foreach ($programme_posts as $post) : $status_value = strtolower(trim(src_field('programme_status', $post->ID, 'upcoming'))); $enrolment_value = src_field('programme_enrolment', $post->ID, 'closed'); $status_key = $status_value === 'past' ? 'closed' : ($status_value === 'upcoming' ? 'upcoming' : ($enrolment_value === 'open' ? 'enrolling' : 'running')); $status_label = $status_key === 'enrolling' ? 'Enrolling Now' : ($status_key === 'closed' ? 'Closed' : 'Upcoming'); $img = src_image_url($post->ID, 'https://images.unsplash.com/photo-1573497019418-b400bb3ab074?w=500&h=280&fit=crop&crop=center&auto=format&q=75'); ?><article class="programme-ref-card" data-reveal><div class="programme-ref-card-media"><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($post)); ?>"><span class="programme-status programme-status-<?php echo esc_attr($status_key); ?>"><?php echo esc_html($status_label); ?></span></div><div class="programme-ref-card-body"><h3><?php echo esc_html(get_the_title($post)); ?></h3><p><?php echo esc_html(wp_trim_words(wp_strip_all_tags($post->post_excerpt ?: $post->post_content), 24)); ?></p><div class="programme-meta"><span>⏱ <?php echo esc_html(src_field('programme_duration', $post->ID, 'Flexible')); ?></span><span>📅 <?php echo esc_html(src_field('programme_next_cohort_label', $post->ID, src_field('programme_dates', $post->ID, 'Next cohort TBC'))); ?></span><?php if (src_field('programme_spots', $post->ID, '')) : ?><span>👥 <?php echo esc_html(src_field('programme_spots', $post->ID)); ?></span><?php endif; ?></div><a class="button" href="<?php echo esc_url(get_permalink($post)); ?>">Learn more & register</a></div></article><?php endforeach; ?></div></div></section>

  <section class="quote-band"><div class="container quote-grid"><div><p class="eyebrow eyebrow-light"><?php echo esc_html($settings['quote_eyebrow']); ?></p><blockquote><?php echo esc_html($settings['quote_text']); ?></blockquote><div class="quote-attribution"><?php echo esc_html($settings['quote_attribution']); ?></div></div><div class="quote-aside"><p><?php echo esc_html($settings['quote_summary']); ?></p><div class="hero-actions"><a class="button" href="<?php echo esc_url(get_post_type_archive_link('project')); ?>">Our case studies</a><a class="button button-ghost" href="<?php echo esc_url(src_page_url('get-involved')); ?>">Work with us</a></div></div></div></section>
</main>
<?php get_footer(); ?>
