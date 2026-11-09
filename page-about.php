<?php
get_header();

$hero_title = (string) src_page_field('page_hero_title', null, "Built in Kenya, for Africa's enterprises.");
$hero_emphasis = (string) src_page_field('page_hero_emphasis', null, 'for');
$hero_eyebrow = (string) src_page_field('page_hero_eyebrow', null, 'About SRC');
$hero_intro = (string) src_page_field('page_hero_intro', null, 'For over a decade, SRC has helped SMEs and cooperatives grow from good to great through strategy, finance and markets.');
$hero_image = (string) src_page_field('page_hero_image_url', null, 'https://images.unsplash.com/photo-1637149014802-d917e6255c2d?auto=format&fit=crop&w=2000&q=85');
$story_body = (string) src_page_field('page_story_body', null, '');
$story_paragraphs = preg_split('/\R\s*\R/', $story_body);
$pillars = src_parse_pairs(src_page_field('page_pillars'));
$values = src_parse_pairs(src_page_field('page_values'));
$theory_steps = src_parse_rows(src_page_field('page_theory_steps'));
$about_stats = src_parse_pairs(src_page_field('page_about_stats'));
$hero_title_parts = $hero_emphasis !== '' ? explode($hero_emphasis, $hero_title, 2) : [$hero_title];
?>

<main class="about-v3">
  <section class="page-hero page-hero--inner about-v3-hero" aria-labelledby="about-page-title">
    <div class="about-v3-hero-backdrop" aria-hidden="true">
      <img src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high">
      <div class="about-v3-hero-gradient"></div>
    </div>
    <div class="about-v3-hero-ring" aria-hidden="true"></div>
    <div class="about-v3-hero-content">
      <div class="about-v3-hero-copy">
        <p class="about-v3-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><?php echo esc_html($hero_eyebrow); ?></p>
        <p class="about-v3-eyebrow"><?php echo esc_html($hero_eyebrow); ?></p>
        <h1 id="about-page-title">
          <?php if (count($hero_title_parts) === 2) : ?><?php echo esc_html($hero_title_parts[0]); ?><em><?php echo esc_html($hero_emphasis); ?></em><?php echo esc_html($hero_title_parts[1]); ?><?php else : ?><?php echo esc_html($hero_title); ?><?php endif; ?>
        </h1>
        <p class="about-v3-hero-intro"><?php echo esc_html($hero_intro); ?></p>
      </div>
    </div>
  </section>

  <section class="about-v3-story">
    <div class="about-v3-shell about-v3-story-grid">
      <div class="about-v3-story-copy">
        <p class="about-v3-section-eyebrow"><?php echo esc_html(src_page_field('page_story_eyebrow', null, 'Our Story')); ?></p>
        <h2><?php echo esc_html(src_page_field('page_story_heading', null, 'A firm built on proximity to the entrepreneur.')); ?></h2>
        <div class="about-v3-story-body">
          <?php foreach ($story_paragraphs as $paragraph) : if (trim($paragraph) === '') continue; ?>
            <p><?php echo esc_html(trim($paragraph)); ?></p>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="about-v3-story-media">
        <img src="<?php echo esc_url(src_page_field('page_story_image_url', null, 'https://images.unsplash.com/photo-1776039213401-f95bfd9c516a?w=800&h=1000&fit=crop&auto=format')); ?>" alt="<?php echo esc_attr(src_page_field('page_story_image_alt', null, 'SRC team training session')); ?>" loading="lazy">
        <div class="about-v3-story-tint" aria-hidden="true"></div>
        <div class="about-v3-founded">
          <strong><?php echo esc_html(src_page_field('page_story_stat', null, '2012')); ?></strong>
          <span><?php echo esc_html(src_page_field('page_story_stat_label', null, 'Founded')); ?></span>
        </div>
      </div>
    </div>
  </section>

  <section class="about-v3-pillars" aria-label="Vision, mission and purpose">
    <?php foreach ($pillars as $index => $pillar) : ?>
      <article class="about-v3-pillar about-v3-pillar-<?php echo esc_attr((string) ($index + 1)); ?>">
        <p><?php echo esc_html($pillar[0] ?? ''); ?></p>
        <h2><?php echo esc_html($pillar[1] ?? ''); ?></h2>
      </article>
    <?php endforeach; ?>
  </section>

  <section class="about-v3-values">
    <div class="about-v3-shell">
      <header class="about-v3-centered-heading">
        <p class="about-v3-section-eyebrow"><?php echo esc_html(src_page_field('page_values_eyebrow', null, 'What We Stand For')); ?></p>
        <h2><?php echo esc_html(src_page_field('page_values_heading', null, 'Core Values')); ?></h2>
      </header>
      <div class="about-v3-values-grid">
        <?php foreach ($values as $value) : ?>
          <article class="about-v3-value">
            <h3><?php echo esc_html($value[0] ?? ''); ?></h3>
            <p><?php echo esc_html($value[1] ?? ''); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="about-v3-theory">
    <div class="about-v3-shell">
      <header class="about-v3-section-heading">
        <p class="about-v3-section-eyebrow"><?php echo esc_html(src_page_field('page_theory_eyebrow', null, 'How Change Happens')); ?></p>
        <h2><?php echo esc_html(src_page_field('page_theory_heading', null, 'Theory of Change')); ?></h2>
      </header>
      <div class="about-v3-theory-grid">
        <?php foreach ($theory_steps as $step) : ?>
          <article class="about-v3-theory-step">
            <span aria-hidden="true"><?php echo esc_html($step[0] ?? ''); ?></span>
            <div>
              <h3><?php echo esc_html($step[1] ?? ''); ?></h3>
              <p><?php echo esc_html($step[2] ?? ''); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="about-v3-stats" aria-label="SRC impact statistics">
    <div class="about-v3-shell about-v3-stats-grid">
      <?php foreach ($about_stats as $stat) : ?>
        <div class="about-v3-stat">
          <strong><?php echo esc_html($stat[0] ?? ''); ?></strong>
          <span><?php echo esc_html($stat[1] ?? ''); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="about-v3-cta">
    <p class="about-v3-section-eyebrow"><?php echo esc_html(src_page_field('page_about_cta_eyebrow', null, 'Meet the team')); ?></p>
    <h2><?php echo esc_html(src_page_field('page_about_cta_heading', null, 'The people behind SRC')); ?></h2>
    <a href="<?php echo esc_url(src_page_field('page_about_cta_url', null, src_page_url('leadership'))); ?>"><?php echo esc_html(src_page_field('page_about_cta_button', null, 'Meet Our Leadership')); ?></a>
  </section>
</main>

<?php get_footer(); ?>
