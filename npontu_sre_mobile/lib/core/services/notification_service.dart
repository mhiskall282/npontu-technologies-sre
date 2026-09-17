// lib/core/services/notification_service.dart

import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../features/notifications/notification_controller.dart';

/// Wraps `flutter_local_notifications` and provides OS-level notification dispatch.
///
/// Also owns the periodic polling timer that syncs unread count from the API.
class NotificationService {
  static const String _channelId = 'npontu_sre_ops';
  static const String _channelName = 'SRE Operational Alerts';
  static const String _channelDesc =
      'Real-time operational alerts, incident escalations, and shift handovers.';

  final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();

  Timer? _pollingTimer;
  final Set<int> _notifiedIds = {};

  // ─── Initialisation ────────────────────────────────────────────────────────

  Future<void> initialise() async {
    const androidInit = AndroidInitializationSettings('@mipmap/ic_launcher');
    const darwinInit = DarwinInitializationSettings(
      requestAlertPermission: false, // We handle this via PermissionService
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    const initSettings = InitializationSettings(
      android: androidInit,
      iOS: darwinInit,
    );

    await _plugin.initialize(initSettings);

    // Create Android notification channel
    const channel = AndroidNotificationChannel(
      _channelId,
      _channelName,
      description: _channelDesc,
      importance: Importance.max,
      playSound: true,
      enableVibration: true,
    );

    await _plugin
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >()
        ?.createNotificationChannel(channel);
  }

  // ─── Local Notification Dispatch ──────────────────────────────────────────

  Future<void> showLocalNotification({
    required int id,
    required String title,
    required String body,
    String? payload,
    NotificationImportance importance = NotificationImportance.high,
  }) async {
    final androidDetails = AndroidNotificationDetails(
      _channelId,
      _channelName,
      channelDescription: _channelDesc,
      importance: _mapImportance(importance),
      priority: Priority.high,
      icon: '@mipmap/ic_launcher',
      styleInformation: BigTextStyleInformation(body),
    );

    const darwinDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    final details = NotificationDetails(
      android: androidDetails,
      iOS: darwinDetails,
    );

    await _plugin.show(id, title, body, details, payload: payload);
  }

  Importance _mapImportance(NotificationImportance imp) {
    switch (imp) {
      case NotificationImportance.critical:
        return Importance.max;
      case NotificationImportance.high:
        return Importance.high;
      case NotificationImportance.normal:
        return Importance.defaultImportance;
      case NotificationImportance.low:
        return Importance.low;
    }
  }

  // ─── Polling ───────────────────────────────────────────────────────────────

  /// Start a 30-second polling timer.
  /// [ref] is used to read the notification controller and push OS notifications.
  void startPolling(WidgetRef ref) {
    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 30), (_) async {
      try {
        await ref
            .read(notificationControllerProvider.notifier)
            .refreshNotifications();

        final notifications = ref.read(notificationControllerProvider).value;
        if (notifications == null) return;

        for (final n in notifications) {
          if (!n.isRead && !_notifiedIds.contains(n.id)) {
            _notifiedIds.add(n.id);
            await showLocalNotification(
              id: n.id,
              title: n.title,
              body: n.body,
              payload: n.id.toString(),
              importance: _mapApiImportance(n.type),
            );
          }
        }
      } catch (e) {
        debugPrint('[NotificationService] Polling error: $e');
      }
    });
  }

  NotificationImportance _mapApiImportance(String type) {
    switch (type) {
      case 'incident':
      case 'escalation':
        return NotificationImportance.critical;
      case 'handover':
      case 'assignment':
        return NotificationImportance.high;
      default:
        return NotificationImportance.normal;
    }
  }

  void stopPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
  }

  void dispose() {
    stopPolling();
  }
}

enum NotificationImportance { critical, high, normal, low }

/// Global provider for [NotificationService].
final notificationServiceProvider = Provider<NotificationService>(
  (_) => NotificationService(),
);

/// Computed provider: total unread notification count (for badges).
final notificationBadgeCountProvider = Provider<int>((ref) {
  final notifications = ref.watch(notificationControllerProvider);
  return notifications.when(
    data: (list) => list.where((n) => !n.isRead).length,
    loading: () => 0,
    error: (_, __) => 0,
  );
});
