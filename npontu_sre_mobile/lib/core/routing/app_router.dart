// lib/core/routing/app_router.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/activities/activities_screen.dart';
import '../../features/activities/activity_detail_screen.dart';
import '../../features/activities/activity_form_screen.dart';
import '../../features/audit/audit_screen.dart';
import '../../features/auth/presentation/auth_controller.dart';
import '../../features/auth/presentation/login_screen.dart';
import '../../features/dashboard/dashboard_screen.dart';
import '../../features/handovers/handover_form_screen.dart';
import '../../features/handovers/handovers_screen.dart';
import '../../features/health/health_screen.dart';
import '../../features/messaging/chat_screen.dart';
import '../../features/messaging/messaging_screen.dart';
import '../../features/reports/reports_screen.dart';
import '../../features/team/team_screen.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authControllerProvider);

  return GoRouter(
    initialLocation: '/',
    redirect: (context, state) {
      final isLoggingIn = state.matchedLocation == '/login';
      final isLoggedIn = authState.isAuthenticated;

      // While initializing token from secure storage, stay on current route
      if (authState.isLoading && !authState.isAuthenticated) {
        return null;
      }

      if (!isLoggedIn && !isLoggingIn) {
        return '/login';
      }

      if (isLoggedIn && isLoggingIn) {
        return '/';
      }

      return null;
    },
    routes: [
      GoRoute(path: '/login', builder: (context, state) => const LoginScreen()),
      GoRoute(path: '/', builder: (context, state) => const DashboardScreen()),
      GoRoute(
        path: '/activities',
        builder: (context, state) => const ActivitiesScreen(),
        routes: [
          GoRoute(
            path: 'new',
            builder: (context, state) => const ActivityFormScreen(),
          ),
          GoRoute(
            path: ':id',
            builder: (context, state) {
              final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
              return ActivityDetailScreen(activityId: id);
            },
            routes: [
              GoRoute(
                path: 'edit',
                builder: (context, state) {
                  final id =
                      int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                  return ActivityFormScreen(activityId: id);
                },
              ),
            ],
          ),
        ],
      ),
      GoRoute(
        path: '/handovers',
        builder: (context, state) => const HandoversScreen(),
        routes: [
          GoRoute(
            path: 'new',
            builder: (context, state) => const HandoverFormScreen(),
          ),
        ],
      ),
      GoRoute(
        path: '/messaging',
        builder: (context, state) => const MessagingScreen(),
        routes: [
          GoRoute(
            path: ':id',
            builder: (context, state) {
              final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
              return ChatScreen(conversationId: id);
            },
          ),
        ],
      ),
      GoRoute(
        path: '/health',
        builder: (context, state) => const HealthScreen(),
      ),
      GoRoute(
        path: '/reports',
        builder: (context, state) => const ReportsScreen(),
      ),
      GoRoute(path: '/team', builder: (context, state) => const TeamScreen()),
      GoRoute(path: '/audit', builder: (context, state) => const AuditScreen()),
    ],
    errorBuilder: (context, state) => Scaffold(
      appBar: AppBar(title: const Text('Page Not Found')),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.error_outline_rounded,
              size: 64,
              color: Colors.grey,
            ),
            const SizedBox(height: 16),
            Text('No route defined for ${state.uri.path}'),
            const SizedBox(height: 12),
            ElevatedButton(
              onPressed: () => context.go('/'),
              child: const Text('Return to Cockpit'),
            ),
          ],
        ),
      ),
    ),
  );
});
