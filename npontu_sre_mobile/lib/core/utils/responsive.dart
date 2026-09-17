// lib/core/utils/responsive.dart

import 'package:flutter/material.dart';

/// Breakpoint constants and helpers for adaptive layouts.
///
/// Phone:   width < 600 dp  → Drawer navigation, single-column
/// Tablet:  600 ≤ width < 1024 dp → NavigationRail, two-column
/// Desktop: width ≥ 1024 dp → NavigationDrawer, wide layouts
abstract final class Responsive {
  static const double _tabletBreakpoint = 600;
  static const double _desktopBreakpoint = 1024;

  static bool isPhone(BuildContext context) =>
      MediaQuery.sizeOf(context).width < _tabletBreakpoint;

  static bool isTablet(BuildContext context) {
    final width = MediaQuery.sizeOf(context).width;
    return width >= _tabletBreakpoint && width < _desktopBreakpoint;
  }

  static bool isDesktop(BuildContext context) =>
      MediaQuery.sizeOf(context).width >= _desktopBreakpoint;

  static bool isTabletOrDesktop(BuildContext context) =>
      MediaQuery.sizeOf(context).width >= _tabletBreakpoint;

  /// Returns the number of grid columns appropriate for the current screen.
  static int gridColumns(
    BuildContext context, {
    int phone = 1,
    int tablet = 2,
    int desktop = 3,
  }) {
    if (isDesktop(context)) return desktop;
    if (isTablet(context)) return tablet;
    return phone;
  }

  /// Horizontal page padding: larger on tablets to avoid overly-wide content.
  static double horizontalPadding(BuildContext context) {
    final width = MediaQuery.sizeOf(context).width;
    if (width >= _desktopBreakpoint) return width * 0.12;
    if (width >= _tabletBreakpoint) return 32.0;
    return 16.0;
  }
}
