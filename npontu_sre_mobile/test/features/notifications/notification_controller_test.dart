// test/features/notifications/notification_controller_test.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:npontu_sre_mobile/features/notifications/notification_controller.dart';
import 'package:npontu_sre_mobile/features/notifications/notification_model.dart';
import 'package:npontu_sre_mobile/core/services/notification_service.dart';

// ── Helpers ──────────────────────────────────────────────────────────────────

NotificationModel _n({int id = 1, bool isRead = false, String type = 'info'}) {
  return NotificationModel(
    id: id,
    title: 'Test Notification $id',
    body: 'Body for notification $id',
    type: type,
    isRead: isRead,
    createdAt: DateTime(2026, 9, 16),
  );
}

// ── Tests ─────────────────────────────────────────────────────────────────────

void main() {
  group('NotificationModel', () {
    test('fromJson parses all fields correctly', () {
      final json = {
        'id': 42,
        'title': 'Incident P1',
        'body': 'System is down',
        'type': 'incident',
        'is_read': false,
        'created_at': '2026-09-16T10:00:00Z',
        'read_at': null,
        'related_type': 'Activity',
        'related_id': 7,
        'data': {'ticket': 'INC-001'},
      };

      final model = NotificationModel.fromJson(json);

      expect(model.id, 42);
      expect(model.title, 'Incident P1');
      expect(model.body, 'System is down');
      expect(model.type, 'incident');
      expect(model.isRead, false);
      expect(model.relatedType, 'Activity');
      expect(model.relatedId, 7);
      expect(model.data?['ticket'], 'INC-001');
    });

    test('fromJson defaults type to info when missing', () {
      final json = {
        'id': 1,
        'title': 'T',
        'body': 'B',
        'is_read': false,
        'created_at': '2026-09-16T10:00:00Z',
      };

      final model = NotificationModel.fromJson(json);
      expect(model.type, 'info');
    });

    test('copyWith preserves existing fields and overrides specified ones', () {
      final original = _n(id: 5, isRead: false);
      final updated = original.copyWith(isRead: true, readAt: DateTime(2026));

      expect(updated.id, 5);
      expect(updated.isRead, true);
      expect(updated.readAt, DateTime(2026));
      expect(updated.title, original.title);
    });
  });

  group('notificationBadgeCountProvider', () {
    // Helper to build a container with a pre-seeded notification list
    ProviderContainer containerWith(List<NotificationModel> data) {
      return ProviderContainer(
        overrides: [
          notificationControllerProvider.overrideWith(
            () => _StubNotificationController(data),
          ),
        ],
      );
    }

    test('returns 0 when notification list is empty', () {
      final container = containerWith([]);
      // Seed the AsyncNotifier to Data state before reading the badge
      container.read(notificationControllerProvider);
      final count = container.read(notificationBadgeCountProvider);
      expect(count, 0);
    });

    test('counts only unread notifications', () async {
      final data = [
        _n(id: 1, isRead: true),
        _n(id: 2, isRead: false),
        _n(id: 3, isRead: false),
        _n(id: 4, isRead: true),
      ];

      final container = containerWith(data);
      // Allow the AsyncNotifier to resolve
      await container.read(notificationControllerProvider.future);
      final count = container.read(notificationBadgeCountProvider);
      expect(count, 2);
    });

    test('returns 0 when all notifications are read', () async {
      final data = [_n(id: 1, isRead: true), _n(id: 2, isRead: true)];

      final container = containerWith(data);
      await container.read(notificationControllerProvider.future);
      final count = container.read(notificationBadgeCountProvider);
      expect(count, 0);
    });
  });
}

// ── Stub Controller ───────────────────────────────────────────────────────────
// Extends NotificationController directly so the override type is compatible.

class _StubNotificationController extends NotificationController {
  _StubNotificationController(this._data);
  final List<NotificationModel> _data;

  @override
  Future<List<NotificationModel>> build() async => _data;

  @override
  Future<void> refreshNotifications() async {}
}
