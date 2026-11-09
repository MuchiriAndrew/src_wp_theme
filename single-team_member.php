<?php
if (!defined('ABSPATH')) exit;

$post = get_queried_object();
if (!$post instanceof WP_Post) {
    get_header();
    echo '<main class="container"><p>Team profile unavailable.</p></main>';
    get_footer();
    return;
}

$member = src_leadership_member_data($post);
$departments = wp_get_post_terms($post->ID, 'team_department', ['fields' => 'names']);
$department = !is_wp_error($departments) && $departments ? $departments[0] : '';
$image = $member['image'] ?: get_theme_file_uri('/assets/src-logo.png');
$archive_url = get_post_type_archive_link('team_member') ?: home_url('/leadership/');
get_header();
?>
<main class="src-team-profile">
    <section class="src-team-profile-hero">
        <div class="src-team-profile-shell">
            <nav class="src-team-profile-breadcrumb" aria-label="Breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span aria-hidden="true">/</span>
                <a href="<?php echo esc_url($archive_url); ?>">Leadership</a><span aria-hidden="true">/</span>
                <span aria-current="page"><?php echo esc_html($member['name']); ?></span>
            </nav>
            <div class="src-team-profile-grid">
                <div class="src-team-profile-copy">
                    <p class="eyebrow"><?php echo esc_html(src_option('leadership_profile_eyebrow', 'Leadership profile')); ?><?php if ($department !== '') : ?> · <?php echo esc_html($department); ?><?php endif; ?></p>
                    <h1 class="display display-lg"><?php echo esc_html($member['name']); ?></h1>
                    <?php if ($member['role'] !== '') : ?><p class="src-team-profile-role"><?php echo esc_html($member['role']); ?></p><?php endif; ?>
                    <?php if ($member['county'] !== '') : ?><p class="src-team-profile-county"><?php echo esc_html($member['county']); ?><?php if ($member['county_level'] !== '') : ?> · <?php echo esc_html($member['county_level']); ?><?php endif; ?></p><?php endif; ?>
                    <?php if ($member['bio_short'] !== '') : ?><p class="src-team-profile-summary"><?php echo esc_html($member['bio_short']); ?></p><?php endif; ?>
                    <?php if ($member['linkedin'] !== '') : ?><a class="src-team-profile-linkedin" href="<?php echo esc_url($member['linkedin']); ?>" target="_blank" rel="noopener noreferrer">LinkedIn <span aria-hidden="true">↗</span></a><?php endif; ?>
                </div>
                <figure class="src-team-profile-portrait"><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($member['name']); ?>" fetchpriority="high"></figure>
            </div>
        </div>
    </section>
    <?php if (trim((string) $post->post_content) !== '') : ?>
        <section class="src-team-profile-bio">
            <div class="src-team-profile-bio-inner">
                <p class="eyebrow"><?php echo esc_html(src_option('leadership_profile_eyebrow', 'Leadership profile')); ?></p>
                <div class="src-team-profile-body"><?php echo apply_filters('the_content', $post->post_content); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                <a href="<?php echo esc_url($archive_url); ?>"><?php echo esc_html(src_option('leadership_profile_back_label', 'Back to Leadership')); ?> <span aria-hidden="true">→</span></a>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
