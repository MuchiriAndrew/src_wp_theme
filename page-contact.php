<?php
get_header();

$page_id = get_queried_object_id();
$hero_title = (string) src_page_field('page_hero_title', $page_id, "Let's build something great.");
$hero_emphasis = (string) src_page_field('page_hero_emphasis', $page_id, 'something great');
$hero_eyebrow = (string) src_page_field('page_hero_eyebrow', $page_id, 'Nairobi, Kenya');
$hero_intro = (string) src_page_field('page_hero_intro', $page_id, "Tell us about your enterprise, programme or partnership and we'll get back to you within two working days.");
$hero_image = (string) src_page_field('page_hero_image_url', $page_id, 'https://images.unsplash.com/photo-1664181220731-06219378d8c7?auto=format&fit=crop&w=2000&q=85');
$title_parts = $hero_emphasis !== '' ? explode($hero_emphasis, $hero_title, 2) : [$hero_title];
$subject_options = src_parse_pairs(src_page_field('page_contact_subject_options', $page_id, ''));
$requested_subject = sanitize_title(wp_unslash($_GET['subject'] ?? ''));
if ($requested_subject === '' && !empty($_GET['programme'])) $requested_subject = 'programmes';
$offices = src_parse_rows(src_page_field('page_contact_offices', $page_id, ''));
$socials = src_parse_rows(src_page_field('page_contact_socials', $page_id, ''));
$status = sanitize_key(wp_unslash($_GET['contact_status'] ?? ''));
$pagehero_hash = 0;
foreach (str_split('contact') as $character) $pagehero_hash = (($pagehero_hash * 31) + ord($character)) & 0xFFFFFFFF;
$ring_rotation = [0, 35, 70, 110, 150][$pagehero_hash % 5];
?>

<main class="contact-v3">
  <section class="page-hero page-hero--inner contact-v3-hero" aria-labelledby="contact-page-title">
    <div class="contact-v3-backdrop"><img src="<?php echo esc_url($hero_image); ?>" alt="A busy street in Nairobi" fetchpriority="high"><div class="contact-v3-gradient" aria-hidden="true"></div></div>
    <div class="contact-v3-ring" style="--ring-rotate:<?php echo esc_attr((string) $ring_rotation); ?>deg" aria-hidden="true"><svg viewBox="0 0 900 900" focusable="false"><circle cx="450" cy="450" r="405" fill="none" stroke="currentColor" stroke-width="90" pathLength="100" stroke-dasharray="70 30" stroke-linecap="round"></circle></svg></div>
    <div class="contact-v3-hero-content"><div class="contact-v3-hero-copy">
      <p class="contact-v3-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span>Contact</p>
      <p class="contact-v3-eyebrow"><?php echo esc_html($hero_eyebrow); ?></p>
      <h1 id="contact-page-title"><?php if (count($title_parts) === 2) : ?><?php echo esc_html($title_parts[0]); ?><em><?php echo esc_html($hero_emphasis); ?></em><?php echo esc_html($title_parts[1]); ?><?php else : ?><?php echo esc_html($hero_title); ?><?php endif; ?></h1>
      <p class="contact-v3-intro"><?php echo esc_html($hero_intro); ?></p>
    </div></div>
  </section>

  <section class="contact-v3-main">
    <div class="contact-v3-grid">
      <div class="contact-v3-form-column">
        <p class="contact-v3-section-eyebrow"><?php echo esc_html(src_page_field('page_contact_form_eyebrow', $page_id, 'Send a Message')); ?></p>
        <?php if ($status === 'sent') : ?>
          <div class="contact-v3-success" role="status"><div aria-hidden="true">✓</div><h2><?php echo esc_html(src_page_field('page_contact_success_heading', $page_id, 'Message received')); ?></h2><p><?php echo esc_html(src_page_field('page_contact_success_message', $page_id, 'Thank you for reaching out. We respond to all enquiries within 2 working days.')); ?></p></div>
        <?php else : ?>
          <?php if ($status === 'error') : ?><p class="contact-v3-error" role="alert"><?php echo esc_html(src_page_field('page_contact_error_message', $page_id, 'We could not send your message just now. Please try again or email SRC directly.')); ?></p><?php endif; ?>
          <form class="contact-v3-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="src_contact">
            <?php wp_nonce_field('src_contact_submit', 'src_contact_nonce'); ?>
            <div class="contact-v3-name-row">
              <label class="screen-reader-text" for="contact-name"><?php echo esc_html(src_page_field('page_contact_name_placeholder', $page_id, 'Full name')); ?></label><input id="contact-name" name="name" type="text" placeholder="<?php echo esc_attr(src_page_field('page_contact_name_placeholder', $page_id, 'Full name')); ?>" required autocomplete="name">
              <label class="screen-reader-text" for="contact-organisation"><?php echo esc_html(src_page_field('page_contact_organisation_placeholder', $page_id, 'Organisation')); ?></label><input id="contact-organisation" name="organisation" type="text" placeholder="<?php echo esc_attr(src_page_field('page_contact_organisation_placeholder', $page_id, 'Organisation')); ?>" autocomplete="organization">
            </div>
            <label class="screen-reader-text" for="contact-email"><?php echo esc_html(src_page_field('page_contact_email_placeholder', $page_id, 'Email address')); ?></label><input id="contact-email" name="email" type="email" placeholder="<?php echo esc_attr(src_page_field('page_contact_email_placeholder', $page_id, 'Email address')); ?>" required autocomplete="email">
            <div class="contact-v3-select-wrap"><label class="screen-reader-text" for="contact-subject"><?php echo esc_html(src_page_field('page_contact_subject_placeholder', $page_id, 'Subject / Enquiry type')); ?></label><select id="contact-subject" name="subject"><option value=""><?php echo esc_html(src_page_field('page_contact_subject_placeholder', $page_id, 'Subject / Enquiry type')); ?></option><?php foreach ($subject_options as [$value, $label]) : ?><option value="<?php echo esc_attr($value); ?>"<?php selected($requested_subject, $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select><svg viewBox="0 0 16 16" aria-hidden="true"><path d="m3 6 5 5 5-5" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"></path></svg></div>
            <label class="screen-reader-text" for="contact-message"><?php echo esc_html(src_page_field('page_contact_message_placeholder', $page_id, 'Your message...')); ?></label><textarea id="contact-message" name="message" rows="5" placeholder="<?php echo esc_attr(src_page_field('page_contact_message_placeholder', $page_id, 'Your message...')); ?>" required></textarea>
            <button type="submit"><?php echo esc_html(src_page_field('page_contact_submit_label', $page_id, 'Send Message')); ?></button>
          </form>
        <?php endif; ?>
      </div>

      <aside class="contact-v3-details">
        <section class="contact-v3-offices" aria-labelledby="contact-offices-title">
          <h2 id="contact-offices-title" class="contact-v3-section-eyebrow"><?php echo esc_html(src_page_field('page_contact_offices_eyebrow', $page_id, 'Our Offices')); ?></h2>
          <div class="contact-v3-office-list">
            <?php foreach ($offices as $office) :
                $office_name = $office[0] ?? ''; $office_location = $office[1] ?? ''; $office_email = sanitize_email($office[2] ?? ''); $accent = sanitize_key($office[3] ?? 'grey');
                if ($office_name === '') continue;
                ?>
              <article class="contact-v3-office contact-v3-office--<?php echo esc_attr($accent === 'red' ? 'red' : 'grey'); ?>">
                <h3><?php echo esc_html($office_name); ?></h3><p><?php echo esc_html($office_location); ?></p>
                <?php if ($office_email) : ?><a href="mailto:<?php echo esc_attr($office_email); ?>"><?php echo esc_html($office_email); ?></a><?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <?php $configured_socials = array_values(array_filter($socials, static function ($row) { return !empty($row[0]) && src_option(sanitize_key($row[0]), '') && !empty($row[1]); })); if ($configured_socials) : ?><section class="contact-v3-social" aria-labelledby="contact-social-title">
          <h2 id="contact-social-title" class="contact-v3-section-eyebrow"><?php echo esc_html(src_page_field('page_contact_social_eyebrow', $page_id, 'Follow SRC')); ?></h2>
          <div class="contact-v3-social-list">
            <?php foreach ($configured_socials as [$setting, $label, $handle]) : $href = src_option(sanitize_key($setting), ''); ?>
              <a href="<?php echo esc_url($href); ?>" target="_blank" rel="noopener noreferrer"><span><?php echo esc_html($label); ?></span><span><?php echo esc_html($handle ?? ''); ?></span></a>
            <?php endforeach; ?>
          </div>
        </section><?php endif; ?>
      </aside>
    </div>
  </section>
</main>

<?php get_footer(); ?>
