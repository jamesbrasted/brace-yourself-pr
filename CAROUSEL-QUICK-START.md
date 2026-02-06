# Background Carousel - Quick Start Guide

## Setup Steps

### 1. Install ACF Plugin
- Install **Advanced Custom Fields** plugin (Pro recommended for Options Page)
- Activate the plugin

### 2. Access Carousel Settings

**For ACF Pro:**
- Go to **WordPress Admin → Theme Options**
- The "Background Carousel" field group will appear there

**For ACF Free:**
- Go to **WordPress Admin → Pages**
- Look for a page called **"Carousel Settings"** (created automatically)
- If it doesn't exist, edit any page and the fields will appear
- The theme will create the "Carousel Settings" page automatically on first use

### 3. Configure Carousel
1. **Add Images** (Required)
   - Click "Add to gallery" in **Carousel Images**
   - Upload at least 1 image (1920x1080px recommended)
   - These are fallback if videos don't autoplay

2. **Add Videos** (Optional)
   - Click "Add Video" in **Carousel Videos**
   - Upload **Desktop Video** (MP4 or WebM)
   - Optionally upload **Mobile Video** (smaller file for mobile)
   - Repeat for multiple videos

3. **Set Duration** (Optional)
   - Default is 7 seconds
   - Adjust **Slide Duration** if needed (3-15 seconds)

4. **Save**
   - Click "Update" or "Publish"
   - Carousel appears immediately on all pages

## What You'll See

- **Homepage**: Carousel fully visible, no blur
- **Other Pages**: Carousel intensely blurred in background
- **Smooth Transitions**: Fade between slides every 7 seconds
- **Video Priority**: Videos play if browser supports autoplay, otherwise images show

## File Locations

- **ACF Fields**: `/inc/acf.php` (lines 95-165)
- **Carousel Logic**: `/inc/carousel.php`
- **Component Template**: `/template-parts/components/background-carousel.php`
- **CSS Styles**: `/assets/css/main.css` (end of file)
- **JavaScript**: `/assets/js/main.js` (carousel functions)

## Troubleshooting

**Carousel not showing?**
- Check ACF is activated
- Add at least 1 image
- Check browser console for errors

**Videos not playing?**
- Normal - browser may block autoplay
- Images will show automatically as fallback
- Check video format (MP4/WebM)

**Need help?**
- See `CAROUSEL-DOCUMENTATION.md` for full details
