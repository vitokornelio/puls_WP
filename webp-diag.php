<?php
/**
 * WebP Diagnostics — tdpuls.com
 * Temporary script, delete after use!
 */
if ($_GET['key'] !== 'tdp2026webp') { http_response_code(404); exit; }

require_once(__DIR__ . '/wp-load.php');

header('Content-Type: text/plain; charset=utf-8');

echo "=== WebP Diagnostics ===\n\n";

// 1. PHP image support
echo "--- PHP Image Support ---\n";
echo "GD loaded: " . (extension_loaded('gd') ? 'YES' : 'NO') . "\n";
if (extension_loaded('gd')) {
    $gd = gd_info();
    echo "GD WebP: " . ($gd['WebP Support'] ?? 'NO') . "\n";
    echo "GD version: " . ($gd['GD Version'] ?? '?') . "\n";
}
echo "Imagick loaded: " . (extension_loaded('imagick') ? 'YES' : 'NO') . "\n";
if (extension_loaded('imagick')) {
    $formats = \Imagick::queryFormats('WEBP');
    echo "Imagick WebP: " . (!empty($formats) ? 'YES' : 'NO') . "\n";
}

// 2. ShortPixel status
echo "\n--- ShortPixel Plugin ---\n";
$sp_active = is_plugin_active('shortpixel-image-optimiser/wp-shortpixel.php');
echo "Active: " . ($sp_active ? 'YES' : 'NO') . "\n";
if ($sp_active) {
    $sp_settings = get_option('wp-short-pixel-options');
    if ($sp_settings) {
        echo "API Key set: " . (!empty($sp_settings['apiKey']) ? 'YES (hidden)' : 'NO') . "\n";
        echo "WebP creation: " . (!empty($sp_settings['createWebp']) ? 'YES' : 'NO') . "\n";
        echo "WebP delivery: " . (!empty($sp_settings['deliverWebp']) ? $sp_settings['deliverWebp'] : 'OFF') . "\n";
        echo "Compression: " . (!empty($sp_settings['compressionType']) ? $sp_settings['compressionType'] : '?') . "\n";
    }
}

// Check other image optimization plugins
$other_plugins = [
    'imagify/imagify.php' => 'Imagify',
    'wp-smushit/wp-smush.php' => 'Smush',
    'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW',
    'webp-converter-for-media/webp-converter-for-media.php' => 'WebP Converter',
    'webp-express/webp-express.php' => 'WebP Express',
];
echo "\n--- Other Image Plugins ---\n";
foreach ($other_plugins as $path => $name) {
    if (is_plugin_active($path)) echo "$name: ACTIVE\n";
}

// 3. Count images in uploads
echo "\n--- Upload Stats ---\n";
$upload_dir = wp_upload_dir();
$base = $upload_dir['basedir'];
echo "Uploads dir: $base\n";

$jpg_count = 0; $webp_count = 0; $png_count = 0;
$jpg_size = 0; $webp_size = 0;
$sample_jpgs = [];

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iter as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $jpg_count++;
        $jpg_size += $file->getSize();
        if (count($sample_jpgs) < 5) $sample_jpgs[] = str_replace($base, '', $file->getPathname());
    } elseif ($ext === 'webp') {
        $webp_count++;
        $webp_size += $file->getSize();
    } elseif ($ext === 'png') {
        $png_count++;
    }
}

echo "JPG/JPEG: $jpg_count (" . round($jpg_size / 1024 / 1024, 1) . " MB)\n";
echo "WebP: $webp_count (" . round($webp_size / 1024 / 1024, 1) . " MB)\n";
echo "PNG: $png_count\n";

echo "\nSample JPGs:\n";
foreach ($sample_jpgs as $s) echo "  $s\n";

// 4. Check if WebP versions exist alongside JPGs
echo "\n--- WebP Companion Check ---\n";
$checked = 0; $has_webp = 0;
$iter2 = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iter2 as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (($ext === 'jpg' || $ext === 'jpeg') && $checked < 20) {
        $checked++;
        $webp_path = preg_replace('/\.(jpe?g)$/i', '.webp', $file->getPathname());
        $webp_path2 = $file->getPathname() . '.webp'; // some plugins append .webp
        if (file_exists($webp_path) || file_exists($webp_path2)) {
            $has_webp++;
        }
    }
}
echo "Checked $checked JPGs, $has_webp have WebP companion files\n";

// 5. Check ShortPixel WebP directory
$sp_webp_dir = $base . '/../shortpixel-backups';
$sp_webp_dir2 = WP_CONTENT_DIR . '/uploads/ShortpixelWebP';
echo "\n--- ShortPixel WebP Dirs ---\n";
echo "SP Backups: " . (is_dir($sp_webp_dir) ? 'EXISTS' : 'NOT FOUND') . "\n";
echo "SP WebP dir: " . (is_dir($sp_webp_dir2) ? 'EXISTS' : 'NOT FOUND') . "\n";

// Check for any webp directory
$possible_webp_dirs = [
    WP_CONTENT_DIR . '/webp-express',
    WP_CONTENT_DIR . '/uploads-webpc',
    $base . '/ShortpixelWebP',
];
foreach ($possible_webp_dirs as $d) {
    if (is_dir($d)) echo "Found: $d\n";
}

// 6. WooCommerce product image stats
echo "\n--- WooCommerce Product Images ---\n";
$products = wc_get_products(['limit' => -1, 'status' => 'publish']);
$total_products = count($products);
$products_with_images = 0;
$total_gallery = 0;
foreach ($products as $p) {
    if ($p->get_image_id()) $products_with_images++;
    $total_gallery += count($p->get_gallery_image_ids());
}
echo "Published products: $total_products\n";
echo "With main image: $products_with_images\n";
echo "Gallery images total: $total_gallery\n";
echo "Total product images: " . ($products_with_images + $total_gallery) . "\n";

// 7. Server info
echo "\n--- Server ---\n";
echo "PHP: " . phpversion() . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";
echo "Memory limit: " . ini_get('memory_limit') . "\n";
echo "Max exec time: " . ini_get('max_execution_time') . "s\n";

echo "\n=== DONE ===\n";
