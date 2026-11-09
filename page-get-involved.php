<?php
get_header();

$page_id = get_queried_object_id();
$hero_title = (string) src_page_field('page_hero_title', $page_id, 'Get Involved');
$hero_eyebrow = (string) src_page_field('page_hero_eyebrow', $page_id, 'Get Involved');
$hero_intro = (string) src_page_field('page_hero_intro', $page_id, "Whether you're a donor, a commercial enterprise or a collaborator — there is a way to work with SRC.");
$hero_image = (string) src_page_field('page_hero_image_url', $page_id, 'https://images.unsplash.com/photo-1637149014802-d917e6255c2d?auto=format&fit=crop&w=2000&q=85');
$track_rows = src_parse_rows(src_page_field('page_involvement_tracks', $page_id, ''));
$tracks = [];
foreach ($track_rows as $row) {
    if (count($row) < 4) continue;
    $track_id = sanitize_key($row[0]);
    if ($track_id === '') continue;
    $tracks[] = [
        'id' => $track_id,
        'label' => $row[1] ?? '',
        'subtitle' => $row[2] ?? '',
        'description' => $row[3] ?? '',
        'background' => sanitize_hex_color($row[4] ?? '') ?: '#B3262C',
    ];
}
$capability_rows = src_parse_rows(src_page_field('page_involvement_capabilities', $page_id, ''));
$capabilities = [];
foreach ($capability_rows as $row) {
    $track_id = sanitize_key($row[0] ?? '');
    $text = sanitize_text_field($row[1] ?? '');
    if ($track_id !== '' && $text !== '') $capabilities[$track_id][] = $text;
}
$first_track = $tracks[0]['id'] ?? 'donor';
$requested_track = isset($_GET['track']) && is_scalar($_GET['track']) ? sanitize_key(wp_unslash($_GET['track'])) : $first_track;
$track_ids = array_column($tracks, 'id');
$active_track = in_array($requested_track, $track_ids, true) ? $requested_track : $first_track;
$active_config = current(array_filter($tracks, static fn($track) => $track['id'] === $active_track)) ?: ['background' => '#B3262C', 'id' => $active_track];
$active_accent = $active_track === 'enterprise' ? '#2B2B2B' : ($active_track === 'collaborate' ? '#F58220' : '#B3262C');
$status = isset($_GET['involvement_status']) && is_scalar($_GET['involvement_status']) ? sanitize_key(wp_unslash($_GET['involvement_status'])) : '';
$pagehero_hash = 0;
foreach (str_split(strtolower($hero_eyebrow)) as $character) $pagehero_hash = (($pagehero_hash * 31) + ord($character)) & 0xFFFFFFFF;
$ring_rotation = [0, 35, 70, 110, 150][$pagehero_hash % 5];
?>
<main class="src-involvement-v3">
  <section class="page-hero page-hero--inner src-v3-programmes-hero src-involvement-v3-hero" aria-labelledby="src-involvement-title">
    <img class="src-v3-programmes-backdrop" src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high">
    <div class="src-v3-programmes-ring src-experts-v3-ring" style="--ring-rotate:<?php echo esc_attr((string) $ring_rotation); ?>deg" aria-hidden="true"><svg viewBox="0 0 900 900" focusable="false"><circle cx="450" cy="450" r="405" fill="none" stroke="currentColor" stroke-width="90" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg></div>
    <div class="src-v3-programmes-hero-content">
      <div class="src-v3-programmes-hero-copy">
        <p class="src-v3-programmes-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><?php echo esc_html($hero_eyebrow); ?></p>
        <p class="eyebrow"><?php echo esc_html($hero_eyebrow); ?></p>
        <h1 id="src-involvement-title"><?php echo esc_html($hero_title); ?></h1>
        <p class="lede"><?php echo esc_html($hero_intro); ?></p>
      </div>
    </div>
  </section>

  <nav class="src-involvement-v3-tabs" aria-label="Ways to work with SRC" data-involvement-tabs>
    <div class="src-involvement-v3-tab-shell">
      <div class="src-involvement-v3-tab-row" aria-label="Partnership pathways">
        <?php foreach ($tracks as $track) : $is_active = $track['id'] === $active_track; ?>
          <button type="button" id="src-involvement-tab-<?php echo esc_attr($track['id']); ?>" class="src-involvement-v3-tab<?php echo $is_active ? ' is-active' : ''; ?>" data-involvement-tab="<?php echo esc_attr($track['id']); ?>">
            <span><?php echo esc_html($track['label']); ?></span>
            <small><?php echo esc_html($track['subtitle']); ?></small>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </nav>

  <section class="src-involvement-v3-content" data-involvement-content style="--involvement-background:<?php echo esc_attr($active_config['background']); ?>;--involvement-accent:<?php echo esc_attr($active_accent); ?>">
    <div class="src-involvement-v3-shell">
      <div class="src-involvement-v3-grid">
        <div class="src-involvement-v3-track-copy">
          <?php foreach ($tracks as $track) : $is_active = $track['id'] === $active_track; ?>
            <div role="tabpanel" id="src-involvement-panel-<?php echo esc_attr($track['id']); ?>" aria-labelledby="src-involvement-tab-<?php echo esc_attr($track['id']); ?>" class="src-involvement-v3-panel" data-involvement-panel="<?php echo esc_attr($track['id']); ?>" data-background="<?php echo esc_attr($track['background']); ?>" data-accent="<?php echo esc_attr($track['id'] === 'enterprise' ? '#2B2B2B' : ($track['id'] === 'collaborate' ? '#F58220' : '#B3262C')); ?>" <?php echo $is_active ? '' : 'hidden'; ?>>
              <p class="src-involvement-v3-subtitle"><?php echo esc_html($track['subtitle']); ?></p>
              <h2><?php echo esc_html($track['label']); ?></h2>
              <p class="src-involvement-v3-description"><?php echo esc_html($track['description']); ?></p>
              <ul>
                <?php foreach ($capabilities[$track['id']] ?? [] as $capability) : ?>
                  <li><span aria-hidden="true"><i></i></span><?php echo esc_html($capability); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="src-involvement-v3-form-column">
          <p class="src-involvement-v3-form-eyebrow"><?php echo esc_html(src_page_field('page_involvement_form_eyebrow', $page_id, 'Get in touch')); ?></p>
          <h3><?php echo esc_html(src_page_field('page_involvement_form_heading', $page_id, 'Start a conversation')); ?></h3>
          <p class="src-involvement-v3-form-intro"><?php echo esc_html(src_page_field('page_involvement_form_intro', $page_id, 'We respond to all enquiries within 2 working days.')); ?></p>
          <?php if ($status === 'sent') : ?>
            <div class="src-involvement-v3-success" role="status"><span aria-hidden="true">✓</span><p><?php echo esc_html(src_page_field('page_involvement_success_message', $page_id, "Thank you — we'll be in touch shortly.")); ?></p></div>
          <?php else : ?>
            <?php if ($status === 'error') : ?><p class="src-involvement-v3-error" role="alert"><?php echo esc_html(src_page_field('page_involvement_error_message', $page_id, 'We could not send your message just now. Please try again or email SRC directly.')); ?></p><?php endif; ?>
            <form class="src-involvement-v3-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-involvement-form>
              <input type="hidden" name="action" value="src_involvement">
              <input type="hidden" name="involvement_track" value="<?php echo esc_attr($active_track); ?>" data-involvement-track-input>
              <?php wp_nonce_field('src_involvement_submit', 'src_involvement_nonce'); ?>
              <div class="src-involvement-v3-name-row">
                <label class="screen-reader-text" for="src-involvement-name"><?php echo esc_html(src_page_field('page_involvement_name_placeholder', $page_id, 'Your name')); ?></label><input id="src-involvement-name" name="name" type="text" placeholder="<?php echo esc_attr(src_page_field('page_involvement_name_placeholder', $page_id, 'Your name')); ?>" required autocomplete="name">
                <label class="screen-reader-text" for="src-involvement-role"><?php echo esc_html(src_page_field('page_involvement_role_placeholder', $page_id, 'Role / Title')); ?></label><input id="src-involvement-role" name="role" type="text" placeholder="<?php echo esc_attr(src_page_field('page_involvement_role_placeholder', $page_id, 'Role / Title')); ?>" autocomplete="organization-title">
              </div>
              <label class="screen-reader-text" for="src-involvement-email"><?php echo esc_html(src_page_field('page_involvement_email_placeholder', $page_id, 'Email address')); ?></label><input id="src-involvement-email" name="email" type="email" placeholder="<?php echo esc_attr(src_page_field('page_involvement_email_placeholder', $page_id, 'Email address')); ?>" required autocomplete="email">
              <label class="screen-reader-text" for="src-involvement-organisation"><?php echo esc_html(src_page_field('page_involvement_organisation_placeholder', $page_id, 'Organisation')); ?></label><input id="src-involvement-organisation" name="organisation" type="text" placeholder="<?php echo esc_attr(src_page_field('page_involvement_organisation_placeholder', $page_id, 'Organisation')); ?>" autocomplete="organization">
              <label class="screen-reader-text" for="src-involvement-message"><?php echo esc_html(src_page_field('page_involvement_message_placeholder', $page_id, 'Tell us about your interest or programme idea')); ?></label><textarea id="src-involvement-message" name="message" rows="3" placeholder="<?php echo esc_attr(src_page_field('page_involvement_message_placeholder', $page_id, 'Tell us about your interest or programme idea...')); ?>"></textarea>
              <button type="submit" data-involvement-submit><?php echo esc_html(src_page_field('page_involvement_submit_label', $page_id, 'Send Message')); ?></button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</main>
<?php get_footer(); ?>
