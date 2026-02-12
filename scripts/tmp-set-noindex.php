<?php
if ($_GET['key'] !== 'tdp2026vsuzi') { http_response_code(404); exit; }
require_once __DIR__ . '/wp-load.php';

$page_id = 12083;

// Set Rank Math robots to noindex, nofollow
update_post_meta($page_id, 'rank_math_robots', ['noindex', 'nofollow']);

// Verify
$robots = get_post_meta($page_id, 'rank_math_robots', true);
echo 'Page ID: ' . $page_id . "\n";
echo 'Rank Math robots: ' . print_r($robots, true) . "\n";
echo 'Done. Page is now noindex.';
