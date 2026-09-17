// lib/app.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'core/constants/app_constants.dart';
import 'core/routing/app_router.dart';
import 'core/theme/npontu_theme.dart';
import 'features/settings/settings_controller.dart';

class NpontuSreApp extends ConsumerWidget {
  const NpontuSreApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(appRouterProvider);
    // Reacts to the user's theme preference from SettingsController.
    final themeMode = ref.watch(appThemeModeProvider);

    return MaterialApp.router(
      title: AppConstants.appName,
      debugShowCheckedModeBanner: false,
      theme: NpontuTheme.lightTheme,
      darkTheme: NpontuTheme.darkTheme,
      themeMode: themeMode,
      routerConfig: router,
    );
  }
}
