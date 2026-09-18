// lib/features/notifications/notifications_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../../core/services/notification_service.dart';
import '../../core/theme/npontu_theme.dart';
import '../../shared/widgets/empty_state.dart';
import '../../shared/widgets/error_retry.dart';
import '../../shared/widgets/skeleton_loader.dart';
import 'notification_controller.dart';
import 'notification_model.dart';
import 'notification_tile.dart';

class NotificationsScreen extends ConsumerWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(notificationControllerProvider);

    final unreadCount = state.valueOrNull?.where((n) => !n.isRead).length ?? 0;

    return Scaffold(
      appBar: AppBar(
        // Show back arrow when push-navigated; default drawer icon otherwise
        leading: Navigator.of(context).canPop()
            ? IconButton(
                icon: const Icon(Icons.arrow_back_rounded),
                tooltip: 'Go Back',
                onPressed: () => Navigator.of(context).pop(),
              )
            : null,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Notifications'),
            if (unreadCount > 0)
              Text(
                '$unreadCount unread',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w400,
                  color: NpontuColors.gold,
                ),
              ),
          ],
        ),
        actions: [
          if (unreadCount > 0)
            IconButton(
              icon: const Icon(Icons.done_all_rounded),
              tooltip: 'Mark all as read',
              color: NpontuColors.gold,
              onPressed: () => ref
                  .read(notificationControllerProvider.notifier)
                  .markAllRead(),
            ),
          IconButton(
            icon: const Icon(Icons.add_alert_rounded),
            tooltip: 'Trigger Test Push Alert',
            onPressed: () async {
              await ref.read(notificationServiceProvider).sendTestAlert();
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text(
                      'SRE Alert dispatched to your device notification tray & lockscreen!',
                    ),
                    backgroundColor: NpontuColors.green,
                    duration: Duration(seconds: 3),
                  ),
                );
              }
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: () => ref.invalidate(notificationControllerProvider),
          ),
        ],
      ),
      body: SafeArea(
        child: state.when(
          loading: () => const SkeletonListPlaceholder(count: 6),
          error: (e, _) => ErrorRetryWidget(
            message: e.toString(),
            onRetry: () => ref.invalidate(notificationControllerProvider),
          ),
          data: (notifications) {
            if (notifications.isEmpty) {
              return const EmptyStateWidget(
                icon: Icons.notifications_off_outlined,
                title: 'All Clear',
                subtitle:
                    'No notifications at the moment. '
                    'Operational alerts will appear here in real time.',
              );
            }

            // Group by date
            final grouped = _groupByDate(notifications);

            return RefreshIndicator(
              color: NpontuColors.green,
              onRefresh: () async =>
                  ref.invalidate(notificationControllerProvider),
              child: ListView.builder(
                itemCount: _countItems(grouped),
                itemBuilder: (ctx, index) {
                  final (isHeader, date, notification) = _itemAt(grouped, index);

                  if (isHeader) {
                    return _DateSeparator(label: date!);
                  }

                  return NotificationTile(
                    notification: notification!,
                    onMarkRead: () => ref
                        .read(notificationControllerProvider.notifier)
                        .markRead(notification.id),
                    onDelete: () => ref
                        .read(notificationControllerProvider.notifier)
                        .deleteNotification(notification.id),
                  );
                },
              ),
            );
          },
        ),
      ),
    );
  }

  // ─── Date-grouping helpers ─────────────────────────────────────────────────

  Map<String, List<NotificationModel>> _groupByDate(
    List<NotificationModel> list,
  ) {
    final grouped = <String, List<NotificationModel>>{};
    for (final n in list) {
      final key = _dateLabel(n.createdAt);
      grouped.putIfAbsent(key, () => []).add(n);
    }
    return grouped;
  }

  String _dateLabel(DateTime dt) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final notifDate = DateTime(dt.year, dt.month, dt.day);
    final diff = today.difference(notifDate).inDays;

    if (diff == 0) return 'Today';
    if (diff == 1) return 'Yesterday';
    return DateFormat('EEEE, d MMMM').format(dt);
  }

  int _countItems(Map<String, List<NotificationModel>> grouped) {
    return grouped.entries.fold(
      0,
      (sum, e) => sum + 1 + e.value.length, // header + items
    );
  }

  (bool isHeader, String? date, NotificationModel? notification) _itemAt(
    Map<String, List<NotificationModel>> grouped,
    int index,
  ) {
    int cursor = 0;
    for (final entry in grouped.entries) {
      if (cursor == index) return (true, entry.key, null);
      cursor++;
      for (final n in entry.value) {
        if (cursor == index) return (false, null, n);
        cursor++;
      }
    }
    throw RangeError('Index $index out of range');
  }
}

class _DateSeparator extends StatelessWidget {
  const _DateSeparator({required this.label});
  final String label;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
      child: Row(
        children: [
          Expanded(
            child: Divider(
              color: isDark ? const Color(0xFF1F2E24) : const Color(0xFFE5E7EB),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 10),
            child: Text(
              label,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: isDark
                    ? NpontuColors.textSecondaryDark
                    : NpontuColors.textSecondaryLight,
                letterSpacing: 0.4,
              ),
            ),
          ),
          Expanded(
            child: Divider(
              color: isDark ? const Color(0xFF1F2E24) : const Color(0xFFE5E7EB),
            ),
          ),
        ],
      ),
    );
  }
}
