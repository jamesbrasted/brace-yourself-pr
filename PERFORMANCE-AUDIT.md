# Brace Yourself Theme — Performance Audit

**Date:** February 2025  
**Focus:** Measurable performance and efficiency only. No functional rewrites unless they improve speed.

---

## 1. Queries & Database

### What’s already good

- **Roster `WP_Query`** (`page-roster.php`): Uses `no_found_rows => true`, `update_post_meta_cache => false`, `update_post_term_cache => false`. No unnecessary meta/term loading.
- Single custom query for artists; no N+1 from post meta in the query itself.

### Issues found

| Issue | Location | Impact |
|-------|----------|--------|
| **Repeated carousel data fetches** | `inc/carousel.php`, `wp_head`, `background-carousel.php` | `brace_yourself_get_carousel_data()` and `brace_yourself_get_carousel_items()` run **4–6 times per request** (preload, carousel_styles, template). Each run does many `get_field()` calls (options + fallback paths). |
| **Multiple `get_field()` per artist** | `page-roster.php` loop | Three separate `get_field('subtitle'|'link'|'hover_image', $artist_id)` per artist. ACF may cache per request, but one `get_fields($artist_id)` would guarantee a single meta fetch per artist. |
| **Four `get_field()` calls for footer** | `footer.php` | `get_field('footer_column_' . $i, $footer_page_id)` in a loop (i=1..4). One `get_fields($footer_page_id)` returns all fields and avoids four round-trips. |
| **No transients for expensive data** | Theme-wide | Carousel config and items never cached. Options-page and settings-page `get_field()` runs on every page load. Transients (e.g. 1-hour TTL) would cut repeated DB/meta work. |
| **Settings page IDs not cached** | `inc/acf.php` | `brace_yourself_get_carousel_settings_page_id()` and `brace_yourself_get_footer_settings_page_id()` use `get_page_by_path()` on every use. These IDs rarely change; caching them (object cache or transient) avoids repeated lookups. |

### Suggested improvements

1. **Carousel in-request cache**  
   In `brace_yourself_get_carousel_data()` and `brace_yourself_get_carousel_items()`, use static variables so each function computes once per request and returns the cached value on subsequent calls. No API change.

2. **Carousel (and optionally footer) transients**  
   Cache carousel data in a transient (e.g. key `brace_yourself_carousel_data`, TTL 1 hour). Invalidate on save of the options/settings page (ACF `acf/save_post` or options update). Optionally cache footer columns the same way.

3. **Roster: one ACF load per artist**  
   In the roster loop, replace three `get_field()` calls with one:
   ```php
   $fields = get_fields( $artist_id );
   $subtitle = isset( $fields['subtitle'] ) ? $fields['subtitle'] : '';
   $link     = isset( $fields['link'] ) ? $fields['link'] : '';
   $hover_img = isset( $fields['hover_image'] ) ? $fields['hover_image'] : null;
   ```

4. **Footer: one `get_fields()`**  
   Replace the loop of four `get_field('footer_column_' . $i)` with a single `get_fields($footer_page_id)` and map columns 1–4 from the returned array.

5. **Cache settings page IDs**  
   In `brace_yourself_get_carousel_settings_page_id()` and `brace_yourself_get_footer_settings_page_id()`, check a transient or object cache key first; only call `get_page_by_path()` on miss, then store the ID.

6. **Large roster**  
   If the artist list grows large (e.g. 100+), consider pagination or a capped `posts_per_page` with “Load more” instead of `-1`, to limit query and DOM size.

---

## 2. Images & Media

### What’s already good

- Custom image sizes in `inc/setup.php`: hero-*, content-*, thumbnail-*, `artist-preview` (600×600). (Hero component was removed; hero-* sizes remain available if needed elsewhere.)
- `inc/performance.php`: `wp_get_attachment_image_attributes` filters add `loading="lazy"` and a sensible `sizes` fallback when missing.
- Roster hover images use `wp_get_attachment_image(..., 'artist-preview', ...)` with `loading="lazy"`, `decoding="async"`.
- Carousel: first image `loading="eager"`, `fetchpriority="high"`; first video `preload="auto"`; others lazy.
- Background carousel preload in `wp_head` for first image.

### Issues found

| Issue | Location | Impact |
|-------|----------|--------|
| **Carousel images use `full` size** | `inc/carousel.php` | `wp_get_attachment_image_srcset($id, 'full')` used for carousel. For full-viewport background this is defensible, but you could cap with a large registered size (e.g. 1920px) to avoid huge files on large screens. |

### Suggested improvements

1. **Carousel**  
   Optional: register a size like `carousel-large` (e.g. 1920×1080) and use it for carousel srcset to cap bandwidth while keeping quality good.

---

## 3. JavaScript

### What’s already good

- Single script bundle, enqueued in footer with `defer`.
- One delegated `mousemove` on the roster list (`rosterEl`), with `passive: true`.
- Roster preview position updated inside `requestAnimationFrame`, and only `transform` is written (GPU-friendly).
- Video lazy loading and roster visibility use `IntersectionObserver`.
- No jQuery; minimal vanilla JS.

### Issues found

| Issue | Location | Impact |
|-------|----------|--------|
| **Video visibility: `setInterval` + `getComputedStyle`** | `main.js` `setupVideoPauseOnHidden()` | Every 250ms, for each video, `getComputedStyle(video).opacity` is read. This can force style recalc. With multiple videos, that’s several style reads per 250ms. Prefer one visibility check per frame (e.g. one `requestAnimationFrame` loop) or an IntersectionObserver keyed to opacity (e.g. observe when element is “visible” and toggle play/pause from that). |
| **Roster preview: two `getBoundingClientRect()` per move** | `main.js` `updatePreviewTransform()` | On each mousemove (throttled by rAF), `li.getBoundingClientRect()` and `preview.getBoundingClientRect()` are called. Two layout reads per frame. For 60fps hover this is acceptable but can be reduced by caching rects and invalidating on `resize`/`scroll`. |
| **Direct style writes in autoplay fallback** | `main.js` `checkVideoAutoplay()` | `images[index].style.display = 'block'; images[index].style.opacity = '1'` is fine; could use classes for consistency but not a performance problem. |

### Suggested improvements

1. **Video pause/when visible**  
   Replace the 250ms `setInterval` + `getComputedStyle` loop with either:
   - A single `requestAnimationFrame` loop that reads opacity once per video per frame and batches play/pause, or  
   - An IntersectionObserver (or a single rAF loop) that infers “visible” from the carousel’s current animated slide index (if that’s reliable in the DOM), to avoid reading computed style every 250ms.

2. **Roster preview rects (optional)**  
   Cache `li.getBoundingClientRect()` and `preview.getBoundingClientRect()` when they’re first needed; update cache on `resize` and `scroll` (debounced). Use cached values inside the mousemove rAF callback to avoid two layout reads per frame.

3. **Keep current approach as fallback**  
   If refactoring video visibility is deferred, consider increasing the interval (e.g. 400–500ms) to reduce style reads while still pausing off-screen videos reasonably quickly.

---

## 4. CSS

### What’s already good

- Carousel keyframe animations animate only `opacity` and `z-index` (no layout).
- `.background-carousel__item` uses `will-change: opacity` to promote compositing.
- Ticker uses `will-change: transform` and `transform: translateX(...)`.
- Roster preview positioning uses `transform: translate3d(...)` (GPU-friendly).
- Reduced-motion preference is respected (including carousel duration override where appropriate).
- No heavy `box-shadow` or `filter` on hot paths except the inner-page carousel (see below).

### Issues found

| Issue | Location | Impact |
|-------|----------|--------|
| **`filter: blur(20px)` on inner pages** | `main.css` `.background-carousel--inner .background-carousel__container` | Blur is expensive and can trigger a new layer. Acceptable for a deliberate design choice; no change suggested unless you want to reduce blur radius or restrict to certain breakpoints. |
| **Generic `sizes` in performance filter** | `inc/performance.php` `brace_yourself_responsive_image_sizes` | Fallback `sizes="(max-width: 768px) 100vw, (max-width: 1200px) 80vw, 1200px"` is only applied when `sizes` is not set, so it doesn’t override `wp_calculate_image_srcset`. Fine as a fallback. |

### Suggested improvements

1. **Leave blur as-is**  
   Keep `filter: blur(20px)` for inner pages; document that it’s a known cost for the design. If you add more blur elsewhere, consider containing it to a single layer.

2. **Avoid adding new expensive properties in hot paths**  
   For elements that animate or are inside frequently repainted areas, prefer `transform` and `opacity`; avoid new `box-shadow` or `filter` in those selectors.

3. **Optional: restrict `will-change`**  
   `.background-carousel__item` already uses `will-change: opacity`. Don’t add broad `will-change` elsewhere unless you have a measured compositing benefit; overuse can increase memory.

---

## 5. Asset Loading & Enqueue

### What’s already good

- One main stylesheet and one main script; no unused block editor or classic theme styles on the front (dequeued in `inc/performance.php`).
- Main script enqueued in footer (`true`) with `defer` via `script_loader_tag` filter.
- Version string `BRACE_YOURSELF_VERSION` used for cache busting in enqueue.

### Issues found

| Issue | Location | Impact |
|-------|----------|--------|
| **Query string removed from assets** | `inc/performance.php` `brace_yourself_remove_query_strings()` | Filter strips `?ver=...` from script and style URLs. Many caches (and some CDNs) ignore query strings; removing `ver` can make it harder to invalidate cache when you bump `BRACE_YOURSELF_VERSION`. Browsers may keep serving old CSS/JS after deploy. |
| **No conditional loading** | `inc/assets.php` | `main.js` and `main.css` load on every page. Carousel and roster logic only run when corresponding elements exist. Skipping script on pages that don’t use carousel/roster would reduce parse/compile cost on those pages (minor gain). |

### Suggested improvements

1. **Do not strip version from theme assets**  
   Remove the `brace_yourself_remove_query_strings` filter from theme script and style URLs, or restrict it to third-party handles only. Keep `?ver=...` so that when you update `BRACE_YOURSELF_VERSION`, browsers fetch new files.

2. **Conditional script loading (optional)**  
   If you split carousel and roster into separate JS files, you could enqueue carousel only on front page and roster only on the roster template. Given the current single-bundle size, this is optional and lower priority.

3. **Preload critical font**  
   You have a commented preload for a custom font in `inc/assets.php`. If you rely on Pilat for above-the-fold text, uncomment and point to the correct woff2 path (e.g. `assets/fonts/Pilat-Bold.woff2`) so the browser discovers the font earlier.

---

## 6. Caching & Hosting Considerations

### What’s already good

- Emoji and oEmbed scripts/styles removed.
- Block editor and global styles dequeued where not needed.
- Lazy loading and responsive image attributes enforced by theme.

### Issues found

| Issue | Location | Impact |
|-------|----------|--------|
| **No object cache or transients** | Theme | Carousel and footer data are recomputed every request. With object cache (Redis/Memcached), `get_option`/meta are faster; with transients, you can skip heavy ACF option/settings work for a period. |
| **External requests** | Theme | No external fonts or scripts in the theme itself. If you add analytics or third-party scripts, load them async/defer and consider self-hosting or a minimal snippet. |

### Suggested improvements

1. **Carousel transient**  
   Store result of `brace_yourself_get_carousel_data()` (or the processed items) in a transient. Key e.g. `brace_yourself_carousel`, TTL 3600. Clear on options/settings save.

2. **Footer columns transient (optional)**  
   Same idea for footer column content if the footer settings page is updated rarely.

3. **Object cache**  
   Ensure production uses an object cache (Redis/Memcached) so that repeated `get_option`, `get_post_meta`, and term queries are served from memory. No theme code change required.

4. **Page ID caching**  
   Cache carousel and footer settings page IDs (see Section 1) so `get_page_by_path()` isn’t called on every request.

---

## Summary: Priority Order

| Priority | Action | Effort | Impact |
|----------|--------|--------|--------|
| High | In-request cache for carousel data/items | Low | Removes 3–5 duplicate ACF/options fetches per request. |
| High | Remove or limit query string stripping for theme assets | Low | Restores reliable cache busting on deploy. |
| Medium | Footer: one `get_fields()` instead of four `get_field()` | Low | Fewer meta lookups on every non-front-page load. |
| Medium | Carousel (and optionally footer) transients | Medium | Cuts DB/options work for anonymous traffic. |
| Medium | Roster: `get_fields()` once per artist | Low | Fewer meta round-trips on roster page. |
| Low | JS: replace setInterval + getComputedStyle for video visibility with rAF or observer | Medium | Fewer style recalculations. |
| Low | Conditional script load (e.g. roster/carousel only where needed) | Medium | Slight parse time reduction on some pages. |
| Low | Cache settings page IDs (transient or object cache) | Low | Fewer `get_page_by_path()` lookups. |

---

## Maintaining Long-Term Performance

1. **Version bumps**  
   When changing CSS/JS, bump `BRACE_YOURSELF_VERSION` in `functions.php` and do not strip `ver` from theme asset URLs.

2. **New queries**  
   For any new `WP_Query` or `get_posts`, set `no_found_rows => true` and turn off meta/term cache updates if you don’t need them (`update_post_meta_cache`, `update_post_term_cache` => false).

3. **New images**  
   Use registered image sizes and `wp_get_attachment_image()` (or equivalent) so srcset, sizes, and dimensions are always output; avoid raw `<img src="...">` for theme-controlled images.

4. **New JS**  
   Prefer one listener per concern (delegation), `passive` where applicable, and `requestAnimationFrame` for visual updates; avoid repeated `getComputedStyle`/`getBoundingClientRect` in tight loops.

5. **New CSS animations**  
   Prefer `transform` and `opacity`; use `will-change` sparingly and only where it improves compositing.

6. **Periodic audit**  
   Re-run a similar checklist when adding new templates, ACF option pages, or third-party integrations.
