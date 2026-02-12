<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'tdp2026cc') {
    http_response_code(404);
    exit;
}

// Рекурсивное удаление директории
function rrmdir($dir) {
    if (!is_dir($dir)) return 0;
    $count = 0;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        if ($item->isDir()) {
            rmdir($item->getRealPath());
        } else {
            unlink($item->getRealPath());
            $count++;
        }
    }
    rmdir($dir);
    return $count;
}

$cache_dir = __DIR__ . '/wp-content/cache/all/';
$minified_dir = __DIR__ . '/wp-content/cache/wpfc-minified/';

$deleted = 0;
if (is_dir($cache_dir)) {
    $deleted += rrmdir($cache_dir);
    @mkdir($cache_dir, 0755);
}
if (is_dir($minified_dir)) {
    $deleted += rrmdir($minified_dir);
    @mkdir($minified_dir, 0755);
}

echo json_encode(['status' => 'ok', 'deleted' => $deleted]);
