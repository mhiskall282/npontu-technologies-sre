// lib/core/theme/npontu_theme.dart

import 'package:flutter/material.dart';

class NpontuColors {
  // Primary Npontu Brand Colors
  static const Color green = Color(0xFF1B6B3A);
  static const Color greenLight = Color(0xFF2A8F52);
  static const Color greenDark = Color(0xFF12492A);
  static const Color gold = Color(0xFFF5C518);
  static const Color goldWarm = Color(0xFFE8A500);
  static const Color danger = Color(0xFFE63946);

  // Surfaces & Backgrounds
  static const Color surfaceDark = Color(0xFF0F1A14);
  static const Color surfaceMid = Color(0xFF1A2E22);
  static const Color surfaceCardDark = Color(0xFF16241B);
  static const Color surfaceLight = Color(0xFFF4F7F5);
  static const Color cardLight = Colors.white;

  // Status & Priority Tiers
  static const Color criticalP1 = Color(0xFFE63946); // P1 Red
  static const Color highP2 = Color(0xFFE8A500); // P2 Amber/Gold
  static const Color mediumP3 = Color(0xFF2A8F52); // P3 Green Light
  static const Color lowP4 = Color(0xFF4A7C59); // P4 Muted Green

  // Text Colors
  static const Color textPrimaryLight = Color(0xFF111827);
  static const Color textSecondaryLight = Color(0xFF4B5563);
  static const Color textPrimaryDark = Color(0xFFF9FAFB);
  static const Color textSecondaryDark = Color(0xFF9CA3AF);
}

class NpontuTheme {
  static ThemeData get lightTheme {
    const colorScheme = ColorScheme(
      brightness: Brightness.light,
      primary: NpontuColors.green,
      onPrimary: Colors.white,
      primaryContainer: Color(0xFFD4E8DC),
      onPrimaryContainer: NpontuColors.greenDark,
      secondary: NpontuColors.gold,
      onSecondary: Color(0xFF1F2937),
      secondaryContainer: Color(0xFFFEF3C7),
      onSecondaryContainer: Color(0xFF92400E),
      error: NpontuColors.danger,
      onError: Colors.white,
      errorContainer: Color(0xFFFEE2E2),
      onErrorContainer: Color(0xFF991B1B),
      surface: NpontuColors.surfaceLight,
      onSurface: NpontuColors.textPrimaryLight,
      surfaceContainerHighest: Color(0xFFE5EBE7),
      outline: Color(0xFFD1D5DB),
      outlineVariant: Color(0xFFE5E7EB),
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: NpontuColors.surfaceLight,
      fontFamily: 'Roboto',
      appBarTheme: const AppBarTheme(
        backgroundColor: NpontuColors.green,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        titleSpacing: 0,
        titleTextStyle: TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w700,
          color: Colors.white,
          letterSpacing: -0.3,
          overflow: TextOverflow.ellipsis,
        ),
      ),
      cardTheme: CardThemeData(
        color: Colors.white,
        elevation: 1,
        shadowColor: Colors.black.withAlpha(20),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
          side: const BorderSide(color: Color(0xFFE5E7EB), width: 1),
        ),
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: NpontuColors.green,
          foregroundColor: Colors.white,
          elevation: 1,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          textStyle: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.w600,
            letterSpacing: -0.2,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: NpontuColors.green,
          side: const BorderSide(color: NpontuColors.green, width: 1.5),
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          textStyle: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 14,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: NpontuColors.green, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: NpontuColors.danger),
        ),
        labelStyle: const TextStyle(
          color: NpontuColors.textSecondaryLight,
          fontSize: 14,
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: Colors.white,
        elevation: 8,
        indicatorColor: colorScheme.primaryContainer,
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: NpontuColors.greenDark,
            );
          }
          return const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: NpontuColors.textSecondaryLight,
          );
        }),
      ),
    );
  }

  static ThemeData get darkTheme {
    const colorScheme = ColorScheme(
      brightness: Brightness.dark,
      primary: NpontuColors.greenLight,
      onPrimary: Colors.white,
      primaryContainer: NpontuColors.greenDark,
      onPrimaryContainer: Color(0xFFD4E8DC),
      secondary: NpontuColors.gold,
      onSecondary: Color(0xFF111827),
      secondaryContainer: Color(0xFF78350F),
      onSecondaryContainer: Color(0xFFFEF3C7),
      error: NpontuColors.danger,
      onError: Colors.white,
      errorContainer: Color(0xFF7F1D1D),
      onErrorContainer: Color(0xFFFEE2E2),
      surface: NpontuColors.surfaceDark,
      onSurface: NpontuColors.textPrimaryDark,
      surfaceContainerHighest: NpontuColors.surfaceMid,
      outline: Color(0xFF374151),
      outlineVariant: Color(0xFF1F2937),
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: colorScheme,
      scaffoldBackgroundColor: NpontuColors.surfaceDark,
      fontFamily: 'Roboto',
      appBarTheme: const AppBarTheme(
        backgroundColor: NpontuColors.surfaceMid,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        titleSpacing: 0,
        titleTextStyle: TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w700,
          color: Colors.white,
          letterSpacing: -0.3,
          overflow: TextOverflow.ellipsis,
        ),
      ),
      cardTheme: CardThemeData(
        color: NpontuColors.surfaceCardDark,
        elevation: 2,
        shadowColor: Colors.black.withAlpha(80),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
          side: const BorderSide(color: Color(0xFF1F2E24), width: 1),
        ),
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: NpontuColors.greenLight,
          foregroundColor: Colors.white,
          elevation: 2,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          textStyle: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.w600,
            letterSpacing: -0.2,
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: NpontuColors.surfaceMid,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 14,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFF374151)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFF374151)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(
            color: NpontuColors.greenLight,
            width: 2,
          ),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: NpontuColors.danger),
        ),
        labelStyle: const TextStyle(
          color: NpontuColors.textSecondaryDark,
          fontSize: 14,
        ),
      ),
      navigationBarTheme: NavigationBarThemeData(
        backgroundColor: NpontuColors.surfaceMid,
        elevation: 8,
        indicatorColor: NpontuColors.greenDark,
        labelTextStyle: WidgetStateProperty.resolveWith((states) {
          if (states.contains(WidgetState.selected)) {
            return const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: NpontuColors.gold,
            );
          }
          return const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: NpontuColors.textSecondaryDark,
          );
        }),
      ),
    );
  }
}
