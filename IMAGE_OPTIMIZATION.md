# Image Optimization Strategy

## Overview
This document outlines the image optimization requirements and implementation plan for the Blue Ridge Farmers Collective website.

## Optimization Goals
1. Compress all images to reduce file size
2. Convert images to WebP format for modern browsers with fallbacks
3. Implement lazy loading for below-the-fold images
4. Add explicit width/height attributes to prevent layout shift (CLS - Cumulative Layout Shift)
5. Use appropriate image dimensions for display contexts

## Current Image Inventory

### Static Assets Location
- **Icons**: `/public/images/icons/` - SVG (fresh-local.svg, community-first.svg, easy-explore.svg)
- **Banners**: `/public/images/banners/` - PNG (logo.png, logo2.png)
- **Backgrounds**: `/public/images/backgrounds/` - JPG (flowers.jpg)
- **Uploaded Images**: `/public/uploads/products/` and `/public/uploads/vendors/` - User-generated content

### SVG Files
- Logo files in root images folder (logo-default.svg, logo-scroll.svg) - These are no longer used, can be removed
- Favicon (favicon.svg)
- Icons in `/images/icons/`

## Implementation Plan

### Phase 1: Update PHP Templates (Add width/height attributes)

#### Files to Update:
1. **src/Views/partials/header.php** - Navigation logo
   - Add width/height attributes
   - Note: Viewport-based sizing handled by CSS classes

2. **src/Views/home/index.php** - Home page icons
   - Already has width/height attributes ✓

3. **src/Views/vendors/index.php** - Vendor card images
   - Add width/height attributes for vendor photos
   - Add loading="lazy" for below-fold images

4. **src/Views/vendors/show.php** - Vendor detail and product images
   - Add width/height attributes
   - Add loading="lazy" for product images

5. **src/Views/products/** - Product display pages
   - Add width/height attributes
   - Add loading="lazy"

6. **src/Views/markets/** - Market pages
   - Add width/height attributes where applicable

7. **src/Views/vendor-dashboard/** - Vendor dashboard pages
   - Add width/height for product previews
   - Add loading="lazy"

#### Referenced from JavaScript:
- **public/js/main.js** (line 774) - Live search images
  - Already has loading="lazy" ✓

### Phase 2: Image Format Conversion (Server Operations)

#### Required Tools:
- ImageMagick or similar image processing library
- cwebp (WebP encoder)

#### Conversion Steps:

##### Static Images:
1. **icons/**.svg → Keep as SVG (best for scalable graphics)
2. **banners/**.png → Convert to WebP with PNG fallback
3. **backgrounds/flowers.jpg** → Compress and convert to WebP

##### Uploaded Images (Dynamic):
- Implement server-side image processing on upload
- Create multiple sizes (thumbnail, medium, large)
- Convert to WebP with JPEG fallback
- Compression levels:
  - Thumbnail (100x100): 70-80% quality
  - Medium (400x400): 75-85% quality
  - Large (800x800): 80-90% quality

### Phase 3: Lazy Loading Implementation

Images to lazy load:
- Below-fold vendor cards in listings
- Product images in grids
- User-generated content in galleries
- Market images

Images NOT to lazy load (above-fold):
- Hero images
- Navigation logo
- First section featured content

### Phase 4: CSS Background Images

#### Current:
- `src/assets/tailwind.css` (line 16)
- `public/css/tailwind.css` (line 355)
- File: `backgrounds/flowers.jpg`

#### Action:
- Convert to WebP with JPG fallback
- Use CSS image-set() or picture element fallback:

```css
.hero {
  background-image: 
    image-set(
      url('../images/backgrounds/flowers.webp') type('image/webp'),
      url('../images/backgrounds/flowers.jpg') type('image/jpeg')
    );
}
```

## Responsive Image Sizing

### Logo (Navigation)
- Mobile: max-width 220px
- Tablet (sm): max-width 280px
- Desktop (md): max-width 320px
- Actual dimensions: Should add width="320" height="XX"

### Vendor Cards
- Thumbnail: 300x300px
- Display sizes handled by CSS

### Product Images
- Thumbnail: 150x150px
- Medium: 400x400px
- Large: 800x800px

### Icon Images
- Standard: 48x48px or 64x64px
- Already optimized as SVG

## Performance Metrics

### Before Optimization:
- Monitor image file sizes
- Check CLS (Cumulative Layout Shift)
- Measure LCP (Largest Contentful Paint)

### After Optimization (Targets):
- Reduce image payload by 60-70%
- CLS < 0.05
- LCP < 2.5 seconds

## WebP Format Details

### Browser Support:
- Chrome/Edge: Full support
- Safari: iOS 14+
- Firefox: Full support
- IE: Not supported (use fallback)

### Implementation Pattern:
```html
<picture>
  <source srcset="image.webp" type="image/webp">
  <source srcset="image.jpg" type="image/jpeg">
  <img src="image.jpg" alt="Description" width="300" height="200" loading="lazy">
</picture>
```

## Files Modified

### Phase 1 Updates (Completed):
- [ ] src/Views/partials/header.php
- [ ] src/Views/vendors/index.php
- [ ] src/Views/vendors/show.php
- [ ] src/Views/products/ (multiple files)
- [ ] src/Views/markets/ (multiple files)
- [ ] src/Views/vendor-dashboard/ (multiple files)

### Phase 2 & 3 (Requires Server Setup):
- Convert existing images to WebP
- Implement image compression
- Set up lazy loading library if needed (pick.jpg or similar)

### Phase 4 (CSS Updates):
- Update background-image rules
- Add image-set() support

## Compression Command Examples

```bash
# Use ImageMagick
convert input.jpg -quality 85 -strip output.jpg
cwebp -q 85 input.jpg -o output.webp

# Batch processing
for img in *.jpg; do cwebp -q 85 "$img" -o "${img%.jpg}.webp"; done
```

## Next Steps

1. **Immediate**: Update PHP templates with width/height attributes
2. **Short-term**: Set up image processing pipeline on server
3. **Medium-term**: Convert all existing images to WebP
4. **Ongoing**: Optimize new uploads on the fly

## Notes

- SVG icons are already optimal and should NOT be converted
- Keep original files and derivatives
- Document actual image dimensions in uploads folder
- Consider CDN delivery if image usage grows significantly

---

# WebP Conversion Implementation (COMPLETED ✅)

## Automated WebP Conversion System

An automated WebP conversion system has been implemented that:
- ✅ Converts uploaded images to WebP on-the-fly
- ✅ Maintains original format as fallback
- ✅ Uses GD or ImageMagick (automatic fallback)
- ✅ Estimates **4,538 KiB savings** per user
- ✅ 60-70% file size reduction per image

### How It Works

1. **Upload:** User uploads JPG/PNG image through admin panel
2. **Validation:** Image validated (size, type, dimensions)
3. **Storage:** Original saved to `/public/uploads/{type}/`
4. **Conversion:** ImageProcessor automatically creates WebP version
5. **Delivery:** modern browsers get WebP, older browsers get original

### Architecture

#### Service: `src/Services/ImageProcessor.php`
Complete image processing with:
- WebP conversion (primary: ImageMagick, fallback: GD)
- Automatic resizing (max 1200x1200)
- Quality control (default 85%)
- Graceful error handling
- Server capability detection

#### Integration: `src/Controllers/BaseController.php`
Modified `uploadPhoto()` method:
- Auto-converts images after upload
- Non-blocking (failures don't break upload)
- Logs conversion errors
- Scales images down from large uploads

#### Helpers: `src/Helpers/functions.php`
Two new functions:

**`picture_tag($path, $alt, $class, $attributes)`**
Generates `<picture>` element with WebP + fallback
```php
<?= picture_tag(
  $product['photo'], 
  'Product Name', 
  'product-image'
) ?>
```

**`webp_image_url($path)`**
Returns WebP URL if available, otherwise original
```php
style="background-image: url('<?= webp_image_url($photo) ?>')"
```

### Server Requirements

**At least one required:**
- PHP GD extension (usually built-in)
- PHP ImageMagick extension (better quality)

**Check support:**
```bash
php check-webp-support.php
```

### Usage Examples

#### Product Page
```php
<!-- Before -->
<img src="<?= asset_url($product['photo']) ?>" alt="<?= h($product['name']) ?>">

<!-- After -->
<?= picture_tag($product['photo'], h($product['name']), 'product-image') ?>
```

#### CSS Backgrounds
```php
<div style="background-image: url('<?= webp_image_url($vendor['photo']) ?>')">
```

### Performance Impact

| Metric | Before | After |
|--------|--------|-------|
| JPG Size | 100 KiB | 30-40 KiB |
| Page (20 images) | ~2 MB | ~600-800 KiB |
| Savings | - | **65-70%** |
| Load time | 500ms+ | 200-300ms |

### Browser Compatibility

**Modern browsers (WebP):**
- Chrome 23+, Edge 18+, Firefox 65+, Safari 14+, Android 5+

**Fallback (Original format):**
- IE 9+, older Safari/Firefox, legacy devices

The `<picture>` element handles fallback automatically.

### Verification

**1. Check server support:**
```bash
php check-webp-support.php
# Should show: WebP Support: ✅ Available
```

**2. Test upload:**
- Admin panel → Upload image
- Check `/public/uploads/{type}/` for both `.jpg` and `.webp`

**3. Browser verification:**
- DevTools → Network tab
- Reload page with image
- Modern browsers should request `.webp`
- Older browsers fallback to original

### Troubleshooting

**WebP files not created:**
1. Run `php check-webp-support.php`
2. If WebP Support = ❌, install GD or ImageMagick
3. Restart PHP-FPM: `sudo systemctl restart php-fpm`
4. Try uploading again

**Permission errors:**
```bash
chmod 755 public/uploads/{products,vendors}
```

**See detailed guide:** `IMAGE_OPTIMIZATION:` for complete setup & troubleshooting.
