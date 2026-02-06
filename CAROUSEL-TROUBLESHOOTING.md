# Carousel Fields Not Showing? Troubleshooting Guide

## Quick Fixes

### 1. Refresh the Page
- Go to **Pages → Carousel Settings**
- Click **Refresh** in your browser (Cmd+R / Ctrl+R)
- The fields should appear below the content editor

### 2. Check ACF Field Groups
- Go to **Custom Fields → Field Groups** in WordPress admin
- Look for **"Background Carousel"** in the list
- If you see a **"Sync"** button, click it
- This syncs the code-based field group with ACF

### 3. Verify ACF is Active
- Go to **Plugins**
- Make sure **Advanced Custom Fields** is **Activated**
- If not activated, activate it and refresh

### 4. Check Field Group Location
The field group should appear on:
- **ACF Pro**: Theme Options page
- **ACF Free**: All pages (including Carousel Settings)

### 5. Clear WordPress Cache
If using a caching plugin:
- Clear all caches
- Refresh the page

## Still Not Working?

### Check Browser Console
1. Open browser DevTools (F12)
2. Go to Console tab
3. Look for any JavaScript errors
4. Report any errors you see

### Verify Field Group Registration
The field group is registered in code at:
- `/inc/acf.php` (lines 161-235)

### Manual Field Group Check
1. Go to **Custom Fields → Field Groups**
2. You should see **"Background Carousel"** listed
3. Click on it to edit
4. Check the **Location** rules:
   - Should show: "Page is equal to Page" OR "Options Page is equal to Theme Options"

### Alternative: Use Any Page
Since the field group appears on all pages, you can:
1. Edit **any page** in WordPress
2. Scroll down below the content editor
3. You should see **"Background Carousel"** fields
4. Configure your carousel there
5. The carousel will work globally regardless of which page you edit

## Expected Behavior

When editing the **Carousel Settings** page, you should see:

1. **Content Editor** (with the default text)
2. **Below the editor**: ACF meta box titled **"Background Carousel"** with:
   - Carousel Images (gallery field)
   - Carousel Videos (repeater field)
   - Slide Duration (number field)

## Still Need Help?

If fields still don't appear:
1. Check ACF plugin version compatibility
2. Verify WordPress version (6.0+)
3. Try deactivating other plugins temporarily
4. Check theme is active and up to date
