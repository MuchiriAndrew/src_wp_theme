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
$signature_only = false;
$themes = get_terms(['taxonomy' => 'programme_theme', 'hide_empty' => false]);
$theme_order = ['resilience-livelihoods', 'livestock-agribusiness', 'womens-enterprise', 'investment-export-readiness', 'digital-learning'];
if (!is_wp_error($themes)) {
    usort($themes, static function ($left, $right) use ($theme_order) {
        $left_order = array_search($left->slug, $theme_order, true);
        $right_order = array_search($right->slug, $theme_order, true);
        return ($left_order === false ? PHP_INT_MAX : $left_order) <=> ($right_order === false ? PHP_INT_MAX : $right_order);
    });
    if ($selected_theme && !in_array($selected_theme, wp_list_pluck($themes, 'slug'), true)) $selected_theme = '';
}
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
                <button class="button" type="button" data-scroll-programmes>View Programmes</button>
                <button class="src-v3-hero-secondary" type="button" data-scroll-programmes><span aria-hidden="true">→</span> How registration works</button>
            </div>
        </div>
    </section>

    <nav class="src-programme-filters" aria-label="Programme filters">
        <div class="src-programme-filter-scroll">
            <div class="src-programme-filter-inner">
                <div class="src-programme-filter-status" role="group" aria-label="Filter by status">
                    <?php foreach (['all' => 'All', 'current' => 'Current', 'upcoming' => 'Upcoming', 'past' => 'Past'] as $value => $label) :
                        $active = $selected_status === $value || ($selected_status === 'all' && $value === 'current'); ?>
                        <button type="button" data-programme-status="<?php echo esc_attr($value); ?>" class="src-programme-filter src-programme-filter-status-item <?php echo $active ? 'is-active' : ''; ?>"><?php echo esc_html($label); ?><span class="src-programme-filter-separator" aria-hidden="true">·</span><span class="src-programme-filter-count"><?php echo esc_html($counts[$value]); ?></span></button>
                    <?php endforeach; ?>
                </div>
                <?php if (!is_wp_error($themes)) foreach ($themes as $theme) :
                    $active = $selected_theme === $theme->slug; ?>
                    <button type="button" data-programme-theme="<?php echo esc_attr($theme->slug); ?>" data-programme-theme-name="<?php echo esc_attr($theme->name); ?>" class="src-programme-filter src-programme-filter-theme <?php echo $active ? 'is-active' : ''; ?>"><?php echo esc_html($theme->name); ?></button>
                <?php endforeach; ?>
                <button type="button" data-programme-signature class="src-programme-filter src-programme-filter-signature" aria-pressed="false">SRC Signature only</button>
            </div>
        </div>
    </nav>

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
            if ($status === 'current') {
                usort($items, static function ($left, $right) {
                    $left_open = get_post_meta($left->ID, '_programme_enrolment', true) === 'open';
                    $right_open = get_post_meta($right->ID, '_programme_enrolment', true) === 'open';
                    if ($left_open !== $right_open) return $left_open ? -1 : 1;
                    return (int) $left->menu_order <=> (int) $right->menu_order;
                });
            }
            $found = true;
            [$eyebrow, $title, $intro] = $group_labels[$status];
            ?>
            <section class="src-programme-section <?php echo $status === 'past' ? 'src-programme-section-past' : ''; ?>" id="<?php echo esc_attr($status); ?>" data-programme-section="<?php echo esc_attr($status); ?>">
                <div class="container">
                    <header class="src-programme-section-heading">
                        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
                        <h2 class="display display-md"><?php echo esc_html($title); ?></h2>
                        <p><?php echo esc_html($intro); ?></p>
                    </header>
                    <div class="src-v3-programme-grid">
                        <?php
                        $items_total = count($items);
                        foreach ($items as $item_index => $programme) :
                            $span_class = '';
                            if ($item_index === $items_total - 1) {
                                if ($items_total % 2 === 1) $span_class .= ' src-v3-programme-card-span-md-2';
                                $xl_span = [1 => 4, 2 => 3, 3 => 2][$items_total % 4] ?? 0;
                                if ($xl_span) $span_class .= ' src-v3-programme-card-span-xl-' . $xl_span;
                            }
                            setup_postdata($programme);
                            get_template_part('template-parts/programme-card', null, ['programme' => $programme, 'span_class' => $span_class]);
                        endforeach;
                        wp_reset_postdata(); ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
        <section class="src-programme-empty container" role="status" <?php echo $found ? 'hidden' : ''; ?>>
            <p>No programmes match these filters.</p>
        </section>
    </div>
</main>
<?php get_footer(); ?>
