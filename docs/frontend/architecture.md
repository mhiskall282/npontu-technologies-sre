# Opsora Frontend — Blade & Livewire 3 Architecture

> **Status:** IMPLEMENTED  
> **Styling:** Tailwind CSS v3  
> **Reactive Engine:** Livewire 3

Opsora utilizes server-rendered Blade templates combined with Livewire 3 reactive components to achieve instant, reactive operational board filtering without the build complexity of client-side single page applications (SPAs).

---

## 1. Key Reactive Livewire Components
1. **`DailyActivityBoard` (`app/Livewire/DailyActivityBoard.php`)**:
   - Manages real-time filtering by recurrence (`daily`, `weekly`, `monthly`), status (`pending`, `done`), and assignment (`assigned to me`, `unassigned`).
   - Supports inline checklist updates, priority updates, and bulk task delegation for supervisors.
2. **`OperationalChat` (`app/Livewire/OperationalChat.php`)**:
   - Manages team channels, war rooms, and 1-on-1 direct messaging.
   - Handles file attachments (images, PDFs) and `@mention` notifications.

---

## 2. Npontu / Opsora Brand Design Tokens
- **Primary Green**: `#1B6B3A` (Tailwind `text-[#1B6B3A]`, `bg-[#1B6B3A]`)
- **Accent Yellow/Gold**: `#F5C518` (Alerts, badges, highlighted actions)
- **Danger Red**: `#E63946` (Incidents, critical priorities, rejections)
- **Background Slate**: `#08120B` (Dark mode operations cockpit)
- **Typography**: Inter (sans-serif) + JetBrains Mono (monospace identifiers, code, and timestamps)
