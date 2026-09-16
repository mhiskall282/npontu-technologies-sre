// lib/shared/widgets/priority_badge.dart

import 'package:flutter/material.dart';

import '../../core/theme/npontu_theme.dart';

class PriorityBadge extends StatelessWidget {
  final String priority;

  const PriorityBadge({super.key, required this.priority});

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color fg = Colors.white;
    String label;

    switch (priority.toLowerCase()) {
      case 'critical':
        bg = NpontuColors.criticalP1;
        label = 'P1 CRITICAL';
        break;
      case 'high':
        bg = NpontuColors.highP2;
        label = 'P2 HIGH';
        break;
      case 'medium':
        bg = NpontuColors.mediumP3;
        label = 'P3 MEDIUM';
        break;
      case 'low':
      default:
        bg = NpontuColors.lowP4;
        label = 'P4 LOW';
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.3,
          color: fg,
        ),
      ),
    );
  }
}
