<?php
if ($_GET['key'] !== 'tdp2026webp') { http_response_code(404); exit; }

header('Content-Type: text/plain; charset=utf-8');

echo "=== WebP Test ===\n";

// Test 1: Find a sample JPG
$base = __DIR__ . '/wp-content/uploads';
echo "Base: $base\n";
echo "Exists: " . (is_dir($base) ? 'YES' : 'NO') . "\n\n";

// Find first JPG
$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base . '/2021', RecursiveDirectoryIterator::SKIP_DOTS)
);
$test_file = null;
foreach ($iter as $f) {
    if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'jpg') {
        $test_file = $f->getPathname();
        break;
    }
}

if (!$test_file) { echo "No JPG found!\n"; exit; }

echo "Test file: $test_file\n";
echo "Size: " . filesize($test_file) . " bytes\n\n";

// Test 2: Convert with Imagick
$webp_path = preg_replace('/\.jpg$/i', '.webp', $test_file);
echo "WebP target: $webp_path\n";

try {
    $img = new Imagick($test_file);
    $img->setImageFormat('webp');
    $img->setImageCompressionQuality(80);
    $img->writeImage($webp_path);
    $img->clear();
    $img->destroy();

    if (file_exists($webp_path)) {
        $ws = filesize($webp_path);
        echo "WebP created: $ws bytes\n";
        echo "Savings: " . round((1 - $ws / filesize($test_file)) * 100) . "%\n";
        // Clean up test
        unlink($webp_path);
        echo "Test file removed.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
