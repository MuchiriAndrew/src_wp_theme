<?php
if (!defined('ABSPATH')) exit;

/** Starter content copied from the SRC v3 export; editors own these records after seeding. */
function src_v3_pool_expert_reference_content() {
    return [
        'technical-value-chain' => [
            ['Dairy Technology', 'Milk handling and hygiene, smallholder dairy cooperative management, dairy processing enterprise development, SGS and KDB compliance.'],
            ['Poultry Production', 'Broiler and layer enterprise management, hatchery systems development, feed formulation, disease management and market linkages.'],
            ['Apiculture', 'Modern beekeeping enterprise development, honey value addition, wax and propolis processing, market linkage facilitation.'],
            ['Fish Farming', 'Aquaculture enterprise development, pond and cage management, fingerling sourcing, value addition and off-take facilitation.'],
            ['Animal Nutrition', 'Feed formulation and nutritional analysis, pasture management, supplementary feeding strategies for ASALs.'],
            ['Food Processing & QA', 'HACCP implementation, food safety management systems, KEBS standards compliance, processing technology advisory.'],
            ['Environmental Impact Assessment', 'EIA studies, NEMA licensing, environmental management plans, climate adaptation for agricultural enterprises.'],
        ],
        'financial-governance' => [
            ['Financial Management', 'SME financial systems design, bookkeeping, cashflow management, financial reporting and management accounts.'],
            ['Tax & Compliance', 'KRA registration and compliance, VAT, PAYE, corporate tax advisory, iTax platform support for SMEs.'],
            ['HR & Governance', 'HR policy development, performance management systems, cooperative governance, board effectiveness and SACCO regulation.'],
            ['Investor Readiness', 'Investment memoranda, financial model development, pitch coaching, due diligence preparation for debt and equity instruments.'],
        ],
        'marketing-growth' => [
            ['Market Research', 'Market assessments, consumer research, competitive analysis, feasibility studies and opportunity scoping for SMEs.'],
            ['Branding & Creative', 'Brand strategy, visual identity, packaging design, marketing collateral and brand positioning for emerging enterprises.'],
            ['Social Media & Video', 'Digital marketing strategy, social media management, video production and content creation for SME platforms.'],
            ['Trade Marketing', 'Retail and trade channel development, route-to-market strategy, trade promotion and modern trade activation.'],
            ['Supply Chain', 'Supply chain mapping, logistics optimisation, procurement systems, last-mile distribution strategy.'],
        ],
        'compliance-safety' => [
            ['Occupational Health & Safety', 'Workplace safety systems design, OSHA compliance, risk assessments, safety audits and employee wellness programmes.'],
            ['Environmental Impact Assessment', 'NEMA EIA licensing, environmental audits, waste management plans, water abstraction permits and climate risk advisory.'],
        ],
    ];
}

function src_v3_expert_category_order_field() {
    echo '<div class="form-field"><label for="expert_category_order">Display order</label><input type="number" min="0" step="1" name="expert_category_order" id="expert_category_order" value=""><p>Lower numbers appear first in the category filters.</p></div>';
}
add_action('expert_category_add_form_fields', 'src_v3_expert_category_order_field');

function src_v3_expert_category_order_edit_field($term) {
    $value = get_term_meta($term->term_id, '_expert_category_order', true);
    echo '<tr class="form-field"><th scope="row"><label for="expert_category_order">Display order</label></th><td><input type="number" min="0" step="1" name="expert_category_order" id="expert_category_order" value="' . esc_attr($value) . '"><p class="description">Lower numbers appear first in the category filters.</p></td></tr>';
}
add_action('expert_category_edit_form_fields', 'src_v3_expert_category_order_edit_field');

function src_v3_save_expert_category_order($term_id) {
    if (!current_user_can('manage_categories') || !isset($_POST['expert_category_order'])) return;
    update_term_meta($term_id, '_expert_category_order', max(0, absint(wp_unslash($_POST['expert_category_order']))));
}
add_action('created_expert_category', 'src_v3_save_expert_category_order');
add_action('edited_expert_category', 'src_v3_save_expert_category_order');

function src_v3_seed_pool_experts() {
    if (get_option('src_pool_experts_seed_v3') === '1') return;

    $category_names = [
        'technical-value-chain' => 'Technical / Value-chain',
        'financial-governance' => 'Financial & Governance',
        'marketing-growth' => 'Marketing & Growth',
        'compliance-safety' => 'Compliance & Safety',
    ];
    $groups = src_v3_pool_expert_reference_content();
    foreach ($category_names as $index => $label) {
        $term = get_term_by('slug', $index, 'expert_category');
        if (!$term) {
            $created = wp_insert_term($label, 'expert_category', ['slug' => $index]);
            if (!is_wp_error($created)) $term = get_term($created['term_id'], 'expert_category');
        }
        if (!$term || is_wp_error($term)) continue;
        if (get_term_meta($term->term_id, '_expert_category_order', true) === '') {
            update_term_meta($term->term_id, '_expert_category_order', array_search($index, array_keys($category_names), true) + 1);
        }

        foreach ($groups[$index] ?? [] as $position => [$title, $description]) {
            $slug = sanitize_title($index . '-' . $title);
            if (get_page_by_path($slug, OBJECT, 'src_expert')) continue;
            $post_id = wp_insert_post([
                'post_type' => 'src_expert',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => $description,
                'menu_order' => $position + 1,
            ], true);
            if (is_wp_error($post_id)) continue;
            update_post_meta($post_id, '_expert_role', $title);
            update_post_meta($post_id, '_expert_summary', $description);
            update_post_meta($post_id, '_expert_order', $position + 1);
            update_post_meta($post_id, '_expert_request_label', 'Request this expert →');
            wp_set_object_terms($post_id, [$index], 'expert_category');
        }
    }
    update_option('src_pool_experts_seed_v3', '1');
}
add_action('init', 'src_v3_seed_pool_experts', 34);
