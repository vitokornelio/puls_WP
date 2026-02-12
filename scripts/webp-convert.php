<?php
/**
 * Batch JPG/PNG → WebP converter for tdpuls.com
 * Uses offset/batch for pagination. Run multiple times.
 * DELETE after use!
 */
if ($_GET['key'] !== 'tdp2026webp') { http_response_code(404); exit; }

set_time_limit(55);
$start = microtime(true);
$max_time = 45;
$batch_size = intval($_GET['batch'] ?? 100);
$quality = intval($_GET['quality'] ?? 80);
$offset = intval($_GET['offset'] ?? 0);

header('Content-Type: application/json; charset=utf-8');

$base = __DIR__ . '/wp-content/uploads';

$converted = 0;
$skipped = 0;
$errors = [];
$saved_bytes = 0;
$current_index = 0;
$processed_in_batch = 0;
$last_file = '';

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iter as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) continue;

    // Skip until offset
    if ($current_index < $offset) {
        $current_index++;
        continue;
    }

    // Batch limit
    if ($processed_in_batch >= $batch_size) break;

    // Time limit
    if ((microtime(true) - $start) > $max_time) break;

    $filepath = $file->getPathname();
    $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $filepath);
    $last_file = str_replace($base, '', $filepath);

    // Skip if WebP already exists
    if (file_exists($webp_path)) {
        $skipped++;
        $processed_in_batch++;
        $current_index++;
        continue;
    }

    try {
        $original_size = $file->getSize();

        $img = new Imagick($filepath);
        $img->setImageFormat('webp');
        $img->setImageCompressionQuality($quality);
        $img->setOption('webp:method', '4');
        $img->writeImage($webp_path);
        $img->clear();
        $img->destroy();

        if (file_exists($webp_path)) {
            $webp_size = filesize($webp_path);
            if ($webp_size >= $original_size) {
                unlink($webp_path);
                $skipped++;
            } else {
                $saved_bytes += ($original_size - $webp_size);
                $converted++;
            }
        }
    } catch (Exception $e) {
        $errors[] = basename($filepath) . ': ' . $e->getMessage();
        if (count($errors) > 10) break;
    }

    $processed_in_batch++;
    $current_index++;
}

$elapsed = round(microtime(true) - $start, 1);
$next_offset = $offset + $processed_in_batch;

echo json_encode([
    'status' => 'ok',
    'offset' => $offset,
    'processed' => $processed_in_batch,
    'converted' => $converted,
    'skipped' => $skipped,
    'errors' => count($errors),
    'error_details' => array_slice($errors, 0, 3),
    'saved_mb' => round($saved_bytes / 1024 / 1024, 2),
    'elapsed_sec' => $elapsed,
    'next_offset' => $next_offset,
    'last_file' => $last_file,
], JSON_PRETTY_PRINT);
