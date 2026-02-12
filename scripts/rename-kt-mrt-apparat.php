<?php
/**
 * Temporary script: rename "— КТ" → "— КТ аппарат" and "— МРТ" → "— МРТ аппарат"
 * Delete immediately after use!
 */
if (!isset($_GET['key']) || $_GET['key'] !== 'rNm8xQ2v') {
    http_response_code(404);
    exit;
}

$dry_run = isset($_GET['dry']) && $_GET['dry'] === '1';

require_once(__DIR__ . '/wp-load.php');

$args = [
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
];

$query = new WP_Query($args);
$results = [];
$skipped = 0;

foreach ($query->posts as $post) {
    $old_title = $post->post_title;
    $new_title = $old_title;

    // "— КТ" at end → "— КТ аппарат" (but NOT "— ОФЭКТ/КТ", "— ПЭТ/КТ", "— инжектор для КТ")
    if (preg_match('/\s—\sКТ$/u', $old_title)) {
        $new_title = preg_replace('/\s—\sКТ$/u', ' — КТ аппарат', $old_title);
    }
    // "— МРТ" at end → "— МРТ аппарат" (but NOT "— ПЭТ/МРТ", "— инжектор для МРТ/КТ")
    elseif (preg_match('/\s—\sМРТ$/u', $old_title)) {
        $new_title = preg_replace('/\s—\sМРТ$/u', ' — МРТ аппарат', $old_title);
    }

    if ($new_title === $old_title) {
        $skipped++;
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
            'id'       => $post->ID,
            'old'      => $old_title,
            'new'      => $new_title,
            'slug'     => $updated->post_name,
            'slug_ok'  => ($updated->post_name === $post->post_name),
        ];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'mode'    => $dry_run ? 'DRY RUN' : 'APPLIED',
    'changed' => count($results),
    'skipped' => $skipped,
    'results' => $results,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
