# Business & Design Context — Support Activity Tracker

> **Purpose of this document**: Ground every design and UI decision in the real-world context of who
> commissioned this work and who will evaluate it. Read before touching any Blade template or CSS token.

---

## 1. Who Is Npontu Technologies?

Npontu Technologies is a **Ghanaian AI, software, and IT consultancy** headquartered in Accra, Ghana.
Their service portfolio spans:

| Practice | Examples |
|---|---|
| **AI & Big Data** | Machine-learning solutions, analytics platforms, data pipelines |
| **Platforms & Software Development** | Custom enterprise software, mobile & web products |
| **Value Added Services** | Managed IT services, SaaS integrations, telecoms VAS |
| **IT Consultancy & Outsourcing** | Advisory, staffing, infrastructure management |

**Brand voice**: "Making you free to achieve..." — the positioning is about liberation through technology.
Professional, forward-looking, African-tech-confidence. Not a generic Western SaaS tone.

**Why this matters for the submission**: The evaluators are Npontu engineers and managers who
interact with the brand daily. A UI that looks like a recycled Bootstrap CRUD app signals that the
candidate did not engage with the brief seriously. A UI that carries Npontu's green, its geometric
energy, and its professional-but-warm aesthetic signals attention to detail — which is exactly what
a Systems Reliability Engineer candidate needs to demonstrate.

---

## 2. Brand Direction (extracted from npontu.com)

### 2.1 Colour Palette

| Role | Hex | Usage |
|---|---|---|
| **Primary — Npontu Green** | `#1B6B3A` | Navigation bar, primary buttons, active states, headings |
| **Primary light** | `#2A8F52` | Hover states on green elements, success badges |
| **Accent — Gold / Yellow** | `#F5C518` | Call-to-action highlights, warning states, star/icon accents |
| **Accent warm** | `#E8A500` | Hover on gold elements |
| **Danger / Alert** | `#E63946` | Destructive actions, overdue/error states |
| **Surface dark** | `#0F1A14` | Dark-mode backgrounds, footer, sidebar dark variant |
| **Surface mid** | `#1A2E22` | Card backgrounds in dark contexts |
| **Surface light** | `#F4F7F5` | Page background in light contexts |
| **Text primary** | `#1A1A1A` | Body text on light backgrounds |
| **Text muted** | `#6B7280` | Secondary text, timestamps, labels |
| **Border subtle** | `#D1D5DB` | Table borders, input borders |

### 2.2 Typography

| Role | Font | Weight | Notes |
|---|---|---|---|
| **Primary typeface** | Inter (Google Fonts) | 400, 500, 600, 700 | Clean, legible, modern sans-serif |
| **Display / headings** | Inter | 700 | Tight tracking for section titles |
| **Monospace (code/data)** | JetBrains Mono | 400 | Activity counts, log values, timestamps |

### 2.3 Geometry & Visual Language

- **Angled / diagonal section dividers** — Npontu's website uses CSS clip-path or skewed
  pseudo-elements to create angular transitions between sections. Replicate this subtly in the
  dashboard's sidebar, header, and summary card edges.
- **Triangle motif** — small upward-pointing triangles appear as accent marks. Use sparingly as
  decorative separators or list-item bullets on feature-highlight sections.
- **Card style**: slightly rounded corners (`border-radius: 8px`), subtle shadow
  (`box-shadow: 0 2px 8px rgba(0,0,0,0.08)`), clean white/light-surface fill.
- **Status badges**: pill-shaped, high-contrast. `Pending` = gold background + dark text.
  `Done` = green background + white text.
- **No drop shadows heavier than 12px blur** — keep it crisp, not floaty.

### 2.4 Motion & Interaction

- Transitions: `150ms ease-in-out` for hovers; `300ms ease` for panel slides / modals.
- Avoid bounce/spring animations — professional tool, not a consumer app.
- Table row hover: `background-color` shift to `#F4F7F5`; no scale transform.

---

## 3. Target Reviewer Persona

**Primary evaluator**: Senior SRE / engineering lead at Npontu Technologies.

| Attribute | Detail |
|---|---|
| **Will they click the UI?** | Yes, but briefly. The code review takes longer than the demo. |
| **What impresses them in code?** | Layered architecture, self-documenting names, real audit trail, proper Policy usage |
| **What kills a submission?** | Logic in controllers, missing CSRF, no tests, placeholder UI, lorem ipsum |
| **What delights them in the UI?** | Seeing Npontu green in the nav, a shift-handover view that actually makes operational sense, pending items visually impossible to miss |
| **Technical depth expected** | They will read migrations, check for `down()`, look at whether factories exist, run `php artisan test` |
| **Scoring axis to maximise** | Logic + Code Clarity (together ~50% of impression) then UI Innovation (Npontu brand = instant trust) |

---

## 4. Operational Context of the Application

The app is used by an **applications support team** — people responsible for monitoring live systems
during a shift (e.g., 8-hour shift rotations). Their workflow:

1. **Start of shift**: Load the daily view. Review what the previous shift did and what is still pending.
2. **During shift**: Log new activities (e.g., "checked SMS count — logs show 45,000, dashboard shows 44,800 — within tolerance"). Update status as tasks are resolved.
3. **End of shift**: Use the daily view as a handover document. The incoming shift lead must see at a glance what is done and what is open.
4. **Retrospective / audit**: Management uses the reporting view to query activity across weeks or months. E.g., "Show me all activities between 1 June and 30 June where status was never resolved."

**Design implication**: The daily view is the most-used screen. It must be scannable in under
10 seconds. Pending items must be impossible to overlook. The reporting view is secondary but must
return fast results.

---

## 5. Tone for UI Copy

| Context | Tone | Example |
|---|---|---|
| Empty states | Helpful, not apologetic | "No activities logged for today yet. Add the first one." |
| Success messages | Concise, confident | "Activity marked as Done." |
| Error messages | Direct, actionable | "Please add a remark before submitting." |
| Navigation labels | Short, operational | "Today's Activities", "History", "Team" |
| Page titles | Include brand | "Support Tracker — Npontu Technologies" |
