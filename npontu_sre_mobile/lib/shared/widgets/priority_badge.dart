// lib/shared/widgets/priority_badge.dart

import 'package:flutter/material.dart';

import '../../core/theme/npontu_theme.dart';

class PriorityBadge extends StatelessWidget {
  final String priority;
  final bool compact;

  const PriorityBadge({
    super.key,
    required this.priority,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color fg = Colors.white;
    String label;

    switch (priority.toLowerCase()) {
      case 'critical':
        bg = NpontuColors.criticalP1;
        label = compact ? 'P1' : 'P1 CRITICAL';
        break;
      case 'high':
        bg = NpontuColors.highP2;
        label = compact ? 'P2' : 'P2 HIGH';
        break;
      case 'medium':
        bg = NpontuColors.mediumP3;
        label = compact ? 'P3' : 'P3 MEDIUM';
        break;
      case 'low':
      default:
        bg = NpontuColors.lowP4;
        label = compact ? 'P4' : 'P4 LOW';
        break;
    }

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: compact ? 6 : 8,
        vertical: compact ? 2 : 3,
      ),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(4),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: compact ? 9 : 10,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.3,
          color: fg,
        ),
      ),
    );
  }
}
