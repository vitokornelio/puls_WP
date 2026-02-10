<?php
/**
 * Temporary script: rename КОМПЬЮТЕРНЫЙ ТОМОГРАФ products to КТ
 * Delete immediately after use!
 */
if ($_GET['key'] !== 'rNm8xQ2v') {
    http_response_code(404);
    exit;
}

$dry_run = isset($_GET['dry']) && $_GET['dry'] === '1';

require_once(__DIR__ . '/wp-load.php');

// Get all products with КОМПЬЮТЕРНЫЙ ТОМОГРАФ in title
$args = [
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    's'              => 'компьютерный томограф',
];

$query = new WP_Query($args);
$results = [];

foreach ($query->posts as $post) {
    $old_title = $post->post_title;

    // Only process if title actually contains компьютерный томограф (case-insensitive)
    if (mb_stripos($old_title, 'компьютерный томограф') === false) {
        continue;
    }

    // Replace "— компьютерный томограф" or "— КОМПЬЮТЕРНЫЙ ТОМОГРАФ" with "— КТ"
    $new_title = preg_replace('/\s*[—–]\s*компьютерный\s+томограф\s*$/ui', ' — КТ', $old_title);
    $new_title = preg_replace('/\s*-\s*компьютерный\s+томограф\s*$/ui', ' — КТ', $new_title);

    // Format model part (before " — КТ")
    $parts = explode(' — КТ', $new_title, 2);
    $model_raw = trim($parts[0]);

    // Apply smart title case to model
    $model = format_model_for_rename($model_raw);

    $new_title = $model . ' — КТ';

    if ($new_title === $old_title) {
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

    // Update only post_title, NOT the slug
    $update_result = wp_update_post([
        'ID'         => $post->ID,
        'post_title' => $new_title,
        'post_name'  => $post->post_name, // Keep slug unchanged!
    ], true);

    if (is_wp_error($update_result)) {
        $results[] = [
            'id'    => $post->ID,
            'error' => $update_result->get_error_message(),
            'old'   => $old_title,
        ];
    } else {
        // Verify slug wasn't changed
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
    'total'   => count($results),
    'results' => $results,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

/**
 * Smart title case for model names
 */
function format_model_for_rename($str) {
    $str = preg_replace('/^GENERAL\s+ELECTRIC\b/iu', 'GE', $str);

    $tokens = preg_split('/(\s+)/u', $str, -1, PREG_SPLIT_DELIM_CAPTURE);
    $result = [];

    foreach ($tokens as $token) {
        if (preg_match('/^\s+$/u', $token)) {
            $result[] = $token;
            continue;
        }
        $result[] = format_word($token);
    }

    return implode('', $result);
}

function format_word($word) {
    $abbrevs = ['GE', 'MR', 'CT', 'MRI', 'PET', 'HP', 'HD', 'AI', 'AIR', 'BT', 'RT', 'DR', 'CR'];
    $upper = mb_strtoupper($word, 'UTF-8');
    if (in_array($upper, $abbrevs)) {
        return $upper;
    }

    if (preg_match('/\d/', $word) || preg_match('/^[A-Za-z]+\.[A-Za-z]/u', $word)) {
        return $word;
    }

    if (preg_match('/^[A-Z]{2,4}$/', $word)) {
        return $word;
    }

    if (preg_match('/^[A-Z]{5,}$/', $word)) {
        return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
             . mb_strtolower(mb_substr($word, 1, null, 'UTF-8'), 'UTF-8');
    }

    if (preg_match('/^[A-Z][a-z]/', $word)) {
        return $word;
    }

    return $word;
}
