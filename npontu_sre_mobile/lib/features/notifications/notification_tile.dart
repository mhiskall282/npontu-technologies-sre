// lib/features/notifications/notification_tile.dart

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../core/theme/npontu_theme.dart';
import 'notification_model.dart';

class NotificationTile extends StatelessWidget {
  const NotificationTile({
    super.key,
    required this.notification,
    required this.onMarkRead,
    required this.onDelete,
  });

  final NotificationModel notification;
  final VoidCallback onMarkRead;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final isUnread = !notification.isRead;

    return Dismissible(
      key: ValueKey(notification.id),
      direction: DismissDirection.endToStart,
      background: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: 20),
        color: NpontuColors.danger,
        child: const Icon(Icons.delete_rounded, color: Colors.white),
      ),
      onDismissed: (_) => onDelete(),
      child: InkWell(
        onTap: isUnread ? onMarkRead : null,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            color: isUnread
                ? (isDark
                      ? NpontuColors.greenDark.withAlpha(60)
                      : NpontuColors.green.withAlpha(12))
                : Colors.transparent,
            border: Border(
              bottom: BorderSide(
                color: isDark
                    ? const Color(0xFF1F2E24)
                    : const Color(0xFFE5E7EB),
              ),
            ),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _TypeIcon(type: notification.type, isUnread: isUnread),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            notification.title,
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: isUnread
                                  ? FontWeight.w700
                                  : FontWeight.w500,
                              color: isDark
                                  ? NpontuColors.textPrimaryDark
                                  : NpontuColors.textPrimaryLight,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text(
                          _formatTimestamp(notification.createdAt),
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark
                                ? NpontuColors.textSecondaryDark
                                : NpontuColors.textSecondaryLight,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 3),
                    Text(
                      notification.body,
                      style: TextStyle(
                        fontSize: 13,
                        color: isDark
                            ? NpontuColors.textSecondaryDark
                            : NpontuColors.textSecondaryLight,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (isUnread)
                      const Padding(
                        padding: EdgeInsets.only(top: 6),
                        child: Row(
                          children: [
                            Icon(
                              Icons.fiber_manual_record,
                              size: 8,
                              color: NpontuColors.green,
                            ),
                            SizedBox(width: 4),
                            Text(
                              'Tap to mark as read',
                              style: TextStyle(
                                fontSize: 11,
                                color: NpontuColors.green,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatTimestamp(DateTime dt) {
    final now = DateTime.now();
    final diff = now.difference(dt);

    if (diff.inMinutes < 1) return 'Just now';
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return DateFormat('d MMM').format(dt);
  }
}

class _TypeIcon extends StatelessWidget {
  const _TypeIcon({required this.type, required this.isUnread});

  final String type;
  final bool isUnread;

  @override
  Widget build(BuildContext context) {
    final (icon, color) = _iconForType(type);

    return Container(
      padding: const EdgeInsets.all(9),
      decoration: BoxDecoration(
        color: color.withAlpha(isUnread ? 30 : 15),
        shape: BoxShape.circle,
      ),
      child: Icon(icon, size: 18, color: color),
    );
  }

  (IconData, Color) _iconForType(String type) {
    switch (type) {
      case 'incident':
        return (Icons.warning_amber_rounded, NpontuColors.criticalP1);
      case 'escalation':
        return (Icons.escalator_warning_rounded, NpontuColors.danger);
      case 'handover':
        return (Icons.swap_horiz_rounded, NpontuColors.goldWarm);
      case 'assignment':
        return (Icons.assignment_ind_rounded, NpontuColors.greenLight);
      case 'system':
        return (Icons.settings_rounded, NpontuColors.textSecondaryLight);
      case 'warning':
        return (Icons.error_outline_rounded, NpontuColors.highP2);
      default:
        return (Icons.notifications_outlined, NpontuColors.green);
    }
  }
}
