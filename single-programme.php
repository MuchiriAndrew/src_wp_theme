<?php
if (get_post_meta(get_the_ID(), '_src_v3_imported', true) !== '1') {
    include get_template_directory() . '/single.php';
    return;
}

get_header();
the_post();
$id = get_the_ID();
$slug = get_post_field('post_name', $id);
$title = (string) get_post_meta($id, '_programme_hero_title', true);
$title = $title !== '' ? $title : get_the_title($id);
$name = get_the_title($id);
$status = (string) get_post_meta($id, '_programme_status', true);
$status = in_array($status, ['current', 'upcoming', 'past'], true) ? $status : 'current';
$enrolment = (string) get_post_meta($id, '_programme_enrolment', true);
$hero_subtitle = (string) get_post_meta($id, '_programme_hero_subtitle', true);
$audience = (string) get_post_meta($id, '_programme_audience', true);
$outcomes = (string) get_post_meta($id, '_programme_outcomes', true);
$duration = (string) get_post_meta($id, '_programme_duration', true);
$dates = (string) get_post_meta($id, '_programme_dates', true);
$delivery = (string) get_post_meta($id, '_programme_delivery', true);
$reach = (string) get_post_meta($id, '_programme_reach', true);
$fee = (string) get_post_meta($id, '_programme_fee', true);
$journey = get_post_meta($id, '_programme_journey', true);
$journey = is_array($journey) ? $journey : [];
$funders = get_post_meta($id, '_programme_funders', true);
$funders = is_array($funders) ? array_values(array_filter($funders)) : [];
$partners = get_post_meta($id, '_programme_partners', true);
$partners = is_array($partners) ? array_values(array_filter($partners)) : [];
$logo_keys = get_post_meta($id, '_programme_logos', true);
$logo_keys = is_array($logo_keys) ? array_values(array_filter($logo_keys)) : [];
$tags = get_post_meta($id, '_programme_tags', true);
$tags = is_array($tags) ? array_values(array_filter($tags)) : [];
$themes = wp_get_post_terms($id, 'programme_theme', ['fields' => 'names']);
$theme = !is_wp_error($themes) && $themes ? (string) $themes[0] : 'SRC Programmes';
$theme_terms = wp_get_post_terms($id, 'programme_theme');
$theme_ids = !is_wp_error($theme_terms) ? wp_list_pluck($theme_terms, 'term_id') : [];
$county_terms = wp_get_post_terms($id, 'programme_county');
$county_names = !is_wp_error($county_terms) ? wp_list_pluck($county_terms, 'name') : [];
$county_slugs = !is_wp_error($county_terms) ? wp_list_pluck($county_terms, 'slug') : [];
$has_counties = !empty($county_names);
$county_svg_path = get_template_directory() . '/assets/kenya-counties.svg';
$county_svg = is_readable($county_svg_path) ? (string) file_get_contents($county_svg_path) : '';
if ($county_svg !== '') {
    $county_svg = preg_replace_callback('/<path\b[^>]*>/', static function ($match) use ($county_slugs) {
        if (!preg_match('/id="county-([^"]+)"/', $match[0], $path_id)) return $match[0];
        $classes = 'county';
        if (!$county_slugs) $classes .= ' is-all-selected';
        elseif (in_array($path_id[1], $county_slugs, true)) $classes .= ' is-selected';
        return preg_replace('/class="county"/', 'class="' . $classes . '"', $match[0], 1);
    }, $county_svg);
    $county_svg = preg_replace('/<svg\b/', '<svg class="src-v3-county-svg" focusable="false"', $county_svg, 1);
}

$image_id = get_post_thumbnail_id($id);
$image_key = (string) get_post_meta($id, '_programme_image_id', true);
$image = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
if (!$image && $image_key !== '') $image = 'https://images.unsplash.com/' . rawurlencode($image_key) . '?auto=format&fit=crop&w=2000&q=85';
if (!$image) $image = 'https://images.unsplash.com/photo-1637149014802-d917e6255c2d?auto=format&fit=crop&w=2000&q=85';

$status_label = $status === 'past' ? 'Past programme' : ($status === 'upcoming' ? 'Upcoming' : ($enrolment === 'open' ? 'Enrolling now' : 'Running'));
$status_tone = $status === 'past' ? 'red' : ($status === 'upcoming' ? 'amber' : 'green');
$cta_label = $enrolment === 'open' ? 'Register' : ($enrolment === 'partner' ? 'Partner with us' : ($status === 'upcoming' ? 'Get notified' : 'Explore current programmes'));
$archive_url = get_post_type_archive_link('programme') ?: home_url('/programmes/');
$cta_url = $status === 'past' ? add_query_arg('status', 'current', $archive_url) : ($status === 'upcoming' ? add_query_arg('status', 'upcoming', $archive_url) : ($enrolment === 'partner' ? src_page_url('get-involved') : add_query_arg('status', 'current', $archive_url)));

$external_partners = array_merge($funders, array_values(array_filter($partners, static fn($partner) => stripos($partner, 'SRC') !== 0)));
$external_logos = array_values(array_filter($logo_keys, static fn($logo) => sanitize_title((string) $logo) !== 'src'));
$signature_only = get_post_meta($id, '_programme_type', true) === 'signature' && !$external_partners;
$dates_unknown = stripos($dates, 'TBC') !== false || stripos($dates, 'coming soon') !== false;
$cohort_value = $dates_unknown ? ($status === 'upcoming' ? 'Returning soon' : '') : $dates;
$time_commitment = stripos($duration, 'about ') !== false ? trim(implode(',', array_slice(explode(',', $duration), 1))) : '';
$delivery_format = stripos($reach, 'Hybrid') !== false ? 'Hybrid' : (stripos($reach, 'Mobile + field') !== false ? 'Mobile + field' : (stripos($reach, 'Virtual') !== false ? 'Virtual' : trim(explode(':', $delivery)[0])));
$delivery_heading = $reach === 'Virtual' ? 'Join from anywhere online' : (stripos($reach, 'Mobile + field') !== false ? 'Open across Kenya, mobile + field' : (stripos($reach, 'Kenya-wide') !== false ? 'Open to participants across Kenya' : ((stripos($reach, 'Kenya') !== false && stripos($reach, 'Virtual') !== false) ? 'Available across Kenya, online' : $reach)));
$delivery_facts = [
    ['label' => 'Format', 'value' => $delivery_format, 'icon' => 'format'],
    ['label' => 'Duration', 'value' => $time_commitment ? trim(explode(',', $duration)[0]) : $duration, 'icon' => 'duration'],
];
if ($cohort_value !== '') $delivery_facts[] = ['label' => 'Cohorts', 'value' => $cohort_value, 'icon' => 'cohorts'];
if ($time_commitment !== '') $delivery_facts[] = ['label' => 'Time commitment', 'value' => $time_commitment, 'icon' => 'duration'];
elseif ($fee !== '' && stripos($fee, 'TBC') === false) $delivery_facts[] = ['label' => 'Fee', 'value' => $fee, 'icon' => 'fee'];

$page_name = 'Programmes / ' . $title;
$ring_hash = 0;
foreach (str_split(strtolower($page_name)) as $character) $ring_hash = (($ring_hash * 31) + ord($character)) & 0xFFFFFFFF;
$ring_rotation = [0, 35, 70, 110, 150][$ring_hash % 5];

$logo_assets = [
    'src' => 'src-logo.png', 'herventure' => 'partner-logos/her-venture-transparent.svg',
    'boma-project' => 'partner-logos/boma-project.png', 'cherie-blair-foundation' => 'partner-logos/cherie-blair-foundation.png',
    'dhl' => 'partner-logos/dhl.png', 'usaid' => 'partner-logos/usaid-programme.png', 'village-capital' => 'partner-logos/village-capital.png',
    'giz' => 'partner-logos/giz.png', 'goal' => 'partner-logos/goal.png', 'united-nations' => 'partner-logos/united-nations.png',
    'world-bank' => 'partner-logos/world-bank.png', 'wwf' => 'partner-logos/wwf.png', 'kcdf' => 'partner-logos/kcdf.png',
];
$logo_labels = [
    'ande' => 'ANDE', 'boma-project' => 'BOMA Project', 'afrishela' => 'Afrishela', 'graca-machel-trust' => 'Graça Machel Trust',
    'sheconnects' => 'SheConnects', 'care' => 'CARE', 'mastercard-foundation' => 'Mastercard Foundation', 'herventure' => 'HerVenture',
    'cherie-blair-foundation' => 'Cherie Blair Foundation', 'dhl' => 'DHL', 'src' => 'SRC', 'kcb-foundation' => 'KCB Foundation',
    'usaid' => 'USAID', 'village-capital' => 'Village Capital', 'giz' => 'GIZ', 'goal' => 'GOAL', 'united-nations' => 'United Nations',
    'world-bank' => 'World Bank', 'wwf' => 'WWF', 'kcdf' => 'KCDF',
];
$logo_items = $external_logos ?: ($funders ? [sanitize_title($funders[0])] : []);

$journey_heading = $journey ? 'How the programme unfolds' : 'How it is delivered';
$journey_eyebrow = $journey ? 'Learning Journey' : 'Delivery Format';
$related = $theme_ids ? get_posts([
    'post_type' => 'programme', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => [$id],
    'orderby' => 'menu_order', 'order' => 'ASC', 'meta_key' => '_src_v3_imported', 'meta_value' => '1',
    'tax_query' => [['taxonomy' => 'programme_theme', 'field' => 'term_id', 'terms' => $theme_ids]],
]) : [];
?>
<main class="src-v3-programme-detail">
  <section class="page-hero page-hero--inner src-v3-programmes-hero src-v3-programme-detail-hero" aria-labelledby="src-programme-title">
    <img class="src-v3-programmes-backdrop" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" fetchpriority="high">
    <div class="src-v3-programmes-ring src-experts-v3-ring" style="--ring-rotate:<?php echo esc_attr((string) $ring_rotation); ?>deg" aria-hidden="true"><svg viewBox="0 0 900 900" focusable="false"><circle cx="450" cy="450" r="405" fill="none" stroke="currentColor" stroke-width="90" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg></div>
    <div class="src-v3-programmes-hero-content">
      <div class="src-v3-programmes-hero-copy">
        <p class="src-v3-programmes-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><a href="<?php echo esc_url($archive_url); ?>">Programmes</a><span>/</span><?php echo esc_html($title); ?></p>
        <div class="src-v3-detail-eyebrow"><span class="src-v3-detail-status src-v3-detail-status--<?php echo esc_attr($status_tone); ?>">● <?php echo esc_html($status_label); ?></span><span class="src-v3-detail-theme"><?php echo esc_html($theme); ?></span></div>
        <h1 id="src-programme-title"><?php echo esc_html($title); ?></h1>
        <?php if ($hero_subtitle !== '') : ?><p class="page-hero-subtitle"><?php echo esc_html($hero_subtitle); ?></p><?php endif; ?>
        <?php if ($audience !== '') : ?><p class="page-hero-intro"><?php echo esc_html($audience); ?></p><?php endif; ?>
        <div class="src-v3-detail-actions">
          <?php if ($status === 'current' && $enrolment === 'open') : ?>
            <button class="src-v3-detail-primary" type="button" data-src-open-registration="<?php echo esc_attr($slug); ?>" aria-controls="src-registration-dialog"><?php echo esc_html($cta_label); ?></button>
          <?php else : ?>
            <a class="src-v3-detail-primary" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
          <?php endif; ?>
          <a class="src-v3-detail-secondary" href="<?php echo esc_url($archive_url); ?>"><span aria-hidden="true">→</span> All programmes</a>
        </div>
      </div>
    </div>
  </section>

  <section class="src-v3-programme-summary">
    <div class="src-v3-programme-shell">
      <header class="src-v3-programme-section-heading">
        <p class="eyebrow">Programme Summary</p>
        <h2>At a glance</h2>
        <?php if ($name !== $title) : ?><p><?php echo esc_html($name); ?></p><?php endif; ?>
      </header>
      <div class="src-v3-programme-summary-grid">
        <?php foreach ([['Target Audience', $audience], ['Key Outcomes', $outcomes], ['Duration', trim($duration . ($dates !== '' ? ' · ' . $dates : ''))], ['Delivery Method', $delivery]] as [$label, $value]) : ?>
          <article><p><?php echo esc_html($label); ?></p><div><?php echo esc_html($value); ?></div></article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="src-v3-programme-journey">
    <div class="src-v3-programme-shell">
      <header class="src-v3-programme-section-heading src-v3-programme-section-heading--compact">
        <p class="eyebrow"><?php echo esc_html($journey_eyebrow); ?></p>
        <h2><?php echo esc_html($journey_heading); ?></h2>
      </header>
      <?php if ($journey) : ?>
        <ol class="src-v3-programme-steps">
          <?php foreach ($journey as $index => $step) : ?><li><span><?php echo esc_html((string) ($index + 1)); ?></span><p><?php echo esc_html($step); ?></p></li><?php endforeach; ?>
        </ol>
      <?php else : ?>
        <div class="src-v3-programme-delivery-copy"><?php echo esc_html($delivery); ?></div>
      <?php endif; ?>
    </div>
  </section>

  <section class="src-v3-programme-reach">
    <div class="src-v3-programme-shell src-v3-programme-reach-grid">
      <div class="src-v3-programme-reach-copy">
        <p class="eyebrow"><?php echo esc_html($has_counties ? 'Where' : "How it's delivered"); ?></p>
        <h2><?php echo esc_html($has_counties ? count($county_names) . ' counties' : $delivery_heading); ?></h2>
        <?php if ($signature_only) : ?><span class="src-v3-programme-signature">An SRC Signature Programme</span><?php endif; ?>
        <?php if ($has_counties) : ?>
          <div class="src-v3-programme-county-tags"><?php foreach ($county_names as $county_name) : ?><span><?php echo esc_html($county_name); ?></span><?php endforeach; ?></div>
        <?php else : ?>
          <div class="src-v3-programme-facts">
            <?php foreach ($delivery_facts as $fact) : ?>
              <article><span aria-hidden="true" class="src-v3-programme-fact-icon">
                <?php if ($fact['icon'] === 'duration') : ?><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="7"></circle><path d="M10 6v4l3 2"></path></svg>
                <?php elseif ($fact['icon'] === 'cohorts') : ?><svg viewBox="0 0 20 20"><rect x="3" y="5" width="14" height="12"></rect><path d="M6 3v4M14 3v4M3 9h14"></path></svg>
                <?php elseif ($fact['icon'] === 'fee') : ?><svg viewBox="0 0 20 20"><path d="M3 6h14v10H3zM6 3h8M6 11h8"></path></svg>
                <?php else : ?><svg viewBox="0 0 20 20"><path d="M3 5h14M3 10h14M3 15h14"></path></svg><?php endif; ?>
              </span><div><p><?php echo esc_html($fact['label']); ?></p><strong><?php echo esc_html($fact['value']); ?></strong></div></article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if ($external_partners) : ?>
          <div class="src-v3-programme-partners"><p class="eyebrow">Partners</p><div class="src-v3-programme-logo-row">
            <?php foreach ($logo_items as $logo_item) :
                $logo_slug = sanitize_title((string) $logo_item);
                $logo_label = $logo_labels[$logo_slug] ?? (string) $logo_item;
                $logo_asset = $logo_assets[$logo_slug] ?? '';
                ?>
              <span title="<?php echo esc_attr($logo_label); ?>"><?php if ($logo_asset) : ?><img src="<?php echo esc_url(get_theme_file_uri('/assets/' . $logo_asset)); ?>" alt="<?php echo esc_attr($logo_label); ?>" loading="lazy" decoding="async"><?php else : ?><?php echo esc_html($logo_label); ?><?php endif; ?></span>
            <?php endforeach; ?>
          </div></div>
        <?php endif; ?>
      </div>
      <div class="src-v3-programme-map-card">
        <?php if ($county_svg) : ?><div class="src-v3-programme-map" role="img" aria-label="<?php echo esc_attr($has_counties ? 'Map of Kenya highlighting programme counties' : 'Map of Kenya showing nationwide programme access'); ?>"><?php echo $county_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
        <?php if (!$has_counties) : ?><p><?php echo esc_html('Open to participants in all 47 counties' . (stripos($reach, 'Virtual') !== false ? ' · Online' : '')); ?></p><?php endif; ?>
      </div>
    </div>
  </section>

  <section class="src-v3-programme-next-step">
    <div class="src-v3-programme-shell">
      <div><p>Take the next step</p><h2><?php echo esc_html($cta_label); ?></h2></div>
      <?php if ($status === 'current' && $enrolment === 'open') : ?>
        <button type="button" data-src-open-registration="<?php echo esc_attr($slug); ?>" aria-controls="src-registration-dialog"><?php echo esc_html($cta_label); ?> <span aria-hidden="true">→</span></button>
      <?php else : ?>
        <a href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?> <span aria-hidden="true">→</span></a>
      <?php endif; ?>
    </div>
  </section>

  <?php if ($related) : ?>
    <section class="src-v3-programme-related">
      <div class="src-v3-programme-shell">
        <header class="src-v3-programme-section-heading"><p class="eyebrow">Related Programmes</p><h2>More in <?php echo esc_html($theme); ?></h2></header>
        <div class="src-v3-programme-related-grid"><?php foreach ($related as $related_programme) get_template_part('template-parts/programme-card', null, ['programme' => $related_programme]); ?></div>
      </div>
    </section>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
