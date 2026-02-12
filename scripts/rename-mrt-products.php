<?php
/**
 * Temporary script: rename МР-ТОМОГРАФ products
 * Delete immediately after use!
 */
if ($_GET['key'] !== 'rNm8xQ2v') {
    http_response_code(404);
    exit;
}

require_once(__DIR__ . '/wp-load.php');

// Get all products with МР-ТОМОГРАФ in title
$args = [
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    's'              => 'МР-ТОМОГРАФ',
];

$query = new WP_Query($args);
$results = [];

foreach ($query->posts as $post) {
    $old_title = $post->post_title;

    // Only process if title actually contains МР-ТОМОГРАФ
    if (mb_stripos($old_title, 'МР-ТОМОГРАФ') === false) {
        continue;
    }

    // Split into model and type parts
    // Handle both "— МР-ТОМОГРАФ" and "- МР-ТОМОГРАФ"
    $new_title = preg_replace('/\s*[—–]\s*МР-ТОМОГРАФ\s*$/ui', ' — МРТ', $old_title);
    $new_title = preg_replace('/\s*-\s*МР-ТОМОГРАФ\s*$/ui', ' — МРТ', $new_title);

    // Format model part (before " — МРТ")
    $parts = explode(' — МРТ', $new_title, 2);
    $model_raw = trim($parts[0]);

    // Apply smart title case to model
    $model = format_model_for_rename($model_raw);

    $new_title = $model . ' — МРТ';

    if ($new_title === $old_title) {
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
    'total'   => count($results),
    'results' => $results,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

/**
 * Smart title case for model names
 * Mirrors tdp_format_model() logic from functions.php
 */
function format_model_for_rename($str) {
    // GENERAL ELECTRIC → GE
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
    // Known abbreviations - keep uppercase
    $abbrevs = ['GE', 'MR', 'CT', 'MRI', 'PET', 'HP', 'HD', 'AI', 'AIR', 'BT', 'RT', 'DR', 'CR'];
    $upper = mb_strtoupper($word, 'UTF-8');
    if (in_array($upper, $abbrevs)) {
        return $upper;
    }

    // Contains digits or dots - keep as is (model numbers like 1.5T, 3.0T, Free.Max)
    if (preg_match('/\d/', $word) || preg_match('/^[A-Za-z]+\.[A-Za-z]/u', $word)) {
        return $word;
    }

    // All uppercase Latin, 2-4 chars - likely abbreviation, keep
    if (preg_match('/^[A-Z]{2,4}$/', $word)) {
        return $word;
    }

    // Brand names to keep: PHILIPS, SIEMENS, MAGNETOM, SIGNA, INGENIA, ELITION, etc.
    // For Latin words in all caps with 5+ letters — Title Case
    if (preg_match('/^[A-Z]{5,}$/', $word)) {
        return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
             . mb_strtolower(mb_substr($word, 1, null, 'UTF-8'), 'UTF-8');
    }

    // Mixed case (like "Pioneer", "Free") - keep as is
    if (preg_match('/^[A-Z][a-z]/', $word)) {
        return $word;
    }

    // All other words - keep as is
    return $word;
}
