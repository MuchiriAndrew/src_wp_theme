<?php if (!defined('ABSPATH')) exit; ?>
<div class="newsletter-ref">
    <div class="container newsletter-grid">
        <div>
            <p class="eyebrow eyebrow-light"><?php echo esc_html(src_option('newsletter_eyebrow', 'Stay connected')); ?></p>
            <h2 class="display"><?php echo nl2br(esc_html(src_option('newsletter_heading', "Building enterprises from Good to Great\nsince 2012."))); ?></h2>
        </div>
        <form class="newsletter-form" action="#" method="post" onsubmit="return false;">
            <label class="sr-only" for="newsletter-email">Your email address</label>
            <input id="newsletter-email" type="email" placeholder="Your email address" autocomplete="email">
            <button type="submit">Subscribe</button>
        </form>
    </div>
</div>

<footer class="footer"><div class="container">
  <div class="footer-grid">
    <div><a class="brand" href="<?php echo esc_url(home_url('/')); ?>"><span class="brand-mark">SRC</span><span class="brand-copy">Smart Regional<small>Consultants</small></span></a><p style="max-width:300px;margin-top:22px"><?php echo esc_html(src_option('site_description', 'Nairobi-based enterprise support organisation. Building SMEs from Good to Great across Kenya and East Africa.')); ?></p><div class="social-links"><?php foreach (['social_linkedin' => 'LinkedIn', 'social_x' => 'X', 'social_facebook' => 'Facebook', 'social_youtube' => 'YouTube'] as $key => $label) : $url = src_option($key, ''); if (!$url) continue; ?><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($label); ?></a><?php endforeach; ?></div></div>
    <div><h3>Navigate</h3><div class="footer-links"><a href="<?php echo esc_url(src_page_url('about')); ?>">About SRC</a><a href="<?php echo esc_url(src_page_url('what-we-do')); ?>">What We Do</a><a href="<?php echo esc_url(get_post_type_archive_link('project')); ?>">Our Work</a><a href="<?php echo esc_url(src_page_url('pool-of-experts')); ?>">Pool of Experts</a><a href="<?php echo esc_url(src_page_url('partners')); ?>">Clients & Partners</a><a href="<?php echo esc_url(get_post_type_archive_link('team_member')); ?>">Leadership</a><a href="<?php echo esc_url(src_page_url('get-involved')); ?>">Get Involved</a><a href="<?php echo esc_url(src_page_url('careers')); ?>">Careers</a></div></div>
    <div><h3>County Footprint</h3><div class="county-list"><?php echo esc_html(src_option('county_footprint', 'Nairobi · Mombasa · Kisumu · Nakuru · Garissa · Marsabit · Turkana · Wajir · Mandera · Isiolo · Samburu · Laikipia · Tana River · Kwale · Kajiado')); ?></div><p>+ Tanzania · Malawi · Zambia<br>South Africa · Senegal</p></div>
    <div><h3>Contact</h3><p><?php echo esc_html(src_option('contact_city', 'Nairobi, Kenya')); ?></p><p><a href="mailto:<?php echo esc_attr(src_option('contact_email', 'info@srctech.co.ke')); ?>"><?php echo esc_html(src_option('contact_email', 'info@srctech.co.ke')); ?></a><br><?php echo esc_html(src_option('contact_phone', '+254 (0) 20 000 0000')); ?></p><a class="footer-contact" href="<?php echo esc_url(src_page_url('contact')); ?>">Get in touch</a></div>
  </div>
  <div class="footer-bottom"><span>© <?php echo esc_html(date('Y')); ?> Smart Regional Consultants Ltd. SRC is an equal opportunity employer.</span><span>srctech.co.ke</span></div>
</div></footer><?php wp_footer(); ?></body></html>
