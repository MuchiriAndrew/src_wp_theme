<?php
get_header();

$page_id = get_queried_object_id();
$hero_title = (string) src_page_field('page_hero_title', $page_id, 'Do work that moves enterprises.');
$hero_emphasis = (string) src_page_field('page_hero_emphasis', $page_id, 'moves');
$hero_eyebrow = (string) src_page_field('page_hero_eyebrow', $page_id, 'Careers');
$hero_intro = (string) src_page_field('page_hero_intro', $page_id, "Join our team or our Pool of Experts and help build Africa's next generation of businesses.");
$hero_image = (string) src_page_field('page_hero_image_url', $page_id, 'https://images.unsplash.com/photo-1573164574511-73c773193279?auto=format&fit=crop&w=2000&q=85');
$title_parts = $hero_emphasis !== '' ? explode($hero_emphasis, $hero_title, 2) : [$hero_title];
$jobs = get_posts(['post_type' => 'job', 'post_status' => 'publish', 'posts_per_page' => -1]);
usort($jobs, static function ($a, $b) {
    $a_order = (int) get_post_meta($a->ID, '_job_order', true);
    $b_order = (int) get_post_meta($b->ID, '_job_order', true);
    return ($a_order <=> $b_order) ?: strcasecmp($a->post_title, $b->post_title);
});
$requested_job = isset($_GET['job']) && is_scalar($_GET['job']) ? absint(wp_unslash($_GET['job'])) : 0;
$active_job_id = 0;
foreach ($jobs as $job) if ($job->ID === $requested_job) $active_job_id = $job->ID;
$application_status = isset($_GET['application_status']) && is_scalar($_GET['application_status']) ? sanitize_key(wp_unslash($_GET['application_status'])) : '';
$pagehero_hash = 0;
foreach (str_split(strtolower($hero_eyebrow)) as $character) $pagehero_hash = (($pagehero_hash * 31) + ord($character)) & 0xFFFFFFFF;
$ring_rotation = [0, 35, 70, 110, 150][$pagehero_hash % 5];
?>
<main class="src-careers-v3">
  <section class="page-hero page-hero--inner src-v3-programmes-hero src-careers-v3-hero" aria-labelledby="src-careers-title">
    <img class="src-v3-programmes-backdrop" src="<?php echo esc_url($hero_image); ?>" alt="" fetchpriority="high">
    <div class="src-v3-programmes-ring src-experts-v3-ring" style="--ring-rotate:<?php echo esc_attr((string) $ring_rotation); ?>deg" aria-hidden="true"><svg viewBox="0 0 900 900" focusable="false"><circle cx="450" cy="450" r="405" fill="none" stroke="currentColor" stroke-width="90" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg></div>
    <div class="src-v3-programmes-hero-content">
      <div class="src-v3-programmes-hero-copy">
        <p class="src-v3-programmes-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span><?php echo esc_html($hero_eyebrow); ?></p>
        <p class="eyebrow"><?php echo esc_html($hero_eyebrow); ?></p>
        <h1 id="src-careers-title"><?php if (count($title_parts) === 2) : ?><?php echo esc_html($title_parts[0]); ?><em><?php echo esc_html($hero_emphasis); ?></em><?php echo esc_html($title_parts[1]); ?><?php else : ?><?php echo esc_html($hero_title); ?><?php endif; ?></h1>
        <p class="lede"><?php echo esc_html($hero_intro); ?></p>
      </div>
    </div>
  </section>

  <section class="src-careers-v3-openings" aria-labelledby="src-careers-openings-title">
    <div class="src-careers-v3-shell">
      <header class="src-careers-v3-heading">
        <div>
          <p><?php echo esc_html(src_page_field('page_careers_eyebrow', $page_id, 'Current Openings')); ?></p>
          <h2 id="src-careers-openings-title"><?php echo esc_html(src_page_field('page_careers_heading', $page_id, 'Open Positions')); ?></h2>
        </div>
        <span><?php echo esc_html((string) count($jobs)); ?> <?php echo esc_html(src_page_field('page_careers_positions_label', $page_id, 'positions')); ?></span>
      </header>

      <?php if ($jobs) : ?>
        <div class="src-careers-v3-list">
          <?php foreach ($jobs as $job) :
              $is_active = $job->ID === $active_job_id;
              $location = (string) src_field('job_location', $job->ID, 'Nairobi, Kenya');
              $type = (string) src_field('job_type', $job->ID, 'Full-time');
              $department = (string) src_field('job_department', $job->ID, 'Programmes');
              $description = trim((string) $job->post_content);
              if ($description === '') $description = (string) $job->post_excerpt;
              $requirements = preg_split('/\r\n|\r|\n/', (string) get_post_meta($job->ID, '_job_requirements', true));
              $requirements = array_values(array_filter(array_map('trim', $requirements)));
              $detail_id = 'src-career-details-' . $job->ID;
              ?>
            <article class="src-careers-v3-card" data-career-card>
              <header class="src-careers-v3-card-head">
                <div class="src-careers-v3-card-copy">
                  <div class="src-careers-v3-title-row">
                    <h3><?php echo esc_html(get_the_title($job)); ?></h3>
                    <span><?php echo esc_html($type); ?></span>
                  </div>
                  <div class="src-careers-v3-meta"><span><?php echo esc_html($location); ?></span><span><?php echo esc_html($department); ?></span></div>
                </div>
                <button type="button" aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($detail_id); ?>" data-career-toggle data-apply-label="<?php echo esc_attr(src_page_field('page_careers_apply_label', $page_id, 'Apply Now')); ?>" data-close-label="<?php echo esc_attr(src_page_field('page_careers_close_label', $page_id, 'Close')); ?>"><?php echo esc_html(src_page_field($is_active ? 'page_careers_close_label' : 'page_careers_apply_label', $page_id, $is_active ? 'Close' : 'Apply Now')); ?></button>
              </header>

              <div class="src-careers-v3-details" id="<?php echo esc_attr($detail_id); ?>" data-career-details<?php echo $is_active ? '' : ' hidden'; ?>>
                <div class="src-careers-v3-detail-copy">
                  <p><?php echo esc_html(wp_strip_all_tags($description)); ?></p>
                  <p class="src-careers-v3-requirements-label"><?php echo esc_html(src_page_field('page_careers_requirements_label', $page_id, 'Requirements')); ?></p>
                  <?php if ($requirements) : ?><ul><?php foreach ($requirements as $requirement) : ?><li><span aria-hidden="true"></span><?php echo esc_html($requirement); ?></li><?php endforeach; ?></ul><?php endif; ?>
                </div>
                <div class="src-careers-v3-application">
                  <?php if ($application_status === 'sent' && $is_active) : ?>
                    <div class="src-careers-v3-success" role="status">
                      <span aria-hidden="true">✓</span>
                      <p class="src-careers-v3-success-heading"><?php echo esc_html(src_page_field('page_careers_success_heading', $page_id, 'Application received')); ?></p>
                      <p><?php echo esc_html(sprintf(src_page_field('page_careers_success_message', $page_id, 'Thank you for applying to %s. We will be in touch within 5 working days.'), get_the_title($job))); ?></p>
                    </div>
                  <?php else : ?>
                    <?php if ($application_status === 'error' && $is_active) : ?><p class="src-careers-v3-error" role="alert"><?php echo esc_html(src_page_field('page_careers_error_message', $page_id, 'We could not send your application just now. Please try again.')); ?></p><?php endif; ?>
                    <form class="src-careers-v3-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                      <p><?php echo esc_html(src_page_field('page_careers_form_label', $page_id, 'Apply for')); ?>: <?php echo esc_html(get_the_title($job)); ?></p>
                      <input type="hidden" name="action" value="src_careers_application">
                      <input type="hidden" name="job_id" value="<?php echo esc_attr((string) $job->ID); ?>">
                      <?php wp_nonce_field('src_careers_application', 'src_careers_nonce'); ?>
                      <label class="screen-reader-text" for="career-name-<?php echo esc_attr((string) $job->ID); ?>">Full name</label><input id="career-name-<?php echo esc_attr((string) $job->ID); ?>" type="text" name="name" placeholder="<?php echo esc_attr(src_page_field('page_careers_name_placeholder', $page_id, 'Full name')); ?>" required autocomplete="name">
                      <label class="screen-reader-text" for="career-email-<?php echo esc_attr((string) $job->ID); ?>">Email address</label><input id="career-email-<?php echo esc_attr((string) $job->ID); ?>" type="email" name="email" placeholder="<?php echo esc_attr(src_page_field('page_careers_email_placeholder', $page_id, 'Email address')); ?>" required autocomplete="email">
                      <label class="screen-reader-text" for="career-phone-<?php echo esc_attr((string) $job->ID); ?>">Phone number</label><input id="career-phone-<?php echo esc_attr((string) $job->ID); ?>" type="tel" name="phone" placeholder="<?php echo esc_attr(src_page_field('page_careers_phone_placeholder', $page_id, 'Phone number')); ?>" autocomplete="tel">
                      <label class="screen-reader-text" for="career-cover-<?php echo esc_attr((string) $job->ID); ?>">Cover letter</label><textarea id="career-cover-<?php echo esc_attr((string) $job->ID); ?>" name="cover_letter" rows="4" placeholder="<?php echo esc_attr(src_page_field('page_careers_cover_placeholder', $page_id, "Cover letter — tell us why you're the right fit (max 300 words)")); ?>"></textarea>
                      <label class="src-careers-v3-cv-dropzone" data-cv-dropzone>
                        <input type="file" name="cv_resume" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" aria-describedby="career-cv-help-<?php echo esc_attr((string) $job->ID); ?>">
                        <span class="src-careers-v3-cv-icon" aria-hidden="true">↑</span>
                        <span class="src-careers-v3-cv-title" data-cv-title><?php echo esc_html(src_page_field('page_careers_cv_label', $page_id, '+ Attach CV / Resume (PDF preferred)')); ?></span>
                        <span class="src-careers-v3-cv-help" id="career-cv-help-<?php echo esc_attr((string) $job->ID); ?>">Click to browse or drag and drop a PDF, DOC or DOCX file (max 10 MB)</span>
                      </label>
                      <button type="submit"><?php echo esc_html(src_page_field('page_careers_submit_label', $page_id, 'Submit Application')); ?></button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <p class="src-careers-v3-empty"><?php echo esc_html(src_page_field('page_careers_empty_message', $page_id, 'There are no open positions right now. Please check back soon.')); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <section class="src-careers-v3-eoe">
    <div class="src-careers-v3-shell src-careers-v3-eoe-grid">
      <div>
        <p class="src-careers-v3-eoe-eyebrow"><?php echo esc_html(src_page_field('page_careers_eoe_eyebrow', $page_id, 'Equal Opportunity')); ?></p>
        <h2><?php echo esc_html(src_page_field('page_careers_eoe_heading', $page_id, 'SRC is an equal opportunity employer.')); ?></h2>
        <p class="src-careers-v3-eoe-copy"><?php echo esc_html(src_page_field('page_careers_eoe_copy', $page_id, "We are committed to creating an inclusive, diverse and equitable workplace. We actively encourage applications from women, young people, persons with disabilities and candidates from Kenya's marginalised communities.")); ?></p>
      </div>
      <div class="src-careers-v3-stats">
        <?php foreach (src_parse_pairs(src_page_field('page_careers_stats', $page_id, "51 | Team Members\n60% | Women on Staff\n15+ | Counties Represented")) as [$value, $label]) : ?>
          <div><strong><?php echo esc_html($value); ?></strong><span><?php echo esc_html($label); ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php get_footer(); ?>
