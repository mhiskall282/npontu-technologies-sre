// lib/core/services/permission_service.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:permission_handler/permission_handler.dart';

/// Result of a permission request — callers decide how to react.
enum PermissionResult { granted, denied, permanentlyDenied }

/// Centralised runtime-permission manager.
///
/// Request permissions through this service so that:
///   - We always show a rationale before the OS dialog.
///   - Permanently-denied permissions redirect to app settings with a dialog.
///   - All logic is testable in isolation (single injection point).
class PermissionService {
  // ─── Public API ────────────────────────────────────────────────────────────

  Future<PermissionResult> requestNotificationPermission(
    BuildContext context,
  ) async {
    return _request(
      context: context,
      permission: Permission.notification,
      rationale:
          'Npontu SRE sends you real-time operational alerts, '
          'incident escalations, and shift handover notifications.',
      rationaleTitle: 'Operational Alerts',
    );
  }

  Future<PermissionResult> requestLocationPermission(
    BuildContext context,
  ) async {
    return _request(
      context: context,
      permission: Permission.locationWhenInUse,
      rationale:
          'Your location is used to tag on-site support activities '
          'and validate engineer check-ins at customer premises.',
      rationaleTitle: 'Location Access',
    );
  }

  Future<PermissionResult> requestCameraPermission(BuildContext context) async {
    return _request(
      context: context,
      permission: Permission.camera,
      rationale:
          'The camera is used to scan equipment QR codes and '
          'attach photographic evidence to support activities.',
      rationaleTitle: 'Camera Access',
    );
  }

  // NOTE: Biometric authentication is not a permission managed by
  // permission_handler. It is handled directly by the local_auth package.
  // See SettingsScreen for the biometric toggle implementation.

  // ─── Internal Logic ────────────────────────────────────────────────────────

  Future<PermissionResult> _request({
    required BuildContext context,
    required Permission permission,
    required String rationale,
    required String rationaleTitle,
  }) async {
    final current = await permission.status;

    if (current.isGranted) return PermissionResult.granted;

    if (current.isPermanentlyDenied) {
      if (context.mounted) {
        await _showPermanentDenialDialog(context, rationaleTitle);
      }
      return PermissionResult.permanentlyDenied;
    }

    // Show rationale before triggering the OS dialog.
    if (context.mounted && current.isDenied) {
      final proceed = await _showRationaleDialog(
        context,
        title: rationaleTitle,
        body: rationale,
      );
      if (!proceed) return PermissionResult.denied;
    }

    final result = await permission.request();

    if (result.isGranted) return PermissionResult.granted;
    if (result.isPermanentlyDenied) {
      if (context.mounted) {
        await _showPermanentDenialDialog(context, rationaleTitle);
      }
      return PermissionResult.permanentlyDenied;
    }
    return PermissionResult.denied;
  }

  Future<bool> _showRationaleDialog(
    BuildContext context, {
    required String title,
    required String body,
  }) async {
    final result = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(title),
        content: Text(body),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Not Now'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            child: const Text('Allow'),
          ),
        ],
      ),
    );
    return result ?? false;
  }

  Future<void> _showPermanentDenialDialog(
    BuildContext context,
    String featureName,
  ) async {
    await showDialog<void>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('$featureName Blocked'),
        content: Text(
          '$featureName permission was previously denied. '
          'Please open App Settings and grant the permission to use this feature.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () async {
              Navigator.of(ctx).pop();
              await openAppSettings();
            },
            child: const Text('Open Settings'),
          ),
        ],
      ),
    );
  }
}

/// Global Riverpod provider for [PermissionService].
final permissionServiceProvider = Provider<PermissionService>(
  (_) => PermissionService(),
);
