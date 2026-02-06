# ACF Free vs Pro - Carousel Limitations

## Summary

The carousel now **automatically detects** whether you're using ACF Free or ACF Pro and adjusts accordingly.

## What Works in Both Versions

✅ **Carousel Images** - Gallery field (works in both)  
✅ **Slide Duration** - Number field (works in both)  
✅ **Basic functionality** - Carousel works with images in both versions

## ACF Pro Features (Not Available in Free)

### 1. **Repeater Field** (Pro Only)
- **What it does**: Allows multiple videos with separate desktop/mobile variants
- **ACF Free alternative**: Gallery field for videos (multiple videos, but same video on all devices)

### 2. **Options Page** (Pro Only)  
- **What it does**: Global settings page in WordPress admin
- **ACF Free alternative**: Settings stored on "Carousel Settings" page (auto-created)

## Differences: Pro vs Free

### ACF Pro Experience
- **Videos Field**: Repeater with "Add Video" button
- **Each Video Has**:
  - Desktop Video (required)
  - Mobile Video (optional)
- **Result**: Different videos can be served to mobile vs desktop

### ACF Free Experience  
- **Videos Fields**: Single video with desktop and mobile variants
- **Video 1**: Desktop file + Mobile file (optional)
- **Result**: Mobile fallback supported! Single video with optional mobile variant
- **Limitation**: Single video only (vs unlimited with Pro repeater) - optimized for performance

## Code Changes Made

The theme now:

1. **Auto-detects** ACF version on field registration
2. **Shows appropriate fields** based on version:
   - Pro: Repeater field with desktop/mobile sub-fields
   - Free: Gallery field for videos
3. **Handles both formats** in carousel logic:
   - Pro format: `$videos[0]['video_desktop']`
   - Free format: `$videos[0]['url']`

## Recommendations

### For ACF Free Users
- ✅ **Works perfectly** with images only
- ✅ **Works with videos** - single video supported (performance optimized)
- ✅ **Mobile fallback supported** - video can have separate mobile variant
- ⚠️ **Limitation**: Single video only (vs unlimited with Pro)
- 💡 **Tip**: Use mobile variant for better performance on mobile devices

### For ACF Pro Users
- ✅ **Full functionality** with desktop/mobile video variants
- ✅ **Better mobile performance** (can use smaller mobile videos)
- ✅ **More flexibility** with repeater field

## Migration Path

If you upgrade from ACF Free to Pro:
1. The field group will automatically switch to Pro format
2. You'll need to re-add videos using the new repeater format
3. Old gallery videos will still work, but you can now add mobile variants

## Technical Details

**Detection Method**:
```php
$is_acf_pro = function_exists( 'acf_get_field_type' ) && acf_get_field_type( 'repeater' );
```

**Field Registration**:
- Pro: `type => 'repeater'` with sub-fields
- Free: `type => 'gallery'` for videos

**Data Format**:
- Pro: `array( array( 'video_desktop' => ..., 'video_mobile' => ... ) )`
- Free: `array( array( 'url' => ... ) )` (gallery format)

---

**Bottom Line**: The carousel works with both ACF Free and Pro. Pro adds mobile video variants and a more flexible repeater interface, but Free works perfectly fine for most use cases.
