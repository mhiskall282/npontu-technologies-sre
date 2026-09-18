# ADR-002: Modular Monolith with Blade and Livewire 3

## Status
Accepted

## Context
Choosing between an SPA architecture (Inertia + React/Vue) versus server-driven reactive components (Blade + Livewire 3).

## Decision
Retain and build upon Laravel 11 LTS with Blade and Livewire 3:
1. Livewire 3 provides reactive client-side reactivity (e.g. real-time shift board filtering and operational chat polling) without requiring a separate JavaScript build pipeline or SPA state management overhead.
2. Blade components guarantee fast server-side rendering, SEO, and immediate visual consistency with Npontu/Opsora design tokens.

## Consequences
- **Positive**: Single language stack (PHP 8.2+), low maintenance complexity, high performance on low-spec infrastructure.
- **Negative**: High-frequency real-time animations require careful use of wire:poll intervals.

## Date
2026-09-18
