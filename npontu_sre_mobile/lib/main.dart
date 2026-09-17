// lib/main.dart

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'app.dart';
import 'core/services/notification_service.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Enforce portrait + landscape, but prioritise portrait on phones.
  await SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
    DeviceOrientation.landscapeLeft,
    DeviceOrientation.landscapeRight,
  ]);

  // Initialise the local notifications channel before the UI starts.
  // The actual OS permission is requested lazily in PermissionService.
  final notificationService = NotificationService();
  await notificationService.initialise();

  runApp(
    ProviderScope(
      overrides: [
        // Inject the already-initialised instance into the provider graph.
        notificationServiceProvider.overrideWithValue(notificationService),
      ],
      child: const NpontuSreApp(),
    ),
  );
}
