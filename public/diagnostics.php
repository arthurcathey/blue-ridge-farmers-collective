<?php

/**
 * Image Processing Diagnostics
 * Check server capabilities for image optimization
 * 
 * Access at: /diagnostics.php
 */

$basePath = dirname(__DIR__);
require $basePath . '/src/Services/ImageProcessor.php';

$diagnostics = [
  'php_version' => phpversion(),
  'gd_extension' => [
    'loaded' => extension_loaded('gd'),
    'info' => extension_loaded('gd') ? gd_info() : null,
    'imagewebp_available' => function_exists('imagewebp'),
  ],
  'imagick_extension' => [
    'loaded' => extension_loaded('imagick'),
    'version' => extension_loaded('imagick') ? phpversion('imagick') : null,
  ],
  'webp_support' => [
    'supported' => \App\Services\ImageProcessor::supportsWebP(),
    'method' => function_exists('imagewebp') ? 'GD' : (extension_loaded('imagick') ? 'ImageMagick' : 'None'),
  ],
  'image_functions' => [
    'getimagesize' => function_exists('getimagesize'),
    'imagecreatefromjpeg' => function_exists('imagecreatefromjpeg'),
    'imagecreatefrompng' => function_exists('imagecreatefrompng'),
    'imagecreatefrompng' => function_exists('imagecreatefrompng'),
    'imagewebp' => function_exists('imagewebp'),
    'imagecopyresampled' => function_exists('imagecopyresampled'),
  ],
  'upload_temp_dir' => sys_get_temp_dir(),
  'memory_limit' => ini_get('memory_limit'),
  'upload_max_filesize' => ini_get('upload_max_filesize'),
];

header('Content-Type: application/json');
echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
