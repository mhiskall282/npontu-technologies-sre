// lib/shared/widgets/status_badge.dart

import 'package:flutter/material.dart';

import '../../core/theme/npontu_theme.dart';

class StatusBadge extends StatelessWidget {
  final String status;
  final double fontSize;
  final bool compact;

  const StatusBadge({
    super.key,
    required this.status,
    this.fontSize = 11,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color fg = Colors.white;
    String label;
    IconData icon;

    switch (status.toLowerCase()) {
      case 'done':
        bg = NpontuColors.green;
        label = 'DONE';
        icon = Icons.check_circle_rounded;
        break;
      case 'acknowledged':
      case 'accepted':
        bg = NpontuColors.green;
        label = compact ? 'ACK' : 'ACKNOWLEDGED';
        icon = Icons.how_to_reg_rounded;
        break;
      case 'in_progress':
        bg = Colors.blue;
        label = compact ? 'PROG' : 'IN PROGRESS';
        icon = Icons.autorenew_rounded;
        break;
      case 'skipped':
        bg = Colors.grey;
        label = 'SKIPPED';
        icon = Icons.next_plan_outlined;
        break;
      case 'pending':
      case 'initiated':
      default:
        bg = NpontuColors.gold;
        fg = const Color(0xFF1F2937);
        label = compact ? 'PEND' : status.toUpperCase();
        icon = Icons.pending_rounded;
        break;
    }

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: compact ? 6 : 10,
        vertical: compact ? 2 : 4,
      ),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(9999),
        boxShadow: [
          BoxShadow(
            color: bg.withAlpha(40),
            blurRadius: 4,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: fontSize + (compact ? 0 : 2), color: fg),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: compact ? fontSize - 1 : fontSize,
              fontWeight: FontWeight.w700,
              letterSpacing: 0.5,
              color: fg,
            ),
          ),
        ],
      ),
    );
  }
}
