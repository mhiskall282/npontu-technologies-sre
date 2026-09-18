# ADR-003: Flutter Multi-Workspace Client Architecture

## Status
Accepted

## Context
The existing Flutter mobile app needed to support multi-tenant workspace discovery, switching, and company code onboarding while maintaining 100% backward compatibility with Android/iOS targets.

## Decision
1. Introduce `WorkspaceModel` and `WorkspaceController` (ChangeNotifier / Riverpod friendly) to manage active workspace state.
2. Intercept all outbound HTTP requests in `ApiClient` to attach `X-Workspace-Id`.
3. Persist the active workspace ID in `FlutterSecureStorage` across application restarts.
4. Implement `WorkspaceSwitcherSheet` bottom modal for seamless 1-tap switching and company code input.

## Consequences
- **Positive**: Zero backend rewriting required; instant multi-tenant awareness in mobile; all 25 unit/widget tests pass.
- **Negative**: Offline caching must invalidate when switching between workspaces.

## Date
2026-09-18
