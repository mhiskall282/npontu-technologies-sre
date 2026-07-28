---
name: npontu-brand-tokens
description: Npontu Technologies brand design tokens for Tailwind CSS v3. Use this skill for all Blade/Livewire templates.
---

# Npontu Brand Tokens - Tailwind CSS v3

## 1. tailwind.config.js Token Definitions

Add to `theme.extend` in tailwind.config.js:

```js
colors: {
  npontu: {
    green:           "#1B6B3A",  // Primary - nav, buttons, done badges
    "green-light":   "#2A8F52",  // Hover on green elements
    "green-dark":    "#12492A",  // Active/pressed green
    gold:            "#F5C518",  // Accent - pending badges, highlights
    "gold-warm":     "#E8A500",  // Hover on gold elements
    danger:          "#E63946",  // Destructive actions, errors, overdue
    "surface-dark":  "#0F1A14",  // Sidebar dark, footer
    "surface-mid":   "#1A2E22",  // Card bg in dark contexts
    "surface-light": "#F4F7F5",  // Page background light mode
  },
},
fontFamily: {
  sans: ["Inter", "ui-sans-serif", "system-ui"],
  mono: ["JetBrains Mono", "ui-monospace", "Courier New"],
},
borderRadius: {
  card:  "8px",
  badge: "9999px",
},
boxShadow: {
  card:         "0 2px 8px rgba(0,0,0,0.08)",
  "card-hover": "0 4px 16px rgba(0,0,0,0.12)",
},
```

## 2. Google Fonts Import

In `resources/css/app.css` (before Tailwind directives):

```css
/* Inter + JetBrains Mono from Google Fonts */
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400&display=swap");

@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  body { @apply font-sans text-gray-900 bg-npontu-surface-light; }
  h1, h2, h3 { @apply font-bold tracking-tight; }
}
```

## 3. Component Recipes

### Primary Button
```html
<button class="bg-npontu-green hover:bg-npontu-green-light text-white
               font-semibold px-5 py-2.5 rounded-card transition-colors
               duration-150 shadow-sm">
  Save Activity
</button>
```

### Danger/Destructive Button
```html
<button class="bg-npontu-danger hover:opacity-90 text-white font-semibold
               px-5 py-2.5 rounded-card transition-opacity duration-150 shadow-sm">
  Delete
</button>
```

### Status Badge - Pending
```html
<span class="inline-flex items-center bg-npontu-gold text-gray-900
             text-xs font-semibold px-3 py-1 rounded-badge">
  Pending
</span>
```

### Status Badge - Done
```html
<span class="inline-flex items-center bg-npontu-green text-white
             text-xs font-semibold px-3 py-1 rounded-badge">
  Done
</span>
```

### Card
```html
<div class="bg-white rounded-card shadow-card p-6
            hover:shadow-card-hover transition-shadow duration-300">
  <!-- content -->
</div>
```

### Navigation Bar
```html
<nav class="bg-npontu-green text-white px-6 py-4 flex items-center justify-between">
  <a href="/" class="font-bold text-lg tracking-tight">
    Support Tracker
    <span class="text-npontu-gold ml-1 text-sm font-normal">by Npontu</span>
  </a>
  <div class="flex items-center gap-6 text-sm font-medium">
    <a href="{{ route('activities.index') }}"
       class="hover:text-npontu-gold transition-colors duration-150">Today</a>
    <a href="{{ route('reports.index') }}"
       class="hover:text-npontu-gold transition-colors duration-150">History</a>
    <a href="{{ route('admin.users.index') }}"
       class="hover:text-npontu-gold transition-colors duration-150">Team</a>
  </div>
</nav>
```

### Angled Section Divider (matches Npontu website geometry)
```html
<!-- Use inline style; no extra Tailwind plugin needed -->
<div class="bg-npontu-green" style="clip-path: polygon(0 0, 100% 0, 100% 88%, 0 100%); padding-bottom: 4rem;">
  <!-- hero / header content -->
</div>
```

## 4. Colour Usage Rules

| Token | Allowed uses | Prohibited uses |
|---|---|---|
| npontu-green | Nav, primary buttons, sidebar, done badges | Body text (contrast) |
| npontu-gold | Pending badges, warning icons, accent highlights | Primary action buttons |
| npontu-danger | Delete buttons, error states, overdue items | Decorative elements |
| npontu-surface-dark | Sidebar dark variant, footer | Inline cards in light mode |
| gray-* (Tailwind) | Body text, muted labels, borders | Primary brand moments |

## 5. Shift Handover View - Visual Priority Rules

The daily board (FR-4) must make **pending items visually unmissable**:

1. Render pending activities ABOVE done activities - never interleaved.
2. Pending row: `border-l-4 border-npontu-gold bg-amber-50`
3. Done row: `opacity-75 bg-gray-50`
4. Pending section heading: `text-npontu-danger font-bold text-sm uppercase tracking-widest`
5. Pending count badge: `bg-npontu-danger text-white rounded-badge px-2 py-0.5 text-xs`

## 6. Typography Scale

| Use case | Tailwind classes |
|---|---|
| Page title (h1) | `text-2xl font-bold text-gray-900` |
| Section heading (h2) | `text-lg font-semibold text-npontu-green` |
| Card title | `text-base font-semibold text-gray-800` |
| Body text | `text-sm text-gray-700` |
| Muted / timestamp | `text-xs text-gray-500 font-mono` |
| Activity count / data | `text-sm font-mono text-gray-800` |
| Error message | `text-xs text-npontu-danger` |

## 7. Motion & Interaction Standards

- Hover transitions: `transition-colors duration-150` (150ms ease-in-out)
- Panel slides / modals: `transition-all duration-300` (300ms ease)
- Table row hover: `hover:bg-npontu-surface-light` - no scale transforms
- Focus ring: `focus:outline-none focus:ring-2 focus:ring-npontu-green focus:ring-offset-2`
