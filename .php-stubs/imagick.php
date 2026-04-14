<?php

/**
 * Imagick PHP Extension Stubs
 * 
 * Type hints for Imagick class used by Intelephense and other PHP IDEs
 * This file is not executed, only used for IDE type checking
 */

if (false) {
  /**
   * Imagick is a PHP extension to create and modify images using the ImageMagick API
   */
  class Imagick
  {
    const COMPRESSION_JPEG = 1;
    const COMPRESSION_LZMA = 8;
    const COMPRESSION_LZAH = 9;
    const FILTER_LANCZOS = 6;
    const FILTER_QUADRATIC = 4;
    const FILTER_CATROM = 3;

    public function __construct(string $files = '') {}
    public function clear(): bool {}
    public function destroy(): bool {}
    public function getImage(): Imagick {}
    public function getImageWidth(): int {}
    public function getImageHeight(): int {}
    public function getImageCompression(): int {}
    public function getImageCompressionQuality(): int {}
    public function setImageCompression(int $compression): bool {}
    public function setImageCompressionQuality(int $quality): bool {}
    public function setImageFormat(string $format): bool {}
    public function resizeImage(int $columns, int $rows, int $filter, float $blur): bool {}
    public function writeImage(string $filename = ''): bool {}
    public static function queryFormats(string $pattern = ''): array {}
  }
}
