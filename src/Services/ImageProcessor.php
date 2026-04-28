<?php

namespace App\Services;

/**
 * ImageProcessor - WebP Image Conversion Service
 * 
 * Converts uploaded images to WebP format for optimized delivery
 * Maintains original format as fallback
 * 
 * Requirements:
 * - PHP GD extension (built-in, usually available)
 * - OR ImageMagick extension (recommended for better quality)
 * 
 * @package App\Services
 */
class ImageProcessor
{
  /**
   * Convert image to WebP format
   * 
   * Creates a WebP version of the image while maintaining the original.
   * Falls back gracefully if WebP conversion is not available.
   * 
   * @param string $imagePath Path to the uploaded image file
   * @param int $quality JPEG quality (0-100, default 85)
   * @param int $maxWidth Maximum width for resizing (optional)
   * @param int $maxHeight Maximum height for resizing (optional)
   * 
   * @return array ['success' => bool, 'webp_path' => string|null, 'error' => string|null]
   */
  public static function convertToWebP(
    string $imagePath,
    int $quality = 85,
    ?int $maxWidth = null,
    ?int $maxHeight = null
  ): array {
    if (!file_exists($imagePath)) {
      return [
        'success' => false,
        'webp_path' => null,
        'error' => 'Image file not found'
      ];
    }

    if (!self::supportsWebP()) {
      return [
        'success' => false,
        'webp_path' => null,
        'error' => 'WebP conversion not supported on this server'
      ];
    }

    try {
      $webpPath = preg_replace('/\.[^.]+$/', '.webp', $imagePath);

      if (extension_loaded('imagick')) {
        return self::convertWithImageMagick($imagePath, $webpPath, $quality, $maxWidth, $maxHeight);
      }

      if (extension_loaded('gd')) {
        return self::convertWithGD($imagePath, $webpPath, $quality, $maxWidth, $maxHeight);
      }

      return [
        'success' => false,
        'webp_path' => null,
        'error' => 'No image processing library available (GD or ImageMagick required)'
      ];
    } catch (\Exception $e) {
      return [
        'success' => false,
        'webp_path' => null,
        'error' => 'WebP conversion error: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Convert image using ImageMagick (recommended)
   * 
   * @param string $source Original image path
   * @param string $destination WebP output path
   * @param int $quality Quality level (0-100)
   * @param int|null $maxWidth Maximum width
   * @param int|null $maxHeight Maximum height
   * 
   * @return array Conversion result
   */
  /**
   * Convert image using ImageMagick (recommended)
   * 
   * @param string $source Original image path
   * @param string $destination WebP output path
   * @param int $quality Quality level (0-100)
   * @param int|null $maxWidth Maximum width
   * @param int|null $maxHeight Maximum height
   * 
   * @return array Conversion result
   * @phpstan-ignore-next-line
   */
  private static function convertWithImageMagick(
    string $source,
    string $destination,
    int $quality,
    ?int $maxWidth,
    ?int $maxHeight
  ): array {
    try {
      /**
       * @phpstan-ignore-next-line
       * @var \Imagick $image
       */
      $image = new \Imagick($source);

      if ($maxWidth || $maxHeight) {
        /** @phpstan-ignore-next-line */
        self::resizeImage($image, $maxWidth, $maxHeight);
      }

      /** @phpstan-ignore-next-line */
      $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
      /** @phpstan-ignore-next-line */
      $image->setImageCompressionQuality($quality);

      /** @phpstan-ignore-next-line */
      $image->setImageFormat('webp');
      /** @phpstan-ignore-next-line */
      $image->writeImage($destination);
      /** @phpstan-ignore-next-line */
      $image->destroy();

      return [
        'success' => true,
        'webp_path' => $destination,
        'error' => null
      ];
    } catch (\Exception $e) {
      return [
        'success' => false,
        'webp_path' => null,
        'error' => 'ImageMagick error: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Convert image using PHP GD library (fallback)
   * 
   * @param string $source Original image path
   * @param string $destination WebP output path
   * @param int $quality Quality level (0-100)
   * @param int|null $maxWidth Maximum width
   * @param int|null $maxHeight Maximum height
   * 
   * @return array Conversion result
   */
  private static function convertWithGD(
    string $source,
    string $destination,
    int $quality,
    ?int $maxWidth,
    ?int $maxHeight
  ): array {
    $imageInfo = getimagesize($source);
    if ($imageInfo === false) {
      return [
        'success' => false,
        'webp_path' => null,
        'error' => 'Invalid image file'
      ];
    }

    list($width, $height, $type) = $imageInfo;

    $image = match ($type) {
      IMAGETYPE_JPEG => imagecreatefromjpeg($source),
      IMAGETYPE_PNG => imagecreatefrompng($source),
      IMAGETYPE_WEBP => imagecreatefromwebp($source),
      default => null
    };

    if (!$image) {
      return [
        'success' => false,
        'webp_path' => null,
        'error' => 'Could not load image with GD'
      ];
    }

    try {
      if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $image = self::applyExifOrientation($source, $image);
      }

      if ($maxWidth || $maxHeight) {
        list($newWidth, $newHeight) = self::calculateDimensions(
          $width,
          $height,
          $maxWidth,
          $maxHeight
        );

        if ($newWidth !== $width || $newHeight !== $height) {
          $resized = imagecreatetruecolor($newWidth, $newHeight);
          imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
          imagedestroy($image);
          $image = $resized;
        }
      }

      if (!imagewebp($image, $destination, $quality)) {
        return [
          'success' => false,
          'webp_path' => null,
          'error' => 'Failed to save WebP image'
        ];
      }

      imagedestroy($image);

      return [
        'success' => true,
        'webp_path' => $destination,
        'error' => null
      ];
    } catch (\Exception $e) {
      imagedestroy($image);
      return [
        'success' => false,
        'webp_path' => null,
        'error' => 'GD conversion error: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Calculate new dimensions while maintaining aspect ratio
   * 
   * @param int $width Original width
   * @param int $height Original height
   * @param int|null $maxWidth Maximum width constraint
   * @param int|null $maxHeight Maximum height constraint
   * 
   * @return array [newWidth, newHeight]
   */
  private static function calculateDimensions(
    int $width,
    int $height,
    ?int $maxWidth,
    ?int $maxHeight
  ): array {
    $newWidth = $width;
    $newHeight = $height;

    if ($maxWidth && $width > $maxWidth) {
      $ratio = $maxWidth / $width;
      $newWidth = $maxWidth;
      $newHeight = (int) ($height * $ratio);
    }

    if ($maxHeight && $newHeight > $maxHeight) {
      $ratio = $maxHeight / $newHeight;
      $newWidth = (int) ($newWidth * $ratio);
      $newHeight = $maxHeight;
    }

    return [$newWidth, $newHeight];
  }

  /**
   * Resize an Imagick image while maintaining aspect ratio
   * 
   * @param object $image Imagick image object
   * @param int|null $maxWidth Maximum width
   * @param int|null $maxHeight Maximum height
   * 
   * @return void
   */
  /**
   * Resize an Imagick image while maintaining aspect ratio
   * 
   * @param object $image Imagick image object
   * @param int|null $maxWidth Maximum width
   * @param int|null $maxHeight Maximum height
   * 
   * @return void
   * @phpstan-ignore-next-line
   */
  private static function resizeImage($image, ?int $maxWidth, ?int $maxHeight): void
  {
    /** @phpstan-ignore-next-line */
    $width = $image->getImageWidth();
    /** @phpstan-ignore-next-line */
    $height = $image->getImageHeight();

    list($newWidth, $newHeight) = self::calculateDimensions($width, $height, $maxWidth, $maxHeight);

    if ($newWidth !== $width || $newHeight !== $height) {
      /** @phpstan-ignore-next-line */
      $image->resizeImage($newWidth, $newHeight, \Imagick::FILTER_LANCZOS, 1);
    }
  }

  /**
   * Check if server supports WebP conversion
   * 
   * @return bool True if WebP is supported
   */
  /**
   * Check if server supports WebP conversion
   * 
   * @return bool True if WebP is supported
   * @phpstan-ignore-next-line
   */
  public static function supportsWebP(): bool
  {
    if (extension_loaded('gd') && function_exists('imagewebp')) {
      return true;
    }

    if (extension_loaded('imagick')) {
      try {
        /** @phpstan-ignore-next-line */
        $formats = \Imagick::queryFormats();
        /** @phpstan-ignore-next-line */
        return in_array('WEBP', $formats);
      } catch (\Exception $e) {
        return false;
      }
    }

    return false;
  }

  /**
   * Get server image processing capabilities
   * 
   * @return array ['has_gd' => bool, 'has_imagick' => bool, 'webp_support' => bool]
   */
  public static function getCapabilities(): array
  {
    return [
      'has_gd' => extension_loaded('gd') && function_exists('imagewebp'),
      'has_imagick' => extension_loaded('imagick'),
      'webp_support' => self::supportsWebP(),
    ];
  }

  /**
   * Resize an image to fit within max dimensions while maintaining aspect ratio
   * Overwrites the original file with the resized version
   * 
   * @param string $imagePath Path to the image file
   * @param int $quality JPEG quality (0-100, default 85)
   * @param int|null $maxWidth Maximum width
   * @param int|null $maxHeight Maximum height
   * 
   * @return array ['success' => bool, 'error' => string|null]
   */
  public static function resizeImageFile(
    string $imagePath,
    int $quality = 85,
    ?int $maxWidth = null,
    ?int $maxHeight = null
  ): array {
    if (!file_exists($imagePath)) {
      return ['success' => false, 'error' => 'Image file not found'];
    }

    if (!$maxWidth && !$maxHeight) {
      return ['success' => true, 'error' => null];
    }

    try {
      $imageInfo = getimagesize($imagePath);
      if ($imageInfo === false) {
        return ['success' => false, 'error' => 'Invalid image file'];
      }

      list($width, $height, $type) = $imageInfo;

      if (($maxWidth && $width <= $maxWidth) && ($maxHeight && $height <= $maxHeight)) {
        return ['success' => true, 'error' => null];
      }

      if (extension_loaded('imagick')) {
        try {
          /**
           * @phpstan-ignore-next-line
           * @var \Imagick $image
           */
          $image = new \Imagick($imagePath);
          /** @phpstan-ignore-next-line */
          $currentWidth = $image->getImageWidth();
          /** @phpstan-ignore-next-line */
          $currentHeight = $image->getImageHeight();

          list($newWidth, $newHeight) = self::calculateDimensions(
            $currentWidth,
            $currentHeight,
            $maxWidth,
            $maxHeight
          );

          if ($newWidth !== $currentWidth || $newHeight !== $currentHeight) {
            /** @phpstan-ignore-next-line */
            $image->resizeImage($newWidth, $newHeight, \Imagick::FILTER_LANCZOS, 1);
          }

          $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
          /** @phpstan-ignore-next-line */
          $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
          /** @phpstan-ignore-next-line */
          $image->setImageCompressionQuality($quality);

          /** @phpstan-ignore-next-line */
          $image->writeImage($imagePath);
          /** @phpstan-ignore-next-line */
          $image->destroy();

          return ['success' => true, 'error' => null];
        } catch (\Exception $e) {
        }
      }

      if (extension_loaded('gd')) {
        $image = match ($type) {
          IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
          IMAGETYPE_PNG => imagecreatefrompng($imagePath),
          IMAGETYPE_WEBP => imagecreatefromwebp($imagePath),
          default => null
        };

        if (!$image) {
          return ['success' => false, 'error' => 'Could not load image with GD'];
        }

        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
          $image = self::applyExifOrientation($imagePath, $image);
        }

        $width = imagesx($image);
        $height = imagesy($image);

        list($newWidth, $newHeight) = self::calculateDimensions(
          $width,
          $height,
          $maxWidth,
          $maxHeight
        );

        if ($newWidth !== $width || $newHeight !== $height) {
          $resized = imagecreatetruecolor($newWidth, $newHeight);
          imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
          imagedestroy($image);
          $image = $resized;
        }

        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $success = false;

        if ($ext === 'webp') {
          $success = imagewebp($image, $imagePath, $quality);
        } elseif ($ext === 'png') {
          $success = imagepng($image, $imagePath);
        } else {
          $success = imagejpeg($image, $imagePath, $quality);
        }

        imagedestroy($image);

        if (!$success) {
          return ['success' => false, 'error' => 'Failed to save resized image'];
        }

        return ['success' => true, 'error' => null];
      }

      return ['success' => false, 'error' => 'No image processing library available'];
    } catch (\Exception $e) {
      return ['success' => false, 'error' => 'Resize error: ' . $e->getMessage()];
    }
  }

  /**
   * Apply EXIF orientation to image
   * 
   * Reads EXIF orientation data and rotates/flips the image accordingly.
   * This ensures images taken on phones with auto-rotation are displayed correctly.
   * 
   * @param string $imagePath Path to the source image file
   * @param \GdImage $image GD image resource
   * @return \GdImage Rotated/flipped image resource
   */
  private static function applyExifOrientation(string $imagePath, $image): mixed
  {
    try {
      $exif = @exif_read_data($imagePath);
      if ($exif === false || empty($exif['Orientation'])) {
        error_log('EXIF: No orientation found for ' . basename($imagePath));
        return $image;
      }

      $orientation = (int) $exif['Orientation'];
      error_log('EXIF: Applying orientation ' . $orientation . ' to ' . basename($imagePath));

      // Apply transformations based on EXIF orientation
      // Reference: https://en.wikipedia.org/wiki/Exif#Orientation
      switch ($orientation) {
        case 2:
          error_log('EXIF: Flipping horizontal');
          imageflip($image, IMG_FLIP_HORIZONTAL);
          break;
        case 3:
          error_log('EXIF: Rotating 180°');
          $rotated = imagerotate($image, -180, 0);
          imagedestroy($image);
          $image = $rotated;
          break;
        case 4:
          error_log('EXIF: Flipping vertical');
          imageflip($image, IMG_FLIP_VERTICAL);
          break;
        case 5:
          error_log('EXIF: Rotating 90° clockwise + flipping horizontal');
          $rotated = imagerotate($image, -90, 0);
          imagedestroy($image);
          imageflip($rotated, IMG_FLIP_HORIZONTAL);
          $image = $rotated;
          break;
        case 6:
          error_log('EXIF: Rotating 270° clockwise (-270 or 90°)');
          $rotated = imagerotate($image, 90, 0);
          imagedestroy($image);
          $image = $rotated;
          break;
        case 7:
          error_log('EXIF: Rotating 270° clockwise + flipping horizontal');
          $rotated = imagerotate($image, 90, 0);
          imagedestroy($image);
          imageflip($rotated, IMG_FLIP_HORIZONTAL);
          $image = $rotated;
          break;
        case 8:
          error_log('EXIF: Rotating 90° clockwise');
          $rotated = imagerotate($image, -90, 0);
          imagedestroy($image);
          $image = $rotated;
          break;
      }

      return $image;
    } catch (\Exception $e) {
      error_log('EXIF orientation error: ' . $e->getMessage());
      return $image;
    }
  }
}
