# Background Carousel Documentation

## Overview

The Background Carousel is a global component that displays images and videos as a full-viewport background on all pages. It provides a continuous, seamless experience across page navigation with smooth CSS animations.

## Features

- **Full viewport coverage** - Carousel fills 100% of the viewport
- **Responsive content** - Images and videos maintain aspect ratio and center properly
- **Homepage vs Inner Pages** - Fully visible on homepage, intensely blurred on inner pages
- **Video Support** - Autoplay, loop, mute with mobile-specific variants
- **Image Fallback** - Automatically falls back to images if video autoplay fails
- **Page Transitions** - Overlay provides seamless page transition effect
- **Performance** - Lazy loading, responsive images, deferred JavaScript
- **Accessibility** - Respects `prefers-reduced-motion`

## ACF Setup

### Prerequisites

1. **ACF Plugin** - Advanced Custom Fields must be installed and activated (Free or Pro)
2. **Field Group Location**:
   - **ACF Pro**: Fields appear in **Theme Options** page (auto-created)
   - **ACF Free**: Fields appear on **Carousel Settings** page (auto-created) or any page

### Field Group: Background Carousel

The carousel fields are registered in code at `/inc/acf.php`. They appear automatically in WordPress admin:

- **ACF Pro**: Under **Theme Options** menu
- **ACF Free**: On the **Carousel Settings** page (created automatically) or any page you edit

**No manual field group creation needed** - it's all handled in code!

#### Fields

1. **Carousel Images** (`carousel_images`)
   - Type: Gallery
   - Required: Yes (at least 1 image)
   - Purpose: Fallback images if videos cannot autoplay
   - Instructions: Add high-quality images (1920x1080 or larger recommended)

2. **Carousel Videos** (`carousel_videos`)
   - Type: Repeater
   - Purpose: Video items with desktop/mobile variants
   - Sub-fields:
     - **Desktop Video** (`video_desktop`) - Required
       - Type: File (MP4 or WebM)
       - Used on: Desktop and tablet devices
     - **Mobile Video** (`video_mobile`) - Optional
       - Type: File (MP4 or WebM)
       - Used on: Mobile devices only
       - If not provided, desktop video will be used

### Setting Up the Carousel

1. **Go to WordPress Admin**
   - Navigate to **Theme Options** (or **Custom Fields → Options** if using ACF free)
   
2. **Add Images**
   - Click "Add to gallery" in the **Carousel Images** field
   - Upload or select at least 1 high-quality image
   - These serve as fallback if videos fail to autoplay

3. **Add Videos** (Optional but recommended)
   - Click "Add Video" in the **Carousel Videos** repeater
   - Upload **Desktop Video** (required)
   - Optionally upload **Mobile Video** (for better mobile performance)
   - Repeat for additional videos

4. **Save Changes**
   - Click "Update" or "Publish"
   - Carousel will appear on all pages immediately

## How It Works

### CSS-First Animation

The carousel uses pure CSS animations for performance:

- **Opacity transitions** - Smooth fade between slides
- **Staggered delays** - Each item has a calculated delay for continuous loop
- **Infinite loop** - Animation repeats seamlessly
- **Duration token** - Uses `--duration-carousel` CSS variable (default: 7000ms)

### Video Autoplay Detection

JavaScript handles video autoplay detection:

1. Attempts to play each video
2. If autoplay succeeds → video displays
3. If autoplay fails → video hidden, image shown instead
4. Falls back to first image if all videos fail

### Mobile Detection

Server-side PHP detects mobile devices:

- Checks `HTTP_USER_AGENT` for mobile indicators
- Serves mobile video if available
- Falls back to desktop video if mobile video not provided

### Page Continuity

- Stores current carousel state in `sessionStorage`
- Randomizes starting position on first visit
- Syncs animation delays across page loads
- Creates perception of continuous carousel

### Blur Effect

- **Homepage**: No blur, fully visible
- **Inner Pages**: Intense blur (20px) + slight scale (1.1x)
- Applied via CSS `filter: blur()` and `transform: scale()`

## CSS Variables

The carousel uses design system tokens:

```css
--duration-carousel: 7000ms; /* Slide duration */
--ease-standard: cubic-bezier(0.4, 0, 0.2, 1); /* Transition easing */
--color-bg: var(--near-black); /* Overlay background */
```

## File Structure

```
/inc/
  └── carousel.php              # Carousel logic and helper functions

/template-parts/components/
  └── background-carousel.php   # Component template

/assets/
  ├── css/
  │   └── main.css              # Carousel styles (at end of file)
  └── js/
      └── main.js               # Carousel JavaScript (autoplay detection, transitions)
```

## Maintenance

### Adding New Slides

1. Go to **Theme Options → Background Carousel**
2. Add images to gallery or videos to repeater
3. Save - changes appear immediately

### Changing Slide Duration

1. Edit **Slide Duration** field in Theme Options
2. Value updates CSS `--duration-carousel` variable
3. Animation automatically adjusts

### Troubleshooting

**Carousel not appearing:**
- Check ACF plugin is activated
- Verify at least 1 image is added
- Check browser console for JavaScript errors

**Videos not playing:**
- Browser may block autoplay (check console)
- Videos will automatically fallback to images
- Ensure videos are MP4 or WebM format
- Check video files are not corrupted

**Blur not working on inner pages:**
- Verify `is_front_page()` returns correct value
- Check CSS is loading properly
- Inspect element to see if `.background-carousel--inner` class is applied

**Page transitions not smooth:**
- Check JavaScript is loading (footer, deferred)
- Verify `prefers-reduced-motion` is not enabled
- Check browser supports CSS transitions

## Performance Considerations

- **Images**: Use `srcset` and `sizes` for responsive loading
- **Videos**: Compress videos (use H.264 for MP4)
- **Mobile**: Provide smaller mobile videos when possible
- **Lazy Loading**: Images use native `loading="lazy"`
- **JavaScript**: Minimal, deferred, only for fallbacks

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Graceful degradation for older browsers
- Video autoplay fallback handles unsupported browsers
- CSS animations with JavaScript fallback

## Accessibility

- **Reduced Motion**: Carousel pauses animations if `prefers-reduced-motion` is enabled
- **ARIA**: Carousel marked as `aria-hidden="true"` (decorative)
- **Semantic HTML**: Proper video/image elements with alt text

## Customization

### Changing Blur Intensity

Edit CSS in `/assets/css/main.css`:

```css
.background-carousel--inner .background-carousel__container {
	filter: blur(20px); /* Change value */
	transform: scale(1.1); /* Adjust scale */
}
```

### Changing Animation Duration

The carousel currently uses a fixed, code-defined slide duration (7 seconds)
for stability and consistent transitions. To change this, you would need to
adjust the timing logic in `inc/carousel.php` and the related CSS variables.

### Adding More Animation Effects

Modify `@keyframes carousel-fade` in CSS:

```css
@keyframes carousel-fade {
	0% { opacity: 0; }
	5% { opacity: 1; }
	95% { opacity: 1; }
	100% { opacity: 0; }
}
```

## Support

For issues or questions:
1. Check browser console for errors
2. Verify ACF fields are properly configured
3. Test with images only (disable videos)
4. Check theme documentation

---

**Remember**: This is a production-grade component. All code follows WordPress and web standards for performance, accessibility, and maintainability.
