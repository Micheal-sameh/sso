---
name: Avarewase Authentication
colors:
  surface: '#FFFFFF'
  surface-dim: '#dbdad7'
  surface-bright: '#fbf9f6'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f5f3f0'
  surface-container: '#efeeeb'
  surface-container-high: '#eae8e5'
  surface-container-highest: '#e4e2df'
  on-surface: '#1b1c1a'
  on-surface-variant: '#564243'
  inverse-surface: '#30312f'
  inverse-on-surface: '#f2f0ed'
  outline: '#897172'
  outline-variant: '#dcc0c1'
  surface-tint: '#a23a4a'
  primary: '#5c021a'
  on-primary: '#ffffff'
  primary-container: '#7b1c2e'
  on-primary-container: '#ff8a96'
  inverse-primary: '#ffb2b8'
  secondary: '#7b5800'
  on-secondary: '#ffffff'
  secondary-container: '#fdc34d'
  on-secondary-container: '#715000'
  tertiary: '#392800'
  on-tertiary: '#ffffff'
  tertiary-container: '#543d00'
  on-tertiary-container: '#d2a642'
  error: '#8B1A1A'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdadb'
  primary-fixed-dim: '#ffb2b8'
  on-primary-fixed: '#40000f'
  on-primary-fixed-variant: '#832234'
  secondary-fixed: '#ffdea6'
  secondary-fixed-dim: '#f7bd48'
  on-secondary-fixed: '#271900'
  on-secondary-fixed-variant: '#5d4200'
  tertiary-fixed: '#ffdf9f'
  tertiary-fixed-dim: '#eec058'
  on-tertiary-fixed: '#261a00'
  on-tertiary-fixed-variant: '#5b4300'
  background: '#fbf9f6'
  on-background: '#1b1c1a'
  surface-variant: '#e4e2df'
  text-main: '#2C1A1A'
  text-muted: '#8B6B6B'
  border-warm: '#D4B896'
  success: '#2D6A4F'
typography:
  headline-xl:
    fontFamily: Source Serif 4
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Source Serif 4
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-md:
    fontFamily: Source Serif 4
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.04em
  headline-xl-mobile:
    fontFamily: Source Serif 4
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 36px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  container-max: 1200px
  auth-card-width: 440px
  gutter: 24px
  margin-mobile: 16px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 32px
---

## Brand & Style

The design system establishes a bridge between traditional ecclesiastical authority and modern software-as-a-service reliability. It targets an institutional audience—clergy, administrators, and congregants—who require a secure, trustworthy gateway to church resources.

The aesthetic follows a **Corporate / Modern** framework infused with **Traditional / Academic** flourishes. The interface prioritizes clarity and solemnity, using high-quality serif typography for headings to evoke a sense of history and permanence, while the UI components remain strictly functional and clean to ensure a frictionless authentication experience. The emotional response should be one of "Warm Professionalism"—the security of a financial institution paired with the welcoming atmosphere of a sanctuary.

## Colors

The palette is anchored by **Deep Wine Maroon**, a color representing institutional authority and heritage. This is balanced by **Dark Gold** and **Light Gold** accents, which are used sparingly for interactive cues and high-level branding to suggest value and tradition.

The background uses a warm off-white (`#F5F3F0`) rather than a sterile pure white to maintain an inviting, organic feel. Text is rendered in a **Dark Warm Brown** to avoid the harsh contrast of pure black, ensuring the reading experience feels natural and refined. Success and Error states are desaturated to align with the somber, professional tone of the portal.

## Typography

This design system utilizes **Source Serif 4** (a high-quality alternative to Georgia with better digital scaling) for all headings. This provides an academic, literary character that establishes trust. **Inter** is used for all functional UI elements, forms, and body text to ensure maximum legibility and a contemporary SaaS feel.

For mobile displays, large headlines scale down to prevent awkward wrapping, while body sizes remain constant to preserve accessibility. Labels use a slightly tighter weight and increased letter spacing for clarity in dense form environments.

## Layout & Spacing

The layout employs a **Fixed Grid** philosophy for its primary function: the authentication card. On desktop, the central auth container is capped at 440px to focus user attention. For broader administrative dashboards, a 12-column grid is used with 24px gutters.

Spacing follows an 8px rhythmic scale. Vertical stacks use `stack-md` (16px) for related form elements and `stack-lg` (32px) to separate sections or primary actions from secondary links. On mobile, margins reduce to 16px to maximize horizontal real estate for input fields.

## Elevation & Depth

Hierarchy is established through **Tonal Layers** and **Warm-Tinted Shadows**. Surfaces do not use "floating" pure-black shadows; instead, shadows use a subtle maroon tint (`rgba(123, 28, 46, 0.12)`) to integrate the depth with the brand's primary color.

1.  **Level 0 (Background):** `#F5F3F0` — The canvas.
2.  **Level 1 (Cards/Surfaces):** `#FFFFFF` — Sharp 1px border in `#D4B896` with a soft, diffused tinted shadow.
3.  **Level 2 (Active States/Popovers):** Higher blur radius (16px) with slightly increased shadow opacity to indicate temporary interaction.

## Shapes

The design system uses a **Rounded** (8px) language. This radius is applied consistently to buttons, input fields, and container cards. It is soft enough to feel approachable but sharp enough to remain formal and institutional. Decorative elements like dividers or accents should maintain 0px radius or "sharp" ends to contrast with the functional UI components.

## Components

### Buttons
- **Primary:** Solid `#7B1C2E` background with white text. High-contrast, no gradient.
- **Secondary:** Outline in `#B8860B` with matching text. 1.5px border width.
- **Tertiary:** Text-only in `#8B6B6B` for less critical actions like "Cancel" or "Help".

### Form Fields
Inputs utilize a white background with a `#D4B896` border. On focus, the border transitions to the primary maroon with a subtle 2px glow in the same tint. Labels are always positioned above the field in `label-md` Inter.

### Authentication Card
The central piece of the portal. It should feature a top-border accent of 4px in `#B8860B` (Gold) to signify the "premium" nature of the access point.

### Feedback Elements
- **Success Toasts:** Use `#2D6A4F` borders and icons.
- **Error Messages:** Inline text in `#8B1A1A`. 

### Specialized Components
- **SSO Provider Buttons:** Use a neutral light gray background with standard brand icons to differentiate from the primary "Avarewase" login action.
- **Institutional Seal:** A small, low-opacity version of the logo or seal may be used as a background watermark to reinforce brand authenticity.