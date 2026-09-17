// lib/shared/widgets/opsora_logo.dart

import 'dart:math' as math;
import 'package:flutter/material.dart';
import '../../core/theme/npontu_theme.dart';

/// Standalone vector emblem for Opsora SRE.
///
/// Features a hexagonal reliability shield, interlocking infinity loop,
/// and golden telemetry pulse vertices.
class OpsoraIcon extends StatelessWidget {
  final double size;
  final bool animateGlow;

  const OpsoraIcon({
    super.key,
    this.size = 48,
    this.animateGlow = false,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: size,
      height: size,
      child: CustomPaint(
        painter: _OpsoraIconPainter(),
      ),
    );
  }
}

class _OpsoraIconPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;
    final center = Offset(w / 2, h / 2);

    // 1. Hexagonal Boundary / Shield
    final hexPath = Path();
    const sides = 6;
    final radius = w * 0.48;
    for (int i = 0; i < sides; i++) {
      final angle = (i * 2 * math.pi / sides) - (math.pi / 2);
      final x = center.dx + radius * math.cos(angle);
      final y = center.dy + radius * math.sin(angle);
      if (i == 0) {
        hexPath.moveTo(x, y);
      } else {
        hexPath.lineTo(x, y);
      }
    }
    hexPath.close();

    // Shield background gradient
    final shieldPaint = Paint()
      ..shader = const LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [
          Color(0xFF248A4B),
          NpontuColors.green,
          Color(0xFF0F3B20),
        ],
      ).createShader(Rect.fromLTWH(0, 0, w, h))
      ..style = PaintingStyle.fill;
    canvas.drawPath(hexPath, shieldPaint);

    // Shield gold border
    final borderPaint = Paint()
      ..color = NpontuColors.gold.withAlpha(160)
      ..strokeWidth = w * 0.035
      ..style = PaintingStyle.stroke
      ..strokeJoin = StrokeJoin.round;
    canvas.drawPath(hexPath, borderPaint);

    // Inner subtle outline
    final innerPath = Path();
    final innerRadius = w * 0.40;
    for (int i = 0; i < sides; i++) {
      final angle = (i * 2 * math.pi / sides) - (math.pi / 2);
      final x = center.dx + innerRadius * math.cos(angle);
      final y = center.dy + innerRadius * math.sin(angle);
      if (i == 0) {
        innerPath.moveTo(x, y);
      } else {
        innerPath.lineTo(x, y);
      }
    }
    innerPath.close();
    final innerLinePaint = Paint()
      ..color = Colors.white.withAlpha(40)
      ..strokeWidth = 1.0
      ..style = PaintingStyle.stroke;
    canvas.drawPath(innerPath, innerLinePaint);

    // 2. Interlocking Reliability Infinity Wave / S-Loop
    final loopPath = Path();
    final loopWidth = w * 0.24;
    final leftCenter = Offset(center.dx - loopWidth / 2, center.dy);
    final rightCenter = Offset(center.dx + loopWidth / 2, center.dy);

    loopPath.moveTo(center.dx, center.dy);
    loopPath.cubicTo(
      center.dx - loopWidth * 0.4,
      center.dy - loopWidth * 0.6,
      leftCenter.dx - loopWidth * 0.6,
      center.dy - loopWidth * 0.6,
      leftCenter.dx - loopWidth * 0.6,
      center.dy,
    );
    loopPath.cubicTo(
      leftCenter.dx - loopWidth * 0.6,
      center.dy + loopWidth * 0.6,
      center.dx - loopWidth * 0.4,
      center.dy + loopWidth * 0.6,
      center.dx,
      center.dy,
    );
    loopPath.cubicTo(
      center.dx + loopWidth * 0.4,
      center.dy - loopWidth * 0.6,
      rightCenter.dx + loopWidth * 0.6,
      center.dy - loopWidth * 0.6,
      rightCenter.dx + loopWidth * 0.6,
      center.dy,
    );
    loopPath.cubicTo(
      rightCenter.dx + loopWidth * 0.6,
      center.dy + loopWidth * 0.6,
      center.dx + loopWidth * 0.4,
      center.dy + loopWidth * 0.6,
      center.dx,
      center.dy,
    );

    // Loop glow
    final glowPaint = Paint()
      ..color = NpontuColors.gold.withAlpha(90)
      ..strokeWidth = w * 0.12
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 3);
    canvas.drawPath(loopPath, glowPaint);

    // Loop main gold stroke
    final loopPaint = Paint()
      ..shader = const LinearGradient(
        colors: [
          Color(0xFFFFE066),
          NpontuColors.gold,
          Color(0xFFD9A807),
        ],
      ).createShader(Rect.fromLTWH(0, 0, w, h))
      ..strokeWidth = w * 0.075
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    canvas.drawPath(loopPath, loopPaint);

    // 3. Telemetry Pulse Nodes
    final nodeBorderPaint = Paint()
      ..color = NpontuColors.green
      ..strokeWidth = 2.0
      ..style = PaintingStyle.stroke;
    final whitePaint = Paint()..color = Colors.white;

    final leftNode = Offset(leftCenter.dx - loopWidth * 0.45, center.dy);
    final rightNode = Offset(rightCenter.dx + loopWidth * 0.45, center.dy);

    canvas.drawCircle(leftNode, w * 0.055, whitePaint);
    canvas.drawCircle(leftNode, w * 0.055, nodeBorderPaint);

    canvas.drawCircle(rightNode, w * 0.055, whitePaint);
    canvas.drawCircle(rightNode, w * 0.055, nodeBorderPaint);

    // Center Hub Pulsar Node
    final centerHubPaint = Paint()..color = NpontuColors.gold;
    final hubWhiteRing = Paint()
      ..color = Colors.white
      ..strokeWidth = 1.5
      ..style = PaintingStyle.stroke;
    canvas.drawCircle(center, w * 0.065, centerHubPaint);
    canvas.drawCircle(center, w * 0.065, hubWhiteRing);

    // Observability North/South Nodes
    final rayPaint = Paint()
      ..color = NpontuColors.gold.withAlpha(220)
      ..style = PaintingStyle.fill;
    canvas.drawCircle(Offset(center.dx, center.dy - w * 0.26), w * 0.035, rayPaint);
    canvas.drawCircle(Offset(center.dx, center.dy + w * 0.26), w * 0.035, rayPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

/// Full Opsora SRE Logo with wordmark and optional platform subtext.
class OpsoraLogo extends StatelessWidget {
  final double iconSize;
  final bool showSubtext;
  final Color textColor;
  final CrossAxisAlignment crossAxisAlignment;

  const OpsoraLogo({
    super.key,
    this.iconSize = 36,
    this.showSubtext = true,
    this.textColor = Colors.white,
    this.crossAxisAlignment = CrossAxisAlignment.center,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: crossAxisAlignment,
      children: [
        OpsoraIcon(size: iconSize),
        SizedBox(width: iconSize * 0.28),
        Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  'OPSORA',
                  style: TextStyle(
                    fontSize: iconSize * 0.52,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.2,
                    color: textColor,
                    height: 1.0,
                  ),
                ),
                const SizedBox(width: 5),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                  decoration: BoxDecoration(
                    color: NpontuColors.gold.withAlpha(40),
                    borderRadius: BorderRadius.circular(4),
                    border: Border.all(
                      color: NpontuColors.gold.withAlpha(150),
                      width: 1.0,
                    ),
                  ),
                  child: Text(
                    'SRE',
                    style: TextStyle(
                      fontFamily: 'monospace',
                      fontSize: iconSize * 0.32,
                      fontWeight: FontWeight.w900,
                      color: NpontuColors.gold,
                      letterSpacing: 1.0,
                      height: 1.0,
                    ),
                  ),
                ),
              ],
            ),
            if (showSubtext) ...[
              const SizedBox(height: 3),
              Text(
                'RELIABILITY OPERATIONS',
                style: TextStyle(
                  fontSize: iconSize * 0.22,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 2.0,
                  color: NpontuColors.greenLight,
                  height: 1.0,
                ),
              ),
            ],
          ],
        ),
      ],
    );
  }
}
