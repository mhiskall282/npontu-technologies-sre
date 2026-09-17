// lib/features/notifications/notification_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/api_client_provider.dart';
import 'notification_model.dart';

/// AsyncNotifier that manages the notification list.
///
/// Endpoints consumed:
///   GET  /api/v1/notifications         → list (unread-first)
///   POST /api/v1/notifications/{id}/read  → mark one as read
///   POST /api/v1/notifications/read-all   → mark all as read
class NotificationController extends AsyncNotifier<List<NotificationModel>> {
  @override
  Future<List<NotificationModel>> build() async {
    return _fetchNotifications();
  }

  // ─── Data Fetching ─────────────────────────────────────────────────────────

  Future<List<NotificationModel>> _fetchNotifications() async {
    final client = ref.read(apiClientProvider);
    final response = await client.get<Map<String, dynamic>>(
      '/notifications',
    );

    final rawList = response.data?['data'] as List<dynamic>? ?? [];
    return rawList
        .map((e) => NotificationModel.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  /// Called externally (e.g., polling timer) to refresh without showing loading.
  Future<void> refreshNotifications() async {
    final fresh = await _fetchNotifications();
    state = AsyncData(fresh);
  }

  // ─── Mutations ─────────────────────────────────────────────────────────────

  Future<void> markRead(int notificationId) async {
    final client = ref.read(apiClientProvider);

    // Optimistic update
    state = state.whenData(
      (list) => list
          .map(
            (n) => n.id == notificationId
                ? n.copyWith(isRead: true, readAt: DateTime.now())
                : n,
          )
          .toList(),
    );

    try {
      await client.post<void>('/notifications/$notificationId/read');
    } catch (_) {
      // Revert on failure by re-fetching
      state = AsyncLoading();
      state = await AsyncValue.guard(_fetchNotifications);
    }
  }

  Future<void> markAllRead() async {
    final client = ref.read(apiClientProvider);

    // Optimistic update
    state = state.whenData(
      (list) => list
          .map((n) => n.copyWith(isRead: true, readAt: DateTime.now()))
          .toList(),
    );

    try {
      await client.post<void>('/notifications/read-all');
    } catch (_) {
      state = AsyncLoading();
      state = await AsyncValue.guard(_fetchNotifications);
    }
  }

  Future<void> deleteNotification(int notificationId) async {
    final client = ref.read(apiClientProvider);

    // Optimistic update
    state = state.whenData(
      (list) => list.where((n) => n.id != notificationId).toList(),
    );

    try {
      await client.delete<void>('/notifications/$notificationId');
    } catch (_) {
      state = AsyncLoading();
      state = await AsyncValue.guard(_fetchNotifications);
    }
  }
}

final notificationControllerProvider =
    AsyncNotifierProvider<NotificationController, List<NotificationModel>>(
      NotificationController.new,
    );
