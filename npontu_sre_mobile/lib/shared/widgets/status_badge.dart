// lib/shared/widgets/status_badge.dart

import 'package:flutter/material.dart';

import '../../core/theme/npontu_theme.dart';

class StatusBadge extends StatelessWidget {
  final String status;
  final double fontSize;

  const StatusBadge({super.key, required this.status, this.fontSize = 11});

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
        label = 'ACKNOWLEDGED';
        icon = Icons.how_to_reg_rounded;
        break;
      case 'in_progress':
        bg = Colors.blue;
        label = 'IN PROGRESS';
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
        label = status.toUpperCase();
        icon = Icons.pending_rounded;
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
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
          Icon(icon, size: fontSize + 2, color: fg),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: fontSize,
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
