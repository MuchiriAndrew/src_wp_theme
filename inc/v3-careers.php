<?php
if (!defined('ABSPATH')) exit;

function src_v3_ensure_careers_page() {
    $stored_page_id = absint(get_option('src_careers_page_id_v3'));
    if ($stored_page_id && get_post_type($stored_page_id) === 'page') return;
    $page = get_page_by_path('careers');
    if (!$page) {
        $page_id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Careers',
            'post_name' => 'careers',
            'post_content' => '',
        ], true);
        if (!is_wp_error($page_id) && $page_id) {
            update_post_meta($page_id, '_wp_page_template', 'page-careers.php');
            $page = get_post($page_id);
        }
    }
    if ($page && $page->post_type === 'page') {
        update_option('src_careers_page_id_v3', (int) $page->ID);
        update_option('src_careers_page_created_v3', '1');
    }
}
add_action('init', 'src_v3_ensure_careers_page', 1);

function src_v3_flush_careers_rewrite_once() {
    if (get_option('src_careers_rewrite_flushed_v3') === '1') return;
    flush_rewrite_rules(false);
    update_option('src_careers_rewrite_flushed_v3', '1');
}
add_action('init', 'src_v3_flush_careers_rewrite_once', 31);

function src_v3_careers_reference_jobs() {
    return [
        [
            'title' => 'Enterprise Development Advisor',
            'location' => 'Nairobi, Kenya', 'type' => 'Full-time', 'department' => 'Programmes',
            'description' => "Lead enterprise support delivery for one of SRC's donor-funded programmes, providing technical assistance to SMEs and cooperatives across assigned counties.",
            'requirements' => [
                'Minimum 5 years in enterprise development, BDS or value chain programmes',
                'Experience with SME diagnostics, business planning and financial management',
                'Strong facilitation and training skills',
                'East Africa field experience preferred',
            ],
        ],
        [
            'title' => 'Agribusiness Consultant — Livestock Systems',
            'location' => 'Arid Counties (field-based)', 'type' => 'Contract', 'department' => 'Technical Pool',
            'description' => "Provide technical advisory on livestock value chains — dairy, meat processing, feedlot or livestock markets — for SRC programmes operating in Kenya's arid and semi-arid counties.",
            'requirements' => [
                'Degree in livestock science, animal production or agricultural economics',
                'Field experience in ASAL counties (Turkana, Marsabit, Garissa, Wajir or similar)',
                'Knowledge of pastoralist systems and livestock market structures',
                'Willingness to travel extensively',
            ],
        ],
        [
            'title' => 'Finance & Grants Manager',
            'location' => 'Nairobi, Kenya', 'type' => 'Full-time', 'department' => 'Finance',
            'description' => "Manage financial reporting, grants compliance and budgeting for SRC's donor-funded programme portfolio, including USAID, FCDO and foundation-funded grants.",
            'requirements' => [
                'CPA (K) or equivalent professional qualification',
                'Minimum 4 years in NGO or donor-funded programme finance',
                'Experience with USAID or FCDO financial reporting requirements',
                'Strong Excel, QuickBooks or accounting system skills',
            ],
        ],
        [
            'title' => 'Monitoring, Evaluation & Learning Officer',
            'location' => 'Nairobi, Kenya', 'type' => 'Full-time', 'department' => 'MEL',
            'description' => "Design and manage MEL systems for SRC's enterprise support programmes — data collection, indicator tracking, learning synthesis and donor reporting.",
            'requirements' => [
                'Degree in statistics, social science, development studies or related field',
                'Minimum 3 years in MEL for NGO or donor programmes',
                'Proficiency in KoBoToolbox, ODK or similar data collection platforms',
                'Strong quantitative and qualitative data analysis skills',
            ],
        ],
        [
            'title' => 'Gender & Inclusion Specialist',
            'location' => 'Nairobi, Kenya', 'type' => 'Contract', 'department' => 'Programmes',
            'description' => "Lead gender and inclusion mainstreaming across SRC's enterprise support programmes — including women's economic empowerment, youth engagement and disability inclusion.",
            'requirements' => [
                'Minimum 4 years in gender programming for enterprise or livelihoods programmes',
                "Experience with women's financial inclusion, HerVenture or similar platforms an advantage",
                'Strong training and facilitation skills',
                'Kenyan and East African context knowledge',
            ],
        ],
    ];
}

function src_v3_seed_careers() {
    if (get_option('src_careers_seed_v3') === '1') return;
    foreach (src_v3_careers_reference_jobs() as $index => $job) {
        $slug = sanitize_title($job['title']);
        if (get_page_by_path($slug, OBJECT, 'job')) continue;
        $post_id = wp_insert_post([
            'post_type' => 'job',
            'post_status' => 'publish',
            'post_title' => $job['title'],
            'post_name' => $slug,
            'post_content' => $job['description'],
            'post_excerpt' => $job['description'],
            'menu_order' => $index + 1,
        ], true);
        if (is_wp_error($post_id)) continue;
        update_post_meta($post_id, '_job_location', $job['location']);
        update_post_meta($post_id, '_job_type', $job['type']);
        update_post_meta($post_id, '_job_department', $job['department']);
        update_post_meta($post_id, '_job_order', $index + 1);
        update_post_meta($post_id, '_job_requirements', implode("\n", $job['requirements']));
        if (!term_exists($job['department'], 'job_department')) wp_insert_term($job['department'], 'job_department');
        wp_set_object_terms($post_id, [$job['department']], 'job_department');
    }
    update_option('src_careers_seed_v3', '1');
}
add_action('init', 'src_v3_seed_careers', 35);

function src_careers_application_handler() {
    $careers_page = get_page_by_path('careers');
    $redirect = $careers_page ? get_permalink($careers_page) : home_url('/careers/');
    $job_id = isset($_POST['job_id']) && is_scalar($_POST['job_id']) ? absint(wp_unslash($_POST['job_id'])) : 0;
    $job = $job_id ? get_post($job_id) : null;
    $job = $job && $job->post_type === 'job' && $job->post_status === 'publish' ? $job : null;
    $redirect = add_query_arg('job', $job ? $job->ID : '', $redirect);
    $read = static function ($key) {
        return isset($_POST[$key]) && is_scalar($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : '';
    };

    if (!isset($_POST['src_careers_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['src_careers_nonce'])), 'src_careers_application')) {
        wp_safe_redirect(add_query_arg('application_status', 'error', $redirect));
        exit;
    }

    $name = $read('name');
    $email = sanitize_email($read('email'));
    $phone = $read('phone');
    $cover_letter = isset($_POST['cover_letter']) && is_scalar($_POST['cover_letter']) ? sanitize_textarea_field(wp_unslash($_POST['cover_letter'])) : '';
    $recipient = $careers_page ? sanitize_email(get_post_meta($careers_page->ID, '_page_application_email', true)) : '';
    if (!is_email($recipient)) $recipient = sanitize_email(get_option('admin_email'));
    $sent = false;
    $attachments = [];
    $uploaded_file = '';
    if (!empty($_FILES['cv_resume']['name'])) {
        $file = $_FILES['cv_resume'];
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed_extensions, true) || (int) $file['size'] > 10 * MB_IN_BYTES || (int) $file['error'] !== UPLOAD_ERR_OK) {
            wp_safe_redirect(add_query_arg('application_status', 'error', $redirect));
            exit;
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $upload = wp_handle_upload($file, ['test_form' => false, 'mimes' => ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']]);
        if (!empty($upload['error']) || empty($upload['file'])) {
            wp_safe_redirect(add_query_arg('application_status', 'error', $redirect));
            exit;
        }
        $uploaded_file = $upload['file'];
        $attachments[] = $uploaded_file;
    } elseif (isset($_FILES['cv_resume']['error']) && (int) $_FILES['cv_resume']['error'] !== UPLOAD_ERR_NO_FILE) {
        wp_safe_redirect(add_query_arg('application_status', 'error', $redirect));
        exit;
    }
    if ($job && $name !== '' && is_email($email) && is_email($recipient)) {
        $mail_subject = 'SRC website application: ' . $job->post_title;
        $mail_body = "Position: {$job->post_title}\nName: {$name}\nEmail: {$email}\nPhone: {$phone}\n\nCover letter:\n{$cover_letter}";
        $sent = wp_mail($recipient, $mail_subject, $mail_body, ['Reply-To: ' . $name . ' <' . $email . '>'], $attachments);
    }
    if ($uploaded_file && file_exists($uploaded_file)) wp_delete_file($uploaded_file);
    wp_safe_redirect(add_query_arg('application_status', $sent ? 'sent' : 'error', $redirect));
    exit;
}
add_action('admin_post_src_careers_application', 'src_careers_application_handler');
add_action('admin_post_nopriv_src_careers_application', 'src_careers_application_handler');
