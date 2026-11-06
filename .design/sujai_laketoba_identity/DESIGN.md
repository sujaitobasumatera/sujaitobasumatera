---
name: Sujai Toba Sumatera Identity
colors:
  surface: '#fcf9f8'
  surface-dim: '#dcd9d9'
  surface-bright: '#fcf9f8'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f6f3f2'
  surface-container: '#f0edec'
  surface-container-high: '#ebe7e7'
  surface-container-highest: '#e5e2e1'
  on-surface: '#1c1b1b'
  on-surface-variant: '#414942'
  inverse-surface: '#313030'
  inverse-on-surface: '#f3f0ef'
  outline: '#717972'
  outline-variant: '#c1c9c0'
  surface-tint: '#3a684c'
  primary: '#002513'
  on-primary: '#ffffff'
  primary-container: '#0b3c24'
  on-primary-container: '#77a788'
  inverse-primary: '#a0d2b0'
  secondary: '#735c00'
  on-secondary: '#ffffff'
  secondary-container: '#fed65b'
  on-secondary-container: '#745c00'
  tertiary: '#3b1017'
  on-tertiary: '#ffffff'
  tertiary-container: '#55252b'
  on-tertiary-container: '#ce8a90'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#bceecb'
  primary-fixed-dim: '#a0d2b0'
  on-primary-fixed: '#002110'
  on-primary-fixed-variant: '#224f36'
  secondary-fixed: '#ffe088'
  secondary-fixed-dim: '#e9c349'
  on-secondary-fixed: '#241a00'
  on-secondary-fixed-variant: '#574500'
  tertiary-fixed: '#ffdadb'
  tertiary-fixed-dim: '#fdb3b9'
  on-tertiary-fixed: '#360d14'
  on-tertiary-fixed-variant: '#6b373d'
  background: '#fcf9f8'
  on-background: '#1c1b1b'
  surface-variant: '#e5e2e1'
typography:
  display-lg:
    fontFamily: Playfair Display
    fontSize: 72px
    fontWeight: '700'
    lineHeight: 84px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Playfair Display
    fontSize: 48px
    fontWeight: '600'
    lineHeight: 56px
  headline-lg-mobile:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-md:
    fontFamily: Playfair Display
    fontSize: 32px
    fontWeight: '500'
    lineHeight: 40px
  body-lg:
    fontFamily: Outfit
    fontSize: 18px
    fontWeight: '300'
    lineHeight: 28px
  body-md:
    fontFamily: Outfit
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-caps:
    fontFamily: Outfit
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.1em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  unit: 8px
  container-max: 1440px
  gutter: 24px
  margin-mobile: 20px
  margin-desktop: 80px
  section-gap: 120px
---

## Brand & Style

This design system is built to evoke the feeling of a private, high-end expedition. It balances the raw, untamed beauty of nature with the refined polish of ultra-luxury service. The aesthetic is "Ecological Opulence"—merging deep, organic tones with sophisticated digital effects.

The visual direction utilizes a hybrid of **Minimalism** and **Glassmorphism**. High-impact photography is the protagonist, treated with subtle Ken Burns scaling effects to create a sense of living memory. Layouts transition between "Day Mode" (expansive white space, crisp typography) and "Night Mode" (deep jungle greens, pitch-black glass surfaces, and gold accents) to differentiate between informational content and exclusive, high-conversion offerings.

## Colors

The palette is anchored by **Deep Jungle Green**, representing the lush landscapes of Lake Toba, and **Warm Gold**, which signals premium status and rare opportunities. 

- **Primary (Deep Jungle Green):** Used for hero backgrounds, primary buttons, and brand-heavy sections.
- **Secondary (Warm Gold):** Reserved for interactive accents, call-to-action highlights, and iconography.
- **Neutrals:** Crisp White (#FFFFFF) provides the "Day" canvas, while Pitch-Black and Slate Grays build the "Night" glassmorphic interfaces.
- **Glassmorphism:** Dark surfaces should use 60-80% opacity black with a 20px-40px backdrop blur to maintain legibility over vibrant imagery.

## Typography

The typography strategy pairs the editorial authority of **Playfair Display** with the modern, geometric clarity of **Outfit**. 

- **Headlines:** Use Playfair Display for all emotional hooks and destination titles. Large display sizes should use tight letter-spacing for a modern, high-fashion look.
- **Body:** Outfit is set with generous line-heights to ensure readability. Light weights (300) are preferred for long-form descriptions to maintain an airy, sophisticated feel.
- **Labels:** Use uppercase Outfit for "Overlines" and utility labels to create clear hierarchy against the serif headings.

## Layout & Spacing

This design system employs a **fixed-center grid** for desktop and a **fluid grid** for mobile devices. 

- **Desktop:** A 12-column grid with a 1440px maximum width. Use wide 80px outer margins to create a "framed" boutique feel.
- **Rhythm:** An 8px base unit drives all spacing. Section gaps are intentionally large (120px+) to allow the high-resolution imagery to breathe.
- **Mobile:** Transitions to a 4-column grid with 20px margins. Elements like booking cards should break the grid slightly or use horizontal "peek" scrolling to suggest more content.

## Elevation & Depth

Visual hierarchy in this design system is achieved through **Glassmorphism** and **Layered Depth** rather than traditional drop shadows.

- **Surface Layers:** Elements "float" over photographic backgrounds. Use a 1px inner border (stroke) with 10% white opacity on dark glass cards to simulate the edge of a glass pane.
- **Background Blurs:** Any card overlaying an image must use `backdrop-filter: blur(24px)`.
- **Depth:** Elements closer to the user are brighter and have higher blur values. Lower-level cards are more transparent.
- **Shadows:** Avoid heavy black shadows. If needed for legibility, use "Ambient Glows"—very soft, large-radius shadows tinted with the Deep Jungle Green color at 5-10% opacity.

## Shapes

The shape language is **Soft (0.25rem - 0.75rem)**. This provides a subtle modern touch without feeling "bubbly" or overly casual.

- **Standard Elements:** Buttons and small inputs use a 4px (0.25rem) radius.
- **Feature Cards:** Glassmorphic cards and image containers use a 12px (0.75rem) radius to create a distinct, nested look.
- **Interactive States:** On hover, cards may expand slightly, but the corner radius should remain consistent to maintain the architectural feel.

## Components

### Buttons
- **Primary:** Deep Jungle Green background, Gold text or White text. Rectangular with a slight 4px radius. 
- **Ghost:** Gold 1px border with Gold text. Used for secondary actions like "View Gallery."
- **Glass Button:** Transparent white background (10%) with heavy blur for overlaying on hero images.

### Cards
- **Destination Card:** Full-bleed image background with a glassmorphic footer containing the price and title. Implements a Ken Burns hover effect where the image scales 10% over 5 seconds.
- **Booking Card:** Pitch-black glass background, gold accents for the "Confirm" action, and crisp white typography for dates/details.

### Inputs
- **Search:** Minimalist bottom-border only for "Day Mode"; dark glass container with subtle gold focus rings for "Night Mode."

### Special Components
- **The Concierge Bar:** A persistent, thin glassmorphic bar at the bottom of the screen containing quick links to "Book Now" and "Contact Expert."
- **Image Scrubber:** A custom-styled progress bar at the top of hero sections to indicate auto-playing destination stories.