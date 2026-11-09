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
$hero_meta_parts = preg_split('/\s*·\s*/u', trim((string) ($settings['hero_meta'] ?? '')));
$country_details = src_get_footprint_countries();
$africa_svg = file_get_contents(get_template_directory() . '/assets/africa.svg');
$africa_svg = preg_replace('/^.*?<svg/s', '<svg', $africa_svg);
$africa_svg = preg_replace('/<svg/', '<svg class="africa-map-svg" aria-hidden="true" focusable="false"', $africa_svg, 1);
$kenya_svg = file_get_contents(get_template_directory() . '/assets/kenya-counties.svg');
$kenya_svg = preg_replace('/<svg/', '<svg class="kenya-county-svg" aria-hidden="true" focusable="false"', $kenya_svg, 1);
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
    <div class="container hero-content"><div class="hero-content-inner"><div class="hero-meta"><?php foreach ($hero_meta_parts as $index => $part) : $part = trim($part); if ($part === '') continue; ?><span><?php echo esc_html($part); ?></span><?php if ($index < count($hero_meta_parts) - 1) : ?><span aria-hidden="true">·</span><?php endif; ?><?php endforeach; ?></div><h1 class="display"><?php foreach ($hero_title as $line) : $line = trim($line); if ($line === '') continue; ?><span class="hero-title-line<?php echo strcasecmp($line, trim($settings['hero_accent_line'] ?? 'From Good')) === 0 ? ' hero-accent-line' : ''; ?>"><?php echo esc_html($line); ?></span><?php if (strcasecmp($line, trim($settings['hero_accent_line'] ?? 'From Good')) === 0) echo '&nbsp;'; ?><?php endforeach; ?></h1><p class="hero-copy"><?php echo esc_html($settings['hero_copy']); ?></p><div class="hero-actions"><a class="button" href="<?php echo esc_url(get_post_type_archive_link('programme')); ?>">Our Programmes</a><a class="watch-story" href="#our-story"><span class="watch-icon">▷</span><span>Watch Our Story</span></a></div></div></div>
    <?php if ($hero_programmes) : ?><div class="v3-hero-enrolments container"><p class="v3-enrolment-label">Now Enrolling</p><div class="v3-enrolment-grid"><?php foreach ($hero_programmes as $programme) :
      $cohort_label = trim((string) src_field('programme_next_cohort_label', $programme->ID, ''));
      $cohort_date = trim((string) src_field('programme_next_cohort_date', $programme->ID, ''));
      $fee_label = (string) src_field('programme_fee', $programme->ID, '[TBC]');
      $fee = trim(str_replace(['[', ']'], '', $fee_label)) ?: 'Free';
      $cohort_unknown = !$cohort_date && (!$cohort_label || strtoupper($cohort_label) === 'TBC');
      $fee_unknown = strtoupper(trim($fee_label)) === '[TBC]';
      $ticker = $cohort_unknown && $fee_unknown ? 'Next cohort opening soon' : 'Next cohort ' . ($cohort_label ?: 'TBC') . ' · ' . $fee;
      ?><button class="v3-enrol-card" type="button" data-src-open-registration="<?php echo esc_attr($programme->post_name); ?>" aria-controls="src-registration-dialog"><div class="v3-enrol-card-top"><span class="v3-enrol-logo"><img src="<?php echo esc_url($programme_logo_url($programme->ID)); ?>" alt=""></span><span class="v3-enrol-badge"><i></i> Enrolling now</span></div><h2><?php echo esc_html(src_field('programme_hero_title', $programme->ID, get_the_title($programme))); ?></h2><div class="ticker v3-enrol-next"><span><?php echo esc_html($ticker); ?></span></div><div class="v3-countdown" data-deadline="<?php echo esc_attr($cohort_date); ?>" aria-label="Cohort countdown"><span><b>00</b><small>Days</small></span><span><b>00</b><small>Hrs</small></span><span><b>00</b><small>Min</small></span><span><b>00</b><small>Sec</small></span></div><span class="v3-enrol-cta">Enroll now</span></button><?php endforeach; ?></div></div><?php endif; ?>
  </section>
  <?php wp_reset_postdata(); ?>

  <?php $impact_metrics = src_ordered_posts('impact_metric', 'metric_order'); ?>
  <?php if ($impact_metrics) : ?><section id="impact" class="v3-impact-section"><div class="v3-impact-pattern" aria-hidden="true"></div><svg class="v3-impact-ring" viewBox="0 0 700 700" aria-hidden="true" focusable="false"><circle cx="350" cy="350" r="309" fill="none" stroke="currentColor" stroke-width="82"></circle></svg><div class="v3-impact-inner"><header class="v3-impact-heading"><p class="eyebrow">Our Impact</p><h2 class="display">Growth you can see.<br><em>Change you can measure.</em></h2></header><div class="impact-grid">
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
        <div class="impact-stat"><div class="impact-number"><?php echo esc_html($prefix); ?><span data-impact-count="<?php echo esc_attr($value); ?>">0</span><?php echo esc_html($suffix); ?></div><div class="impact-label"><?php echo esc_html($label); ?></div></div>
        <div class="impact-panel" id="<?php echo esc_attr($panel_id); ?>" inert><p class="impact-panel-context"><?php echo esc_html(src_field('metric_context', $metric->ID, '')); ?></p><ul class="impact-panel-facts"><?php foreach ($facts as $fact) : ?><li><?php echo esc_html($fact); ?></li><?php endforeach; ?></ul><a class="impact-panel-link" href="<?php echo esc_url($target_link); ?>">See the programmes →</a></div>
      </article>
    <?php endforeach; ?>
  </div></div></section><?php endif; ?>

  <?php $county_details = src_get_footprint_counties(); ?>
  <section id="footprint" class="footprint-ref" data-footprint data-active-view="africa">
    <div class="footprint-brand-pattern" aria-hidden="true"></div>
    <svg class="footprint-brand-ring" viewBox="0 0 820 820" aria-hidden="true" focusable="false"><circle cx="410" cy="410" r="362" fill="none" stroke="currentColor" stroke-width="96" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg>
    <div class="container footprint-layout">
    <div class="footprint-heading"><p class="eyebrow">Where We Work</p><h2 class="display display-md">Our Footprint</h2><p class="footprint-intro">SRC is headquartered in Nairobi with active programmes across Kenya and six other African countries.</p>
      <div class="footprint-view-toggle" role="tablist" aria-label="Map view"><button type="button" role="tab" aria-selected="true" data-map-view="africa">Africa</button><button type="button" role="tab" aria-selected="false" data-map-view="kenya">Kenya</button></div>
      <div class="map-legend map-legend-africa" data-legend-view="africa"><?php foreach ($country_details as $code => $country) : ?><button type="button" data-country="<?php echo esc_attr($code); ?>"><i></i><span><?php echo esc_html($country['name']); ?></span><?php if (!empty($country['hq'])) : ?><b>HQ</b><?php endif; ?></button><?php endforeach; ?></div>
      <div class="map-legend map-legend-kenya" data-legend-view="kenya" hidden><p><?php echo esc_html(count($county_details)); ?> counties</p><?php foreach ($county_details as $code => $county) : ?><button type="button" data-country="<?php echo esc_attr($code); ?>"><i></i><span><?php echo esc_html($county['name']); ?></span><?php if (!empty($county['hq'])) : ?><b>HQ</b><?php endif; ?></button><?php endforeach; ?></div>
    </div>
    <div class="footprint-map-window">
      <div class="map-art map-art-africa" data-map-panel="africa" aria-label="Map of Africa showing SRC programme countries"><?php echo $africa_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <div class="map-hover-label" aria-hidden="true"><span></span></div>
        <?php foreach ($country_details as $code => $country) : ?><button class="map-dot<?php echo !empty($country['hq']) ? ' is-hq' : ''; ?>" type="button" data-country="<?php echo esc_attr($code); ?>" aria-label="View <?php echo esc_attr($country['name']); ?> programmes"><span></span></button><?php endforeach; ?>
      </div>
      <div class="map-art map-art-kenya" data-map-panel="kenya" hidden aria-label="Map of Kenya counties"><?php echo $kenya_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <div class="map-hover-label" aria-hidden="true"><span></span></div>
        <?php foreach ($county_details as $code => $county) : ?><button class="map-dot<?php echo !empty($county['hq']) ? ' is-hq' : ''; ?>" type="button" data-country="<?php echo esc_attr($code); ?>" aria-label="View <?php echo esc_attr($county['name']); ?> programmes"><span></span></button><?php endforeach; ?>
      </div>
    </div>
    </div>
    <aside class="footprint-detail" id="footprint-detail" role="dialog" aria-modal="true" aria-label="Country programmes" aria-hidden="true" inert>
      <div class="footprint-dialog">
        <button class="footprint-back" type="button" data-footprint-back aria-label="Close country programmes">×</button>
        <div class="footprint-dialog-hero"><img src="" alt="" class="footprint-dialog-image"><span class="footprint-dialog-shade"></span><div><p class="footprint-count"></p><h3></h3><p class="footprint-meta"></p></div></div>
        <div class="footprint-dialog-body"><div class="footprint-programmes"></div></div>
      </div>
    </aside>
    <script type="application/json" id="footprint-data" data-v3-both-views><?php echo wp_json_encode(['africa' => $country_details, 'kenya' => $county_details]); ?></script>
  </section>

  <?php
  $theme_order = ['resilience-livelihoods', 'livestock-agribusiness', 'womens-enterprise', 'investment-export-readiness', 'digital-learning'];
  $project_folder_defaults = [
    'resilience-livelihoods' => ['lift-nk', 'kuza-jamii', 'seed', 'village-capital-bds'],
    'livestock-agribusiness' => ['casha', 'kcb-mifugo-ni-mali', 'usaid-acdi-voca'],
    'womens-enterprise' => ['road-to-markets', 'road-to-growth', 'road-to-leadership', 'graca-machel-investor-readiness'],
    'investment-export-readiness' => ['investor-readiness', 'export-readiness', 'ande-circularity', 'palladium-usaid', 'giz-sme-loop', 'giz-business-scouts', 'ntf-iii-it-ites'],
    'digital-learning' => ['sheconnects', 'webinar-series', 'herventure'],
  ];
  $project_terms = get_terms(['taxonomy' => 'programme_theme', 'hide_empty' => false]);
  $project_terms = is_wp_error($project_terms) ? [] : array_values(array_filter(array_map(static function ($slug) use ($project_terms) { foreach ($project_terms as $term) if ($term->slug === $slug) return $term; return null; }, $theme_order)));
  $project_groups = [];
  foreach (is_wp_error($project_terms) ? [] : $project_terms as $term) {
    $items = get_posts(['post_type' => 'programme', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC', 'meta_query' => [['key' => '_src_v3_imported', 'value' => '1']], 'tax_query' => [['taxonomy' => 'programme_theme', 'field' => 'term_id', 'terms' => $term->term_id]]]);
    $default_order = array_flip($project_folder_defaults[$term->slug] ?? []);
    usort($items, static function ($a, $b) use ($default_order) {
      $order_for = static function ($post) use ($default_order) {
        $saved = (int) get_post_meta($post->ID, '_programme_project_order', true);
        return $saved > 0 ? $saved : 1000 + ($default_order[$post->post_name] ?? 999);
      };
      return ($order_for($a) <=> $order_for($b)) ?: strcasecmp($a->post_title, $b->post_title);
    });
    if ($items) $project_groups[] = [$term, $items];
  }
  $project_modal_data = [];
  ?>
  <section id="portfolio" class="project-ref"><div class="container"><div class="section-heading"><div><p class="eyebrow"><?php echo esc_html($settings['projects_eyebrow']); ?></p><h2 class="display display-md"><?php echo esc_html($settings['projects_heading']); ?></h2></div><a class="section-link" href="<?php echo esc_url(home_url('/programmes/')); ?>">See all case studies →</a></div><div class="projects-stage"><div class="projects-grid project-mosaic project-theme-grid">
    <?php foreach ($project_groups as $index => [$term, $programmes]) :
      $lead = $programmes[0];
      $image = src_image_url($lead->ID, 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=900&h=600&fit=crop&auto=format&q=82');
      $tile_image = src_project_tile_image_url($image);
      $funders = [];
      $impact_links = [];
      foreach ($programmes as $programme) {
        foreach ((array) src_field('programme_funders', $programme->ID, []) as $funder) if ($funder && !in_array($funder, $funders, true)) $funders[] = $funder;
        $impact_links[] = [html_entity_decode(get_the_title($programme), ENT_QUOTES | ENT_HTML5, 'UTF-8'), wp_trim_words(wp_strip_all_tags($programme->post_excerpt ?: $programme->post_content), 18), get_permalink($programme)];
      }
      $funder_label = $term->slug === 'womens-enterprise' ? 'Cherie Blair Foundation for Women' : (implode(' · ', array_slice($funders, 0, 2)) ?: 'SRC');
      $summary = html_entity_decode(src_field('programme_outcomes', $lead->ID, $term->description ?: wp_trim_words(wp_strip_all_tags($lead->post_excerpt ?: $lead->post_content), 35)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $project_title = $term->slug === 'womens-enterprise' ? 'Cherie Blair Foundation for Women' : html_entity_decode($term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $project_modal_data[] = ['title' => $project_title, 'shortName' => html_entity_decode($term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'partner' => $funder_label, 'funder' => $funder_label, 'image' => $image, 'location' => src_field('programme_reach', $lead->ID, 'Across Africa'), 'description' => $summary, 'result' => count($programmes) . ' programmes in this theme', 'url' => home_url('/portfolio/'), 'folders' => array_map(static function ($programme) { return ['title' => html_entity_decode(src_field('programme_hero_title', $programme->ID, get_the_title($programme)), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'slug' => $programme->post_name, 'description' => src_field('programme_audience', $programme->ID, wp_trim_words(wp_strip_all_tags($programme->post_content), 32)), 'outcomes' => src_field('programme_outcomes', $programme->ID, ''), 'status' => src_field('programme_status', $programme->ID, 'past'), 'fee' => src_field('programme_fee', $programme->ID, ''), 'duration' => src_field('programme_duration', $programme->ID, ''), 'dates' => src_field('programme_dates', $programme->ID, src_field('programme_start', $programme->ID, '')), 'reach' => src_field('programme_reach', $programme->ID, ''), 'url' => get_permalink($programme)]; }, $programmes)];
    ?>
      <button class="project-tile <?php echo in_array($index, [0, 3], true) ? 'span-2' : ''; ?>" type="button" data-project-index="<?php echo esc_attr(count($project_modal_data) - 1); ?>" aria-haspopup="dialog" aria-label="Open <?php echo esc_attr($project_title); ?>"><img src="<?php echo esc_url($tile_image); ?>" alt=""><span class="project-tile-overlay" aria-hidden="true"></span><span class="project-tile-content"><span class="project-tile-partner"><?php echo esc_html($funder_label); ?></span><strong><?php echo esc_html(count($programmes) . ' programmes'); ?></strong><span class="project-theme-name"><?php echo esc_html($project_title); ?></span></span><span class="project-open-label">Open project →</span></button>
    <?php endforeach; ?>
  </div></div><a class="project-ref-mobile-link" href="<?php echo esc_url(home_url('/programmes/')); ?>">See all case studies →</a></div></section>

  <dialog class="project-folder" id="project-folder" aria-labelledby="project-folder-title">
    <div class="project-folder-photo" aria-hidden="true"></div><div class="project-folder-shade" aria-hidden="true"></div>
    <div class="project-folder-content">
      <aside class="project-folder-profile">
        <button class="project-file-back" type="button" data-project-close aria-label="Back to projects">◀</button>
        <div class="project-profile-copy"><div class="project-name-display" id="project-folder-title"></div><p class="project-profile-description"></p><div class="project-result-chip"></div></div>
        <div class="project-funder"></div>
      </aside>
      <div class="project-index" aria-label="Project programme folders"></div>
      <div class="project-stage">
        <div class="project-folder-tabs">
          <a class="project-folder-tab" data-project-action="learn" href="#"><span class="project-tab-description">Goals, results and stories</span><span class="project-tab-label">See more about this project</span></a>
          <a class="project-folder-tab" data-project-action="involve" href="<?php echo esc_url(src_page_url('get-involved')); ?>"><span class="project-tab-description">Register or apply</span><span class="project-tab-label">Get involved</span></a>
        </div>
        <div class="programme-folder-layer" hidden>
          <span class="programme-folder-texture" aria-hidden="true"></span><button class="programme-folder-close" type="button" aria-label="Close programme details">×</button>
          <div class="programme-folder-copy"><div class="programme-folder-left"><div class="programme-folder-eyebrow"></div><h3></h3><div class="programme-status-chip"></div><p></p><div class="programme-folder-rule"></div></div><div class="programme-folder-right"><div class="programme-facts"></div><div class="programme-actions"><a class="programme-action-primary" href="#">View programme →</a><a class="programme-action-secondary" href="#">Learn more →</a></div></div></div>
        </div>
      </div>
    </div>
  </dialog>
  <script type="application/json" id="project-modal-data"><?php echo wp_json_encode($project_modal_data); ?></script>

  <?php
  $partner_posts = array_values(array_filter(src_ordered_posts('src_partner_item', 'partner_order'), static fn($post) => src_field('partner_marquee', $post->ID, '0') === '1'));
  $open_programmes = get_posts(['post_type' => 'programme', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_query' => [['key' => '_src_v3_imported', 'value' => '1'], ['key' => '_programme_status', 'value' => 'current'], ['key' => '_programme_enrolment', 'value' => 'open']]]);
  $open_priority = ['investor-readiness' => 1, 'export-readiness' => 2, 'herventure' => 3, 'webinar-series' => 4];
  usort($open_programmes, static fn($a, $b) => (($open_priority[$a->post_name] ?? 99) <=> ($open_priority[$b->post_name] ?? 99)));
  $road_programmes = [];
  foreach (['road-to-markets', 'road-to-growth'] as $road_slug) { $road = get_page_by_path($road_slug, OBJECT, 'programme'); if ($road && get_post_status($road) === 'publish') $road_programmes[] = $road; }
  ?>
  <section id="partners" class="home-partners-ref">
    <div class="home-partners-heading"><p class="eyebrow">Trusted By</p><h2 class="display">Our Partners</h2></div>
    <?php if ($partner_posts) : ?><div class="home-partner-marquee brand-section-content cx" aria-label="SRC partners">
      <?php for ($copy = 0; $copy < 2; $copy++) : foreach ($partner_posts as $partner) : $name = src_field('partner_name', $partner->ID, $partner->post_title); $logo = src_field('partner_logo_url', $partner->ID, ''); ?>
        <div class="home-partner-logo" <?php echo $copy ? 'aria-hidden="true"' : ''; ?>><?php if ($logo) : ?><img src="<?php echo esc_url($logo); ?>" alt="<?php echo $copy ? '' : esc_attr($name); ?>" loading="lazy"><?php else : ?><span><?php echo esc_html($name); ?></span><?php endif; ?></div>
      <?php endforeach; endfor; ?>
    </div><?php endif; ?>
  </section>

  <section id="programmes" class="home-programmes-ref">
    <svg class="home-programmes-ring" viewBox="0 0 600 600" aria-hidden="true" focusable="false"><circle cx="300" cy="300" r="264" fill="none" stroke="currentColor" stroke-width="72" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg>
    <div class="home-programmes-inner">
    <div class="home-programmes-heading"><div><p class="eyebrow">Applications Open</p><h2 class="display">Now Enrolling</h2></div><a href="<?php echo esc_url(get_post_type_archive_link('programme')); ?>">All programmes <span aria-hidden="true">→</span></a></div>
    <div class="home-programme-grid">
      <?php foreach (array_slice($open_programmes, 0, 4) as $programme) : $img = src_image_url($programme->ID, ''); $audience = src_field('programme_audience', $programme->ID, wp_trim_words(wp_strip_all_tags($programme->post_excerpt ?: $programme->post_content), 24)); $fee = src_field('programme_fee', $programme->ID, '[TBC]'); $dates = src_field('programme_dates', $programme->ID, 'TBC'); $reach = src_field('programme_reach', $programme->ID, 'Kenya'); ?>
        <article class="home-programme-card">
          <div class="home-programme-photo"><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(src_field('programme_hero_title', $programme->ID, get_the_title($programme))); ?>" loading="lazy"></div>
          <div class="home-programme-card-body"><span class="home-programme-status">● Enrolling now</span>
            <h3><?php echo esc_html(src_field('programme_hero_title', $programme->ID, get_the_title($programme))); ?></h3>
            <p><?php echo esc_html($audience); ?></p>
            <dl class="home-programme-facts">
              <div><dt>Dates</dt><dd><?php echo esc_html($dates); ?></dd></div>
              <div><dt>Fee</dt><dd><?php echo esc_html($fee); ?></dd></div>
              <div class="home-programme-fact-location"><dt>Location</dt><dd><?php echo esc_html($reach); ?></dd></div>
            </dl>
            <button class="home-programme-cta" type="button" data-src-open-registration="<?php echo esc_attr($programme->post_name); ?>" aria-controls="src-registration-dialog">Register</button>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if ($road_programmes) : ?><div class="home-coming-soon"><span>Coming soon</span><?php foreach ($road_programmes as $programme) : ?><a href="<?php echo esc_url(get_permalink($programme)); ?>"><?php echo esc_html(src_field('programme_hero_title', $programme->ID, get_the_title($programme))); ?> <span aria-hidden="true">→</span></a><?php endforeach; ?></div><?php endif; ?>
    </div>
  </section>

  <?php $stories = array_values(array_filter(src_ordered_posts('src_testimonial', 'story_order'), static fn($post) => src_field('story_featured', $post->ID, '0') === '1')); ?>
  <?php if ($stories) : ?><section id="testimonials" class="home-testimonials-ref" data-testimonial-carousel>
    <svg class="home-testimonial-ring" viewBox="0 0 780 780" aria-hidden="true" focusable="false"><circle cx="390" cy="390" r="346" fill="none" stroke="currentColor" stroke-width="88" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg>
    <div class="home-testimonial-slides">
      <?php foreach ($stories as $index => $story) : $person = src_field('story_person', $story->ID, $story->post_title); $image = src_field('story_image_url', $story->ID, ''); $paragraphs = (array) src_field('story_paragraphs', $story->ID, []); $video_url = src_field('story_video_url', $story->ID, ''); ?>
        <article class="home-testimonial-slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-testimonial-slide="<?php echo esc_attr($index); ?>" <?php echo $index ? 'hidden' : ''; ?>>
          <div class="home-testimonial-layout">
            <div class="home-testimonial-player">
              <button type="button" class="home-testimonial-video" <?php echo $video_url ? 'data-testimonial-video="' . esc_attr($video_url) . '" aria-label="Play ' . esc_attr($person) . '\'s story"' : 'aria-label="' . esc_attr($person) . '\'s video coming soon"'; ?>><img src="<?php echo esc_url($image); ?>" alt=""><span class="home-testimonial-shade" aria-hidden="true"></span><span class="home-testimonial-play" aria-hidden="true">▶</span><span class="home-testimonial-caption"><strong><?php echo esc_html($person); ?></strong><small>Story video coming soon</small></span></button>
            </div>
            <div class="home-testimonial-copy"><div class="home-testimonial-copy-main"><p class="home-testimonial-eyebrow">Voices From Our Entrepreneurs · <?php echo esc_html(($index + 1) . '/' . count($stories)); ?></p><h2><?php echo esc_html(src_field('story_headline', $story->ID, '')); ?></h2><div class="home-testimonial-body"><?php foreach (array_slice($paragraphs, 0, 2) as $paragraph) : ?><p><?php echo esc_html($paragraph); ?></p><?php endforeach; ?></div><div class="home-testimonial-person"><strong><?php echo esc_html($person); ?></strong><span><?php echo esc_html(src_field('story_business', $story->ID, '')); ?></span></div></div><button type="button" class="home-story-action">See their story</button></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="home-testimonial-controls"><button type="button" data-testimonial-prev aria-label="Previous story">← Prev</button><div><?php foreach ($stories as $index => $story) : ?><button type="button" data-testimonial-dot="<?php echo esc_attr($index); ?>" aria-label="Show story <?php echo esc_attr($index + 1); ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"></button><?php endforeach; ?></div><button type="button" data-testimonial-next aria-label="Next story">Next →</button></div>
  </section><?php endif; ?>

  <?php $news_posts = array_values(array_filter(src_ordered_posts('src_news_item', 'news_order'), static fn($post) => src_field('news_featured', $post->ID, '0') === '1')); ?>
  <?php if ($news_posts) : ?><section id="news" class="home-news-ref"><div class="home-news-inner"><header><div><p class="eyebrow">From SRC</p><h2 class="display">Latest News &amp; Updates</h2></div><a href="<?php echo esc_url(home_url('/news/')); ?>">All news <span aria-hidden="true">→</span></a></header><div class="home-news-grid">
    <?php foreach (array_slice($news_posts, 0, 3) as $news_item) : $image = src_field('news_image_url', $news_item->ID, ''); $programme = get_page_by_path(src_field('news_programme_slug', $news_item->ID, ''), OBJECT, 'programme'); $story_url = src_field('news_url', $news_item->ID, ''); if (!$story_url) $story_url = $programme ? get_permalink($programme) : get_permalink($news_item); ?>
      <article class="home-news-card"><a class="home-news-image" href="<?php echo esc_url($story_url); ?>"><img src="<?php echo esc_url($image); ?>" alt="" loading="lazy"></a><div><p class="home-news-meta"><?php echo esc_html(src_field('news_date_label', $news_item->ID, '')); ?> <span>·</span> <?php echo esc_html(src_field('news_category', $news_item->ID, '')); ?></p><h3><a href="<?php echo esc_url($story_url); ?>"><?php echo esc_html(get_the_title($news_item)); ?></a></h3><p class="home-news-excerpt"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($news_item->post_excerpt ?: $news_item->post_content), 26)); ?></p><a class="home-news-link" href="<?php echo esc_url($story_url); ?>">Read more <span aria-hidden="true">→</span></a></div></article>
    <?php endforeach; ?>
  </div></div></section><?php endif; ?>

  <?php $publications = src_ordered_posts('src_publication', 'publication_year'); $publication = null; foreach (array_reverse($publications) as $candidate) if (src_field('publication_featured', $candidate->ID, '0') === '1') { $publication = $candidate; break; } ?>
  <?php if ($publication) : $pdf = src_field('publication_pdf', $publication->ID, ''); $flipbook = src_field('publication_flipbook', $publication->ID, ''); ?>
    <section class="home-report-ref"><div class="home-report-inner"><div class="home-report-copy"><p class="eyebrow">Annual Publication</p><h2 class="display"><?php echo esc_html(get_the_title($publication)); ?></h2><p><?php echo esc_html(wp_strip_all_tags($publication->post_content)); ?></p><a class="home-report-download" href="<?php echo esc_url($pdf ?: '#impact-report'); ?>" <?php echo $pdf ? 'download' : 'aria-disabled="true"'; ?>>Download PDF</a></div><div class="home-report-viewer" id="impact-report"><?php if ($flipbook) : ?><iframe src="<?php echo esc_url($flipbook); ?>" title="<?php echo esc_attr(get_the_title($publication)); ?>" loading="lazy"></iframe><?php else : ?><div class="home-report-placeholder"><span>Publuu flipbook embed</span></div><?php endif; ?></div></div></section>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
