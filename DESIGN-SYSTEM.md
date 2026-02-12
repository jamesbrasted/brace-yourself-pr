# Design System Reference

Quick reference for the Brace Yourself theme design system.

## Modular Scale Calculation

**Formula**: `base × (ratio ^ step)`

- Base: 17px
- Ratio: 1.414 (√2)
- Browser default: 16px (for rem conversion)

### Scale Values

| Token | Rem | Pixels | Usage |
|-------|-----|--------|-------|
| `--scale-000` | 0.187967rem | 3.01px | Tiny spacing |
| `--scale-100` | 0.265786rem | 4.25px | Small spacing |
| `--scale-200` | 0.375821rem | 6.01px | Border radius small |
| `--scale-300` | 0.531410rem | 8.50px | Border radius base |
| `--scale-400` | 0.751414rem | 12.02px | Text small, spacing |
| `--scale-500` | 1.062500rem | 17.00px | **Base** - Body text |
| `--scale-600` | 1.502375rem | 24.04px | Text large, spacing |
| `--scale-700` | 2.124358rem | 33.99px | Text XL, heading H3 |
| `--scale-800` | 3.003843rem | 48.06px | Heading H2 |
| `--scale-900` | 4.247433rem | 67.96px | Heading H1 |
| `--scale-1000` | 6.005871rem | 96.09px | Heading display |

## Spacing Scale

| Token | Value | Usage |
|-------|-------|-------|
| `--space-000` | 0 | Reset |
| `--space-100` | `var(--scale-000)` | 3.01px |
| `--space-200` | `var(--scale-100)` | 4.25px |
| `--space-300` | `var(--scale-200)` | 6.01px |
| `--space-400` | `var(--scale-300)` | 8.50px |
| `--space-500` | `var(--scale-400)` | 12.02px |
| `--space-600` | `var(--scale-500)` | 17.00px |
| `--space-700` | `var(--scale-600)` | 24.04px |
| `--space-800` | `var(--scale-700)` | 33.99px |
| `--space-900` | `var(--scale-800)` | 48.06px |
| `--space-1000` | `var(--scale-900)` | 67.96px |
| `--space-1100` | `var(--scale-1000)` | 96.09px |

### Semantic Spacing Tokens

- `--space-section`: Large section padding (`--space-1100` / 96.09px)
- `--space-stack`: Vertical rhythm between elements (`--space-600` / 17.00px)
- `--space-inline`: Horizontal spacing (`--space-400` / 8.50px)

## Typography Scale

| Token | Value | Pixels | Usage |
|-------|-------|--------|-------|
| `--text-sm` | `var(--scale-400)` | 12.02px | Captions, small text |
| `--text-base` | `var(--scale-500)` | 17.00px | Body text (base) |
| `--text-lg` | `var(--scale-600)` | 24.04px | Large body text |
| `--text-xl` | `var(--scale-700)` | 33.99px | Small headings |
| `--text-2xl` | `var(--scale-800)` | 48.06px | Medium headings |
| `--text-3xl` | `var(--scale-900)` | 67.96px | Large headings |
| `--text-4xl` | `var(--scale-1000)` | 96.09px | Display headings |

### Semantic Typography Tokens

- `--font-heading-xl`: `var(--text-4xl)` - Display / hero headings
- `--font-heading-l`: `var(--text-3xl)` - Primary page titles
- `--font-heading-m`: `var(--text-2xl)` - Section headings
- `--font-heading-s`: `var(--text-xl)` - Subsection headings
- `--font-body-l`: `var(--text-lg)` - Large body text / lead
- `--font-body`: `var(--text-base)` - Default body text
- `--font-caption`: `var(--text-sm)` - Captions, meta text

## Color System

### Raw Colors (Never use directly)

- `--near-black: #121212`
- `--crimson-red: #E03C28`
- `--pure-white: #FFFFFF`

### Semantic Colors (Use these)

- `--color-bg: var(--near-black)` - Background
- `--color-text: var(--pure-white)` - Text color
- `--color-accent: var(--crimson-red)` - Accent color, links, CTAs

## Line Height

- `--line-height-tight: 1` - Headings
- `--line-height-base: 1.2` - Body text (default)
- `--line-height-relaxed: 1.3` - Long-form content
- `--line-height-loose: 1.4` - Spacious layouts

## Letter Spacing

- `--letter-spacing-tight: -0.02em` - Large headings
- `--letter-spacing-normal: 0` - Default
- `--letter-spacing-wide: 0.02em` - Uppercase text
- `--letter-spacing-wider: 0.05em` - Display text

## Border Radius

- `--radius-sm: var(--scale-200)` - 6.01px
- `--radius-base: var(--scale-300)` - 8.50px
- `--radius-lg: var(--scale-400)` - 12.02px
- `--radius-full: 9999px` - Pills, circles

## Shadows

- `--shadow-sm`: Subtle elevation
- `--shadow-base`: Default elevation
- `--shadow-md`: Medium elevation
- `--shadow-lg`: Large elevation
- `--shadow-xl`: Extra large elevation

## Motion System

### Easing Functions

- `--ease-standard: cubic-bezier(0.4, 0, 0.2, 1)` - Default
- `--ease-in: cubic-bezier(0.4, 0, 1, 1)` - Accelerate
- `--ease-out: cubic-bezier(0, 0, 0.2, 1)` - Decelerate
- `--ease-in-out: cubic-bezier(0.4, 0, 0.2, 1)` - Symmetric

### Durations

- `--duration-fast: 150ms` - Quick interactions
- `--duration-base: 250ms` - Default transitions
- `--duration-slow: 400ms` - Deliberate animations

**Note**: Always respect `prefers-reduced-motion` media query.

## Font Families

- `--font-family-body`: Primary sans-serif stack for body text
  ```css
  'Pilat', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif
  ```
- `--font-family-heading`: Heading stack (prefers wide display where available)
  ```css
  'Pilat Wide', 'Pilat', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif
  ```

## Usage Guidelines

### Do's ✅

- Use semantic tokens: `var(--space-stack)`, `var(--font-body)`
- Use `.flow` utility for vertical rhythm
- Calculate new values from the scale
- Document component ACF contracts

### Don'ts ❌

- Never use raw color values directly
- Never use raw scale values in components (use semantic tokens)
- Never use arbitrary pixel values
- Never bypass the design system

## Calculating New Values

To add a new scale value:

1. Determine the step (e.g., scale-1100 would be step +6)
2. Calculate: `17 × (1.414 ^ 6) = 17 × 7.09 = 120.53px`
3. Convert to rem: `120.53 / 16 = 7.533125rem`
4. Add to CSS with comment: `/* 120.53px */`

## Vertical Rhythm

The `.flow` utility automatically adds `--space-stack` (17px) between direct children:

```html
<div class="flow">
  <h1>Heading</h1>  <!-- margin-top: 0 -->
  <p>Paragraph</p>   <!-- margin-top: 17px -->
  <p>Another</p>     <!-- margin-top: 17px -->
</div>
```

This ensures consistent vertical rhythm throughout the theme.
