<?php
get_header();

$page_id = get_queried_object_id();
$hero_title = (string) src_page_field('page_hero_title', $page_id, 'Pool of Experts');
$hero_eyebrow = (string) src_page_field('page_hero_eyebrow', $page_id, 'Pool of Experts');
$hero_intro = (string) src_page_field('page_hero_intro', $page_id, "SRC's Pay-As-You-Go consultant network gives enterprises and programmes access to specialist knowledge — on demand, without overhead.");
$hero_image = (string) src_page_field('page_hero_image_url', $page_id, 'https://images.unsplash.com/photo-1637149014802-d917e6255c2d?auto=format&fit=crop&w=2000&q=85');
$all_label = (string) src_page_field('page_experts_all_label', $page_id, 'All Categories');
$cta_url = (string) src_page_field('page_experts_cta_url', $page_id, '');
$cta_url = $cta_url !== '' ? $cta_url : src_page_url('get-involved');
$pagehero_hash = 0;
foreach (str_split(strtolower($hero_eyebrow)) as $character) $pagehero_hash = (($pagehero_hash * 31) + ord($character)) & 0xFFFFFFFF;
$ring_rotation = [0, 35, 70, 110, 150][$pagehero_hash % 5];

$expert_categories = get_terms(['taxonomy' => 'expert_category', 'hide_empty' => false]);
if (is_wp_error($expert_categories)) $expert_categories = [];
usort($expert_categories, static function ($a, $b) {
    $a_order = (int) get_term_meta($a->term_id, '_expert_category_order', true);
    $b_order = (int) get_term_meta($b->term_id, '_expert_category_order', true);
    return ($a_order <=> $b_order) ?: strcasecmp($a->name, $b->name);
});

$experts_by_category = [];
foreach ($expert_categories as $category) {
    $experts = get_posts([
        'post_type' => 'src_expert',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'tax_query' => [[
            'taxonomy' => 'expert_category',
            'field' => 'term_id',
            'terms' => $category->term_id,
        ]],
    ]);
    usort($experts, static function ($a, $b) {
        $a_order = (int) get_post_meta($a->ID, '_expert_order', true);
        $b_order = (int) get_post_meta($b->ID, '_expert_order', true);
        return ($a_order <=> $b_order) ?: strcasecmp($a->post_title, $b->post_title);
    });
    $experts_by_category[$category->term_id] = $experts;
}
?>
<main class="src-experts-v3">
  <section class="page-hero page-hero--inner src-v3-programmes-hero src-experts-v3-hero" aria-labelledby="src-experts-title">
    <img class="src-v3-programmes-backdrop" src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high">
    <div class="src-v3-programmes-ring src-experts-v3-ring" style="--ring-rotate:<?php echo esc_attr((string) $ring_rotation); ?>deg" aria-hidden="true"><svg viewBox="0 0 900 900" focusable="false"><circle cx="450" cy="450" r="405" fill="none" stroke="currentColor" stroke-width="90" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg></div>
    <div class="src-v3-programmes-hero-content">
      <div class="src-v3-programmes-hero-copy">
        <p class="src-v3-programmes-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><?php echo esc_html($hero_eyebrow); ?></p>
        <p class="eyebrow"><?php echo esc_html($hero_eyebrow); ?></p>
        <h1 id="src-experts-title"><?php echo esc_html($hero_title); ?></h1>
        <p class="lede"><?php echo esc_html($hero_intro); ?></p>
      </div>
    </div>
  </section>

  <nav class="src-experts-v3-filters" aria-label="Expert category filters" data-expert-filters>
    <div class="src-experts-v3-filter-scroll">
      <div class="src-experts-v3-filter-row" role="group" aria-label="Filter expertise categories">
        <button type="button" class="src-experts-v3-filter is-active" data-expert-filter="all" aria-pressed="true"><?php echo esc_html($all_label); ?></button>
        <?php foreach ($expert_categories as $category) : ?>
          <button type="button" class="src-experts-v3-filter" data-expert-filter="<?php echo esc_attr($category->slug); ?>" aria-pressed="false"><?php echo esc_html($category->name); ?></button>
        <?php endforeach; ?>
      </div>
    </div>
  </nav>

  <section class="src-experts-v3-list" aria-label="Specialist expertise">
    <div class="src-experts-v3-shell">
      <?php foreach ($expert_categories as $category) :
          $category_experts = $experts_by_category[$category->term_id] ?? [];
          $category_slug = (string) $category->slug;
          $accent = match ($category_slug) {
              'technical-value-chain' => 'red',
              'financial-governance' => 'orange',
              'marketing-growth' => 'blue',
              'compliance-safety' => 'green',
              default => 'red',
          };
          ?>
        <section class="src-experts-v3-category src-experts-v3-category--<?php echo esc_attr($accent); ?>" data-expert-category="<?php echo esc_attr($category_slug); ?>">
          <header class="src-experts-v3-category-heading">
            <span class="src-experts-v3-category-dot" aria-hidden="true"></span>
            <h2><?php echo esc_html($category->name); ?></h2>
            <span class="src-experts-v3-category-rule" aria-hidden="true"></span>
            <span class="src-experts-v3-count"><?php echo esc_html((string) count($category_experts)); ?> specialists</span>
          </header>
          <?php if ($category_experts) : ?>
            <div class="src-experts-v3-accordion">
              <?php foreach ($category_experts as $expert) :
                  $summary = (string) get_post_meta($expert->ID, '_expert_summary', true);
                  if ($summary === '') $summary = $expert->post_content;
                  $request_label = (string) get_post_meta($expert->ID, '_expert_request_label', true);
                  $request_url = (string) get_post_meta($expert->ID, '_expert_request_url', true);
                  if ($request_label === '') $request_label = 'Request this expert →';
                  if ($request_url === '') $request_url = $cta_url;
                  ?>
                <details class="src-experts-v3-item">
                  <summary>
                    <span class="src-experts-v3-item-category"><?php echo esc_html(strtoupper(strtok($category->name, ' /&'))); ?></span>
                    <span class="src-experts-v3-item-title"><?php echo esc_html(get_the_title($expert)); ?></span>
                    <span class="src-experts-v3-plus" aria-hidden="true">+</span>
                  </summary>
                  <div class="src-experts-v3-item-body">
                    <p><?php echo esc_html(wp_strip_all_tags($summary)); ?></p>
                    <a href="<?php echo esc_url($request_url); ?>"><?php echo esc_html($request_label); ?></a>
                  </div>
                </details>
              <?php endforeach; ?>
            </div>
          <?php else : ?>
            <p class="src-experts-v3-empty">Specialist profiles will be added here soon.</p>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="src-experts-v3-cta">
    <p><?php echo esc_html(src_page_field('page_experts_cta_eyebrow', $page_id, 'Flexible expertise')); ?></p>
    <h2><?php echo esc_html(src_page_field('page_experts_cta_heading', $page_id, 'Need a specialist for your programme?')); ?></h2>
    <div class="src-experts-v3-cta-copy"><?php echo esc_html(src_page_field('page_experts_cta_copy', $page_id, 'Our Pay-As-You-Go model means you get the exact expertise you need, for exactly as long as you need it.')); ?></div>
    <a href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html(src_page_field('page_experts_cta_button', $page_id, 'Engage Our Experts')); ?></a>
  </section>
</main>
<?php get_footer(); ?>
