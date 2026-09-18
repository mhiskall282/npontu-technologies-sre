// lib/shared/widgets/opsora_loader.dart

import 'package:flutter/material.dart';
import '../../core/theme/npontu_theme.dart';
import 'opsora_logo.dart';

/// A custom, high-tech SRE loading widget replacing generic platform spinners.
///
/// Combines the glowing Opsora reliability emblem with a smooth breathing
/// halo and optional operational status text.
class OpsoraPulseLoader extends StatefulWidget {
  final double size;
  final String? message;
  final bool fullScreen;

  const OpsoraPulseLoader({
    super.key,
    this.size = 48,
    this.message,
    this.fullScreen = false,
  });

  @override
  State<OpsoraPulseLoader> createState() => _OpsoraPulseLoaderState();
}

class _OpsoraPulseLoaderState extends State<OpsoraPulseLoader>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnim;
  late Animation<double> _opacityAnim;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat(reverse: true);

    _scaleAnim = Tween<double>(begin: 0.90, end: 1.08).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOutSine),
    );

    _opacityAnim = Tween<double>(begin: 0.65, end: 1.0).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOutSine),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final content = AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Stack(
              alignment: Alignment.center,
              children: [
                // Ambient emerald-gold halo
                Container(
                  width: widget.size * 1.5 * _scaleAnim.value,
                  height: widget.size * 1.5 * _scaleAnim.value,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: RadialGradient(
                      colors: [
                        NpontuColors.green.withAlpha((_opacityAnim.value * 70).toInt()),
                        Colors.transparent,
                      ],
                    ),
                  ),
                ),
                // Glowing Opsora Shield Emblem
                Transform.scale(
                  scale: _scaleAnim.value,
                  child: Opacity(
                    opacity: _opacityAnim.value,
                    child: OpsoraIcon(size: widget.size),
                  ),
                ),
              ],
            ),
            if (widget.message != null && widget.message!.isNotEmpty) ...[
              const SizedBox(height: 14),
              Text(
                widget.message!,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  letterSpacing: 0.5,
                  color: isDark
                      ? NpontuColors.textSecondaryDark
                      : NpontuColors.textSecondaryLight,
                ),
              ),
            ],
          ],
        );
      },
    );

    if (widget.fullScreen) {
      return Container(
        color: isDark ? const Color(0xFF07100B) : Colors.white,
        alignment: Alignment.center,
        child: content,
      );
    }

    return Center(child: content);
  }
}
