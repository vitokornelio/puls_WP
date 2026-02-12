<?php
/**
 * Temporary script: rename ALL products — Title Case + GENERAL ELECTRIC→GE + SEO type mapping
 * Uses tdp_format_model() and tdp_format_equipment_type() from functions.php
 * Delete immediately after use!
 */
if ($_GET['key'] !== 'rNm8xQ2v') {
    http_response_code(404);
    exit;
}

$dry_run = isset($_GET['dry']) && $_GET['dry'] === '1';

require_once(__DIR__ . '/wp-load.php');

// Get ALL published products
$args = [
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
];

$query = new WP_Query($args);
$results = [];
$skipped = [];

foreach ($query->posts as $post) {
    $old_title = $post->post_title;

    // Skip already-renamed МРТ products
    if (preg_match('/— МРТ$/u', $old_title)) {
        continue;
    }

    $new_title = rename_product_title($old_title);

    if ($new_title === $old_title) {
        $skipped[] = ['id' => $post->ID, 'title' => $old_title, 'reason' => 'no_change'];
        continue;
    }

    if ($dry_run) {
        $results[] = [
            'id'   => $post->ID,
            'old'  => $old_title,
            'new'  => $new_title,
            'slug' => $post->post_name,
        ];
        continue;
    }

    // Update only post_title, keep slug!
    $update_result = wp_update_post([
        'ID'         => $post->ID,
        'post_title' => $new_title,
        'post_name'  => $post->post_name,
    ], true);

    if (is_wp_error($update_result)) {
        $results[] = [
            'id'    => $post->ID,
            'error' => $update_result->get_error_message(),
            'old'   => $old_title,
        ];
    } else {
        $updated = get_post($post->ID);
        $results[] = [
            'id'      => $post->ID,
            'old'     => $old_title,
            'new'     => $new_title,
            'slug'    => $updated->post_name,
            'slug_ok' => ($updated->post_name === $post->post_name),
        ];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'mode'    => $dry_run ? 'DRY RUN' : 'APPLIED',
    'changed' => count($results),
    'skipped' => count($skipped),
    'results' => $results,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);


function rename_product_title($title) {
    // Normalize dashes: – (en-dash) and various dash+space patterns
    $title = str_replace('&#8212;', '—', $title);
    $title = str_replace('&#8211;', '–', $title);

    // Split into model and type by — or – or "- " before uppercase
    $parts = preg_split('/\s*[—–]\s*|\s*-\s+(?=[А-ЯЁA-Z])/u', $title, 2);
    $model_raw = trim($parts[0]);
    $type_raw = isset($parts[1]) ? trim($parts[1]) : '';

    // Format model part
    $model = tdp_format_model($model_raw);

    if (!$type_raw) {
        // No type separator — just format model
        return $model;
    }

    // Format type using the mapping from functions.php
    $type = tdp_format_equipment_type($type_raw);

    return $model . ' — ' . $type;
}
