# Vendor Image Upload Issue - Root Cause & Fix

## Problem Identified

Users were unable to upload vendor profile images. The error message indicated "Photo must be 2MB or smaller" even when attempting to upload reasonably-sized images.

## Root Cause

The upload limit in `src/Controllers/BaseController.php` was set to **2MB**, which is too restrictive for vendor profile photos. When users attempted to upload images larger than 2MB (for example, background images that are 7+ MB), the upload was rejected before any processing occurred.

Additionally, the code was not optimizing uploaded images to reasonable dimensions before storage.

## Solution Implemented

### 1. **Increased Upload Limit** (5MB)
- Changed the file size check from 2MB to 5MB in `uploadPhoto()`
- Error message updated to reflect "Photo must be 5MB or smaller"

### 2. **Added Image Optimization**
- New `optimizeImage()` method automatically resizes large images to fit within 1200x1200px
- Only resizes if image exceeds optimal dimensions
- Preserves aspect ratio while reducing file size
- Runs before WebP conversion

### 3. **Added ResizeImageFile Method**
- New public method `ImageProcessor::resizeImageFile()` handles image resizing
- Supports both ImageMagick (primary) and GD (fallback)
- Maintains aspect ratio using `calculateDimensions()` helper
- Quality set to 85 for good balance between size and quality

### 4. **Process Flow (Updated)**
```
1. User uploads file
   ↓
2. Validate MIME type and size (5MB limit)
   ↓
3. Save original file via move_uploaded_file()
   ↓
4. Optimize image (resize if > 1200x1200px)
   ↓
5. Convert to WebP format alongside original
   ↓
6. Return both formats to browser (WebP for modern browsers, original as fallback)
```

## Files Modified

### `src/Controllers/BaseController.php`
- **uploadPhoto()**: Increased limit to 5MB, added optimization call
- **optimizeImage()**: New method for automatic image resizing
- **convertImageToWebP()**: Unchanged, still called after optimization

### `src/Services/ImageProcessor.php`
- **resizeImageFile()**: New public method for image resizing
  - Supports max dimensions for automatic resizing
  - Works with JPEG, PNG, WebP formats
  - Overwrites original file with resized version
  - Non-breaking: image remains available if resize fails

## Benefits

1. **User Experience**: Users can now upload images up to 5MB
2. **Storage Optimization**: Large images are automatically resized
3. **Performance**: Smaller image files = faster serving
4. **Web Format Support**: WebP conversion still happens for modern browsers
5. **Reliability**: Non-blocking optimization (failures don't prevent upload)

## Testing Recommendations

1. Test uploading a 2-5MB JPG image - should succeed
2. Test uploading a large JPG (e.g., 7MB) - should resize automatically
3. Verify both `.jpg` and `.webp` files exist in `/public/uploads/`
4. Test on modern browser (WebP) and older browser (JPG fallback)

## No Breaking Changes

- Backward compatible with existing image uploads
- All helper functions (`picture_tag()`, `webp_image_url()`) work unchanged
- View templates continue to work as-is
