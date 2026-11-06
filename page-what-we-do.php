<?php
get_header();

$page_id = get_queried_object_id();
$hero_title = (string) src_page_field('page_hero_title', $page_id, 'Building SMEs from good to great');
$hero_emphasis = (string) src_page_field('page_hero_emphasis', $page_id, 'from good to great');
$hero_eyebrow = (string) src_page_field('page_hero_eyebrow', $page_id, 'WHAT WE DO');
$hero_intro = (string) src_page_field('page_hero_intro', $page_id, 'We strengthen how enterprises run, prepare them for capital and connect them to buyers.');
$hero_image = (string) src_page_field('page_hero_image_url', $page_id, 'https://images.unsplash.com/photo-1687422808248-f807f4ea2a2e?auto=format&fit=crop&w=2000&q=85');
$title_parts = $hero_emphasis !== '' ? explode($hero_emphasis, $hero_title, 2) : [$hero_title];
$pillar_rows = src_parse_rows(src_page_field('page_service_pillars', $page_id, ''));
$pillars = [];
foreach ($pillar_rows as $row) {
    if (count($row) < 6) continue;
    $key = sanitize_title($row[0]);
    if ($key === '') continue;
    $start = sanitize_hex_color($row[6] ?? '') ?: '#B3262C';
    $end = sanitize_hex_color($row[7] ?? '') ?: '#8B0005';
    $accent = sanitize_hex_color($row[8] ?? '') ?: $start;
    $pillars[] = [
        'key' => $key,
        'number' => $row[1] ?: sprintf('%02d', count($pillars) + 1),
        'title' => $row[2] ?? '',
        'tagline' => $row[3] ?? '',
        'description' => $row[4] ?? '',
        'audience' => $row[5] ?? '',
        'gradient' => 'linear-gradient(175deg, ' . $start . ' 0%, ' . $end . ' 100%)',
        'color' => $accent,
    ];
}

$pillar_programmes = [];
foreach ($pillars as $pillar) {
    $programmes = get_posts([
        'post_type' => 'programme',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => [['key' => '_src_v3_imported', 'value' => '1']],
        'tax_query' => [[
            'taxonomy' => 'programme_pillar',
            'field' => 'slug',
            'terms' => $pillar['key'],
        ]],
    ]);
    usort($programmes, static function ($a, $b) {
        $a_order = (int) get_post_meta($a->ID, '_programme_pillar_order', true);
        $b_order = (int) get_post_meta($b->ID, '_programme_pillar_order', true);
        $a_order = $a_order > 0 ? $a_order : PHP_INT_MAX;
        $b_order = $b_order > 0 ? $b_order : PHP_INT_MAX;
        return ($a_order <=> $b_order) ?: strcasecmp($a->post_title, $b->post_title);
    });
    $pillar_programmes[$pillar['key']] = array_slice($programmes, 0, 10);
}

$programme_archive = get_post_type_archive_link('programme') ?: home_url('/programmes/');
$get_involved_url = src_page_url('get-involved');
$logo_assets = [
    'src' => 'src-logo.png', 'herventure' => 'her-venture-transparent.svg',
    'boma-project' => 'partner-logos/boma-project.png', 'cherie-blair-foundation' => 'partner-logos/cherie-blair-foundation.png',
    'dhl' => 'partner-logos/dhl.png', 'usaid' => 'partner-logos/usaid-programme.png', 'village-capital' => 'partner-logos/village-capital.png',
    'giz' => 'partner-logos/giz.png', 'goal' => 'partner-logos/goal.png', 'united-nations' => 'partner-logos/united-nations.png',
    'world-bank' => 'partner-logos/world-bank.png', 'wwf' => 'partner-logos/wwf.png', 'kcdf' => 'partner-logos/kcdf.png',
];
$logo_labels = ['ande' => 'ANDE', 'boma-project' => 'BOMA Project', 'afrishela' => 'Afrishela', 'graca-machel-trust' => 'Graça Machel Trust', 'sheconnects' => 'SheConnects', 'care' => 'CARE', 'mastercard-foundation' => 'Mastercard Foundation', 'herventure' => 'HerVenture', 'cherie-blair-foundation' => 'Cherie Blair Foundation', 'dhl' => 'DHL', 'src' => 'SRC', 'kcb-foundation' => 'KCB Foundation', 'usaid' => 'USAID', 'village-capital' => 'Village Capital', 'giz' => 'GIZ', 'goal' => 'GOAL', 'united-nations' => 'United Nations', 'world-bank' => 'World Bank', 'wwf' => 'WWF', 'kcdf' => 'KCDF'];
$status_label = static function ($programme) {
    $enrolment = (string) get_post_meta($programme->ID, '_programme_enrolment', true);
    $status = (string) get_post_meta($programme->ID, '_programme_status', true);
    if ($enrolment === 'open') return 'Enrolling now';
    if ($status === 'upcoming') return 'Upcoming';
    if ($status === 'current') return 'In delivery';
    return 'Completed';
};
$programme_title = static function ($programme) {
    return (string) (get_post_meta($programme->ID, '_programme_hero_title', true) ?: get_post_meta($programme->ID, '_programme_short_name', true) ?: get_the_title($programme));
};
$programme_short = static function ($programme) {
    return (string) (get_post_meta($programme->ID, '_programme_short_name', true) ?: get_the_title($programme));
};
$pagehero_hash = 0;
foreach (str_split(strtolower($hero_eyebrow)) as $character) $pagehero_hash = (($pagehero_hash * 31) + ord($character)) & 0xFFFFFFFF;
$ring_rotation = [0, 35, 70, 110, 150][$pagehero_hash % 5];
?>

<main class="what-we-do-v3" data-what-we-do>
  <section class="page-hero page-hero--inner what-we-do-v3-hero" aria-labelledby="what-we-do-title">
    <div class="what-we-do-v3-backdrop" aria-hidden="true">
      <img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high">
      <div class="what-we-do-v3-gradient"></div>
    </div>
    <div class="what-we-do-v3-ring" style="--ring-rotate:<?php echo esc_attr((string) $ring_rotation); ?>deg" aria-hidden="true">
      <svg viewBox="0 0 900 900" focusable="false"><circle cx="450" cy="450" r="405" fill="none" stroke="currentColor" stroke-width="90" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg>
    </div>
    <div class="what-we-do-v3-hero-content">
      <div class="what-we-do-v3-hero-copy">
        <p class="what-we-do-v3-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><?php echo esc_html($hero_eyebrow); ?></p>
        <p class="what-we-do-v3-eyebrow"><?php echo esc_html($hero_eyebrow); ?></p>
        <h1 id="what-we-do-title"><?php if (count($title_parts) === 2) : ?><?php echo esc_html($title_parts[0]); ?><em><?php echo esc_html($hero_emphasis); ?></em><?php echo esc_html($title_parts[1]); ?><?php else : ?><?php echo esc_html($hero_title); ?><?php endif; ?></h1>
        <p class="what-we-do-v3-intro"><?php echo esc_html($hero_intro); ?></p>
      </div>
    </div>
  </section>

  <section class="pillar-section pillar-section--page what-we-do-v3-pillars" aria-label="Three pillars of enterprise growth" data-pillar-section>
    <div class="pillar-stage" data-pillar-stage>
      <div class="pillar-accordion-desktop">
        <?php foreach ($pillars as $index => $pillar) : $active = $index === 0; ?>
          <div class="pillar-panel pillar-panel--<?php echo esc_attr($pillar['key']); ?><?php echo $active ? ' is-active' : ''; ?>" style="--pillar-gradient:<?php echo esc_attr($pillar['gradient']); ?>;--pillar-color:<?php echo esc_attr($pillar['color']); ?>" data-pillar-panel="<?php echo esc_attr($pillar['key']); ?>" role="button" tabindex="0" aria-expanded="<?php echo $active ? 'true' : 'false'; ?>">
            <div class="pillar-panel-collapsed"><span><?php echo esc_html((string) ((int) $pillar['number'])); ?></span><strong><?php echo esc_html($pillar['title']); ?></strong></div>
            <div class="pillar-panel-expanded">
              <div><span class="pillar-number"><?php echo esc_html((string) ((int) $pillar['number'])); ?></span><h3><?php echo esc_html($pillar['title']); ?></h3><em>“<?php echo esc_html($pillar['tagline']); ?>”</em><p><?php echo esc_html($pillar['description']); ?></p><div class="pillar-audience"><span>Who it's for</span><strong><?php echo esc_html($pillar['audience']); ?></strong></div></div>
              <button type="button" class="pillar-learn" data-pillar-open="<?php echo esc_attr($pillar['key']); ?>">Explore programmes <span aria-hidden="true">→</span></button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="pillar-accordion-mobile">
        <?php foreach ($pillars as $index => $pillar) : $active = $index === 0; ?>
          <article class="pillar-mobile-card<?php echo $active ? ' is-active' : ''; ?>" style="--pillar-gradient:<?php echo esc_attr($pillar['gradient']); ?>;--pillar-color:<?php echo esc_attr($pillar['color']); ?>" data-pillar-mobile-card="<?php echo esc_attr($pillar['key']); ?>" data-tagline="<?php echo esc_attr($pillar['tagline']); ?>" data-description="<?php echo esc_attr($pillar['description']); ?>" data-audience="<?php echo esc_attr($pillar['audience']); ?>">
            <button type="button" data-pillar-mobile-trigger="<?php echo esc_attr($pillar['key']); ?>" aria-expanded="<?php echo $active ? 'true' : 'false'; ?>"><span><?php echo esc_html((string) ((int) $pillar['number'])); ?></span><strong><?php echo esc_html($pillar['title']); ?></strong><i aria-hidden="true">+</i></button>
            <?php if ($active) : ?><div><em>“<?php echo esc_html($pillar['tagline']); ?>”</em><p><?php echo esc_html($pillar['description']); ?></p><div class="pillar-audience"><span>Who it's for</span><strong><?php echo esc_html($pillar['audience']); ?></strong></div><button type="button" class="pillar-learn" data-pillar-open="<?php echo esc_attr($pillar['key']); ?>">Explore programmes <span aria-hidden="true">→</span></button></div><?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>

      <?php foreach ($pillars as $pillar) : $programmes = $pillar_programmes[$pillar['key']] ?? []; ?>
        <div class="pillar-cabinet" style="--pillar-color:<?php echo esc_attr($pillar['color']); ?>;--pillar-gradient:<?php echo esc_attr($pillar['gradient']); ?>" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr($pillar['title'] . ' programme cabinet'); ?>" data-pillar-cabinet="<?php echo esc_attr($pillar['key']); ?>" hidden>
          <div class="pillar-title-strip pillar-title-strip--<?php echo esc_attr($pillar['key']); ?>"><button type="button" aria-label="Close programme cabinet" data-pillar-close>×</button><span><?php echo esc_html((string) ((int) $pillar['number'])); ?></span><strong><?php echo esc_html($pillar['title']); ?></strong></div>
          <div class="pillar-tab-strip" aria-label="<?php echo esc_attr($pillar['title'] . ' programmes'); ?>">
            <button type="button" class="is-active" aria-pressed="true" data-pillar-tab="overview"><span class="pillar-tab-dot pillar-tab-dot--overview" aria-hidden="true"></span><strong>Overview</strong></button>
            <?php foreach ($programmes as $programme) : ?><button type="button" aria-pressed="false" data-pillar-tab="<?php echo esc_attr($programme->post_name); ?>"><span class="pillar-tab-dot pillar-tab-dot--<?php echo esc_attr(get_post_meta($programme->ID, '_programme_status', true) ?: 'past'); ?>" aria-hidden="true"></span><strong><?php echo esc_html($programme_short($programme)); ?></strong></button><?php endforeach; ?>
          </div>
          <div class="pillar-folder-edges" aria-hidden="true"><span></span><span></span><span></span></div>
          <div class="pillar-paper">
            <div class="pillar-sheet-content" data-pillar-content="overview">
              <div class="pillar-sheet-summary">
                <div class="pillar-sheet-eyebrow"><?php echo esc_html((string) ((int) $pillar['number'])); ?></div><h3><?php echo esc_html($pillar['title']); ?></h3><p class="pillar-sheet-tagline">“<?php echo esc_html($pillar['tagline']); ?>”</p><p class="pillar-sheet-description"><?php echo esc_html($pillar['description']); ?></p>
                <div class="pillar-sheet-audience"><span>Who it's for</span><strong><?php echo esc_html($pillar['audience']); ?></strong></div>
                <div class="pillar-overview-programmes"><div class="pillar-sheet-eyebrow">Programmes in this pillar</div><div>
                  <?php foreach ($programmes as $programme) : ?><button type="button" data-pillar-select-programme="<?php echo esc_attr($programme->post_name); ?>"><span><?php echo esc_html($status_label($programme)); ?></span><strong><?php echo esc_html($programme_short($programme)); ?></strong><i aria-hidden="true">→</i></button><?php endforeach; ?>
                </div></div>
              </div>
            </div>

            <?php foreach ($programmes as $programme) :
                $programme_id = $programme->ID;
                $status = get_post_meta($programme_id, '_programme_status', true) ?: 'past';
                $enrolment = get_post_meta($programme_id, '_programme_enrolment', true);
                $theme_terms = wp_get_post_terms($programme_id, 'programme_theme', ['fields' => 'names']);
                $theme = !is_wp_error($theme_terms) && $theme_terms ? $theme_terms[0] : '';
                $logos = get_post_meta($programme_id, '_programme_logos', true);
                $funders = get_post_meta($programme_id, '_programme_funders', true);
                if (!is_array($logos)) $logos = [];
                if (!$logos && is_array($funders) && $funders) $logos = [$funders[0]];
                $audience = get_post_meta($programme_id, '_programme_audience', true) ?: get_the_excerpt($programme_id);
                $duration = get_post_meta($programme_id, '_programme_duration', true) ?: '—';
                $delivery = get_post_meta($programme_id, '_programme_delivery', true) ?: '—';
                $journey = get_post_meta($programme_id, '_programme_journey', true);
                if (!is_array($journey)) $journey = preg_split('/\r\n|\r|\n/', (string) $journey);
                $journey = array_values(array_filter(array_map('trim', $journey)));
                $available = get_post_meta($programme_id, '_programme_detail_available', true) !== '0';
                $programme_url = $available ? get_permalink($programme_id) : add_query_arg('status', $status === 'upcoming' ? 'upcoming' : 'past', $programme_archive);
                ?>
              <div class="pillar-sheet-content" data-pillar-content="<?php echo esc_attr($programme->post_name); ?>" hidden>
                <div class="pillar-programme-sheet">
                  <?php if ($theme !== '') : ?><div class="pillar-sheet-eyebrow"><?php echo esc_html($theme); ?></div><?php endif; ?>
                  <h3><?php echo esc_html($programme_title($programme)); ?></h3>
                  <div class="pillar-programme-topline"><div class="pillar-sheet-status"><?php echo esc_html($status_label($programme)); ?></div><div class="pillar-sheet-logos"><div>
                    <?php foreach ($logos as $logo) : $logo = (string) $logo; $logo_slug = sanitize_title($logo); $asset = $logo_assets[$logo_slug] ?? ''; $label = $logo_labels[$logo_slug] ?? $logo; ?><div><?php if ($asset) : ?><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/' . $asset); ?>" alt="<?php echo esc_attr($label); ?>" loading="lazy"><?php else : ?><span><?php echo esc_html($label); ?></span><?php endif; ?></div><?php endforeach; ?>
                  </div></div></div>
                  <div class="pillar-sheet-facts"><div><span>Audience</span><strong><?php echo esc_html($audience); ?></strong></div><div><span>Duration</span><strong><?php echo esc_html($duration); ?></strong></div><div><span>Delivery</span><strong><?php echo esc_html($delivery); ?></strong></div></div>
                  <div class="pillar-sheet-journey"><div class="pillar-sheet-eyebrow">Learning journey</div><?php if ($journey) : ?><ol><?php foreach ($journey as $index => $step) : ?><li><span><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span><strong><?php echo esc_html($step); ?></strong></li><?php endforeach; ?></ol><?php else : ?><p>Journey details coming soon.</p><?php endif; ?></div>
                  <div class="pillar-sheet-actions">
                    <?php if ($status === 'past') : ?><a href="<?php echo esc_url($programme_url); ?>">View programme</a><a href="<?php echo esc_url(add_query_arg('status', 'current', $programme_archive)); ?>">Explore current programmes</a>
                    <?php elseif ($status === 'upcoming') : ?><a href="<?php echo esc_url(add_query_arg('status', 'upcoming', $programme_archive)); ?>">Get notified</a><a href="<?php echo esc_url($programme_url); ?>">View programme</a>
                    <?php elseif ($enrolment === 'open') : ?><a href="<?php echo esc_url($programme_url); ?>">Register</a><a href="<?php echo esc_url($programme_url); ?>">View programme</a>
                    <?php else : ?><a href="<?php echo esc_url($programme_url); ?>">View programme</a><a href="<?php echo esc_url($get_involved_url); ?>">Partner with us</a><?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

            <div class="pillar-sheet-footer"><a href="<?php echo esc_url($programme_archive); ?>">See all programmes</a><span aria-hidden="true">·</span><a href="<?php echo esc_url($get_involved_url); ?>">Partner with us</a><span aria-hidden="true">·</span><a href="<?php echo esc_url($get_involved_url); ?>">Get involved</a></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="what-we-do-delivery">
    <div class="what-we-do-delivery-inner">
      <div class="what-we-do-delivery-heading"><p><?php echo esc_html(src_page_field('page_service_delivery_eyebrow', $page_id, 'How we deliver')); ?></p><h2><?php echo esc_html(src_page_field('page_service_delivery_heading', $page_id, 'Practical support, close to the enterprise.')); ?></h2></div>
      <div class="what-we-do-delivery-methods"><?php foreach (preg_split('/\r\n|\r|\n/', (string) src_page_field('page_service_delivery_methods', $page_id, '')) as $method) : if (trim($method) === '') continue; ?><span><?php echo esc_html(trim($method)); ?></span><?php endforeach; ?></div>
    </div>
  </section>

  <section class="what-we-do-partner-cta">
    <div class="what-we-do-partner-inner"><div><p><?php echo esc_html(src_page_field('page_service_cta_eyebrow', $page_id, 'Partner with us')); ?></p><h2><?php echo esc_html(src_page_field('page_service_cta_heading', $page_id, 'Build the next generation of resilient, investable enterprises.')); ?></h2></div><a href="<?php echo esc_url($get_involved_url); ?>"><?php echo esc_html(src_page_field('page_service_cta_button', $page_id, 'Start a partnership →')); ?></a></div>
  </section>
</main>

<?php get_footer(); ?>
