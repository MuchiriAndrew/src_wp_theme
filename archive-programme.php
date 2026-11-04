<?php
get_header();
$all = get_posts([
    'post_type' => 'programme', 'post_status' => 'publish', 'posts_per_page' => -1,
    'meta_key' => '_src_v3_imported', 'meta_value' => '1', 'orderby' => 'menu_order', 'order' => 'ASC',
]);
$counts = ['all' => count($all), 'current' => 0, 'upcoming' => 0, 'past' => 0];
foreach ($all as $item) {
    $status = get_post_meta($item->ID, '_programme_status', true);
    if (isset($counts[$status])) $counts[$status]++;
}
$selected_status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : 'all';
if (!in_array($selected_status, ['all', 'current', 'upcoming', 'past'], true)) $selected_status = 'all';
$selected_theme = isset($_GET['theme']) ? sanitize_title(wp_unslash($_GET['theme'])) : '';
$signature_only = isset($_GET['signature']) && $_GET['signature'] === '1';
$themes = get_terms(['taxonomy' => 'programme_theme', 'hide_empty' => false]);
$group_labels = [
    'current' => ['Active Now', 'Current Programmes', 'Programmes currently enrolling or being delivered with our partners.'],
    'upcoming' => ['Returning Soon', 'Upcoming', 'Proven programmes preparing for their next cohort.'],
    'past' => ['Our Track Record', 'Past Programmes', 'Programmes SRC has delivered with its partners.'],
];
?>
<main class="src-v3-programmes">
    <section class="page-hero src-v3-programmes-hero">
        <img class="src-v3-programmes-backdrop" src="https://images.unsplash.com/photo-1776039325240-02916820bfeb?auto=format&amp;fit=crop&amp;w=2000&amp;q=85" alt="Entrepreneurs attending a business training session" fetchpriority="high">
        <div class="src-v3-programmes-ring" aria-hidden="true"></div>
        <div class="container src-v3-programmes-hero-content" data-reveal="left">
            <div class="src-v3-programmes-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>/</span>Programmes</div>
            <p class="eyebrow eyebrow-light">Enrolling Now <span aria-hidden="true">·</span> Upcoming</p>
            <h1 class="display display-lg">Grow your business <em>with us.</em></h1>
            <p class="lede lede-light">Training, bootcamps and advisory designed for entrepreneurs ready for their next stage.</p>
            <div class="hero-actions">
                <a class="button" href="#src-programme-results">View Programmes</a>
                <a class="src-v3-hero-secondary" href="#src-programme-results"><span aria-hidden="true">→</span> How registration works</a>
            </div>
        </div>
    </section>

    <form class="src-programme-filters" method="get" action="<?php echo esc_url(get_post_type_archive_link('programme')); ?>" aria-label="Filter programmes">
        <div class="container src-programme-filter-inner">
            <input type="hidden" name="status" value="<?php echo esc_attr($selected_status === 'all' ? '' : $selected_status); ?>">
            <div class="src-programme-filter-status" role="group" aria-label="Filter by status">
                <?php foreach (['all' => 'All', 'current' => 'Current', 'upcoming' => 'Upcoming', 'past' => 'Past'] as $value => $label) : ?>
                    <button type="button" data-programme-status="<?php echo esc_attr($value === 'all' ? '' : $value); ?>" class="src-programme-filter <?php echo $selected_status === $value ? 'is-active' : ''; ?>" <?php echo $selected_status === $value ? 'aria-current="true"' : ''; ?>><?php echo esc_html($label); ?> <span><?php echo esc_html($counts[$value]); ?></span></button>
                <?php endforeach; ?>
            </div>
            <label class="src-programme-theme-select"><span class="screen-reader-text">Programme theme</span>
                <select name="theme" aria-label="Filter by programme theme">
                    <option value="">All themes</option>
                    <?php if (!is_wp_error($themes)) foreach ($themes as $theme) : ?>
                        <option value="<?php echo esc_attr($theme->slug); ?>" <?php selected($selected_theme, $theme->slug); ?>><?php echo esc_html($theme->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="src-programme-signature"><input type="checkbox" name="signature" value="1" <?php checked($signature_only); ?>> <span>SRC Signature only</span></label>
            <button class="src-programme-apply" type="submit">Apply filters</button>
        </div>
    </form>

    <div id="src-programme-results" class="src-programme-results">
        <?php
        $found = false;
        foreach (['current', 'upcoming', 'past'] as $status) :
            $items = array_values(array_filter($all, static function ($item) use ($status, $selected_status, $selected_theme, $signature_only) {
                if (get_post_meta($item->ID, '_programme_status', true) !== $status) return false;
                if ($selected_status !== 'all' && $selected_status !== $status) return false;
                if ($signature_only && get_post_meta($item->ID, '_programme_type', true) !== 'signature') return false;
                if ($selected_theme && !has_term($selected_theme, 'programme_theme', $item)) return false;
                return true;
            }));
            if (!$items) continue;
            $found = true;
            [$eyebrow, $title, $intro] = $group_labels[$status];
            ?>
            <section class="src-programme-section <?php echo $status === 'past' ? 'src-programme-section-past' : ''; ?>" id="<?php echo esc_attr($status); ?>">
                <div class="container">
                    <header class="src-programme-section-heading">
                        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
                        <h2 class="display display-md"><?php echo esc_html($title); ?></h2>
                        <p><?php echo esc_html($intro); ?></p>
                    </header>
                    <div class="src-v3-programme-grid">
                        <?php foreach ($items as $programme) : setup_postdata($programme); get_template_part('template-parts/programme-card', null, ['programme' => $programme]); endforeach; wp_reset_postdata(); ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
        <?php if (!$found) : ?>
            <section class="src-programme-empty container" role="status">
                <p class="eyebrow">No results</p><h2 class="display display-md">No programmes match these filters.</h2>
                <p>Try changing the programme status or theme to see more opportunities.</p>
                <a class="button" href="<?php echo esc_url(get_post_type_archive_link('programme')); ?>">Clear filters</a>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
