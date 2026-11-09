<?php
get_header();

$settings = array_merge(src_settings_defaults(), get_option('src_settings', []));
$departments = src_leadership_departments();
$senior_members = src_leadership_team_members('senior-management');
$featured_member = $senior_members ? array_shift($senior_members) : null;
$board_placeholders = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $settings['leadership_board_placeholders'] ?? ''))));
$board_section = [
    'id' => 'board',
    'nav_label' => $settings['leadership_board_nickname'] ?? 'The Compass',
    'formal' => $settings['leadership_board_formal'] ?? 'Board of Directors',
    'nickname' => $settings['leadership_board_nickname'] ?? 'The Compass',
    'tagline' => $settings['leadership_board_tagline'] ?? "Setting SRC's direction.",
];

$programme_members = src_leadership_team_members('programmes-team');
$programme_leads = array_values(array_filter($programme_members, static fn($member) => $member['county'] === ''));
$county_members = array_values(array_filter($programme_members, static fn($member) => $member['county'] !== ''));
$programme_members = array_merge($programme_leads, $county_members);
$counties = array_values(array_unique(array_filter(array_column($county_members, 'county'))));
sort($counties, SORT_NATURAL | SORT_FLAG_CASE);

$careers_url = src_page_url('careers');
$hero_image = $settings['leadership_hero_image_url'] ?? '';
$title_before = $settings['leadership_hero_title_before'] ?? 'The team behind';
$title_emphasis = $settings['leadership_hero_emphasis'] ?? 'the growth';
$hero_ring_hash = 0;
foreach (str_split('leadership') as $character) $hero_ring_hash = (($hero_ring_hash * 31) + ord($character)) & 0xFFFFFFFF;
$hero_ring_rotation = [0, 35, 70, 110, 150][$hero_ring_hash % 5];
?>

<main class="leadership-v3">
  <section class="page-hero page-hero--inner contact-v3-hero leadership-v3-hero" aria-labelledby="leadership-page-title">
    <div class="contact-v3-backdrop"><img src="<?php echo esc_url($hero_image); ?>" alt="A business team collaborating around a table" fetchpriority="high" style="object-position:center 45%"><div class="contact-v3-gradient" aria-hidden="true"></div></div>
    <div class="contact-v3-ring" style="--ring-rotate:<?php echo esc_attr((string) $hero_ring_rotation); ?>deg" aria-hidden="true"><svg viewBox="0 0 900 900" focusable="false"><circle cx="450" cy="450" r="405" fill="none" stroke="currentColor" stroke-width="90" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg></div>
    <div class="contact-v3-hero-content"><div class="contact-v3-hero-copy">
      <p class="contact-v3-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span>Leadership</p>
      <p class="contact-v3-eyebrow"><?php echo esc_html($settings['leadership_hero_eyebrow'] ?? 'OUR PEOPLE'); ?></p>
      <h1 id="leadership-page-title"><?php echo esc_html($title_before); ?> <em><?php echo esc_html($title_emphasis); ?></em></h1>
      <div class="leadership-v3-hero-actions">
        <a class="leadership-v3-hero-primary" href="#team-sections"><?php echo esc_html($settings['leadership_meet_button'] ?? 'Meet the team ↓'); ?></a>
        <a class="leadership-v3-hero-secondary" href="<?php echo esc_url($careers_url); ?>"><?php echo esc_html($settings['leadership_join_button'] ?? 'Join us → Careers'); ?></a>
      </div>
    </div></div>
  </section>

  <nav id="team-sections" class="sticky-subnav" aria-label="Team sections" data-leadership-subnav>
    <div class="sticky-subnav-scroll">
      <div class="sticky-subnav-row hide-scrollbar">
        <a class="sticky-subnav-item sticky-subnav-item--active" href="#board" data-team-section-link> <?php echo esc_html($board_section['nav_label']); ?></a>
        <?php foreach ($departments as $department) : ?>
          <a class="sticky-subnav-item" href="#<?php echo esc_attr($department['id']); ?>" data-team-section-link><?php echo esc_html($department['nav_label']); ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </nav>

  <section id="board" class="leadership-v3-section leadership-v3-section--white subnav-anchor" data-team-section>
    <div class="leadership-v3-shell">
      <?php src_leadership_render_section_heading($board_section['formal'], $board_section['nickname'], $board_section['tagline'], 'the-compass'); ?>
      <div class="leadership-board-grid">
        <?php foreach ($board_placeholders as $placeholder) : ?>
          <article class="leadership-board-card">
            <span><?php echo esc_html($board_section['formal']); ?></span>
            <div><strong><?php echo esc_html(preg_match('/\d+$/', $placeholder, $match) ? $match[0] : $placeholder); ?></strong><p>Profile forthcoming</p></div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php foreach ($departments as $department) :
      $members = src_leadership_team_members($department['id']);
      if ($department['id'] === 'senior-management') $members = $senior_members;
      if ($department['id'] === 'programmes-team') $members = $programme_members;
      $background = $department['background'] === 'cream' ? 'cream' : 'white';
      ?>
    <section id="<?php echo esc_attr($department['id']); ?>" class="leadership-v3-section leadership-v3-section--<?php echo esc_attr($background); ?> subnav-anchor" data-team-section>
      <div class="leadership-v3-shell">
        <?php src_leadership_render_section_heading($department['formal'], $department['nickname'], $department['tagline'], $department['illustration']); ?>

        <?php if ($department['id'] === 'senior-management') : ?>
          <?php if ($featured_member) : ?>
            <article class="leadership-featured">
              <div class="leadership-featured-portrait"><?php src_leadership_render_portrait($featured_member, 'red'); ?></div>
              <div class="leadership-featured-content">
                <div class="leadership-featured-role"><?php echo esc_html($featured_member['role']); ?></div>
                <h3><?php echo esc_html($featured_member['name']); ?></h3>
                <div class="leadership-featured-bio">
                  <?php foreach (preg_split('/\n\s*\n/', $featured_member['bio']) as $paragraph) : if (trim($paragraph) !== '') : ?><p><?php echo esc_html(trim($paragraph)); ?></p><?php endif; endforeach; ?>
                </div>
                <?php if ($featured_member['linkedin'] !== '') : ?><a href="<?php echo esc_url($featured_member['linkedin']); ?>" target="_blank" rel="noreferrer" class="leadership-featured-link"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286ZM5.337 7.433a2.062 2.062 0 1 1 0-4.123 2.062 2.062 0 0 1 0 4.123ZM7.119 20.452H3.555V9h3.564v11.452Z" /></svg> LinkedIn</a><?php endif; ?>
              </div>
            </article>
          <?php endif; ?>
          <div class="round-table-row">
            <?php foreach ($members as $index => $member) : $tone = $index % 2 === 0 ? 'yellow' : 'grey'; $badge = $member['name'] === 'Emmaculate Mulinge' ? 'The Hive · People & Culture' : ''; src_leadership_render_team_card($member, $tone, false, false, $badge); endforeach; ?>
          </div>

        <?php elseif ($department['id'] === 'programmes-team') : ?>
          <div class="leadership-programmes-toolbar">
            <h3><?php echo esc_html($settings['leadership_programmes_filter_heading'] ?? 'County Program Manager/Officer'); ?></h3>
            <div class="leadership-county-filter-shell">
              <div class="leadership-county-filters hide-scrollbar" aria-label="Filter by county">
                <button type="button" class="leadership-county-filter is-active" data-team-county-filter="All" aria-pressed="true">All</button>
                <?php foreach ($counties as $county) : ?><button type="button" class="leadership-county-filter" data-team-county-filter="<?php echo esc_attr($county); ?>" aria-pressed="false"><?php echo esc_html($county); ?></button><?php endforeach; ?>
              </div>
              <div class="leadership-county-scroll-hint" aria-hidden="true"><?php echo esc_html($settings['leadership_programmes_scroll_hint'] ?? 'Swipe to see all counties →'); ?></div>
            </div>
          </div>
          <div class="brainstorm-team-grid" data-team-programme-grid>
            <?php foreach ($members as $index => $member) : $tone = src_leadership_team_tone($member, $index, $members, true); src_leadership_render_team_card($member, $tone, false, $member['name'] === 'Bonano Badia', '', true); endforeach; ?>
          </div>

        <?php else : ?>
          <div class="team-directory-grid">
            <?php foreach ($members as $index => $member) : $tone = src_leadership_team_tone($member, $index, $members); src_leadership_render_team_card($member, $tone); endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  <?php endforeach; ?>

  <section class="leadership-v3-cta">
    <div class="leadership-v3-cta-inner"><h2><?php echo esc_html($settings['leadership_cta_heading'] ?? 'Want to work with this team?'); ?></h2><a href="<?php echo esc_url($careers_url); ?>"><?php echo esc_html($settings['leadership_cta_button'] ?? 'See open positions'); ?></a></div>
  </section>
</main>

<?php get_footer(); ?>
