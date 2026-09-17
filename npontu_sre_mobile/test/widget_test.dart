// test/widget_test.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:npontu_sre_mobile/features/auth/presentation/login_screen.dart';
import 'package:npontu_sre_mobile/shared/widgets/empty_state.dart';
import 'package:npontu_sre_mobile/shared/widgets/error_retry.dart';
import 'package:npontu_sre_mobile/shared/widgets/priority_badge.dart';
import 'package:npontu_sre_mobile/shared/widgets/status_badge.dart';

void main() {
  group('StatusBadge Widget Tests', () {
    testWidgets('renders DONE badge correctly', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(body: StatusBadge(status: 'done')),
        ),
      );

      expect(find.text('DONE'), findsOneWidget);
    });

    testWidgets('renders PENDING badge correctly', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(body: StatusBadge(status: 'pending')),
        ),
      );

      expect(find.text('PENDING'), findsOneWidget);
    });

    testWidgets('renders ACKNOWLEDGED badge correctly', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(body: StatusBadge(status: 'acknowledged')),
        ),
      );

      expect(find.text('ACKNOWLEDGED'), findsOneWidget);
    });
  });

  group('PriorityBadge Widget Tests', () {
    testWidgets('renders CRITICAL priority badge', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(body: PriorityBadge(priority: 'critical')),
        ),
      );

      expect(find.text('P1 CRITICAL'), findsOneWidget);
    });

    testWidgets('renders HIGH priority badge', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(body: PriorityBadge(priority: 'high')),
        ),
      );

      expect(find.text('P2 HIGH'), findsOneWidget);
    });

    testWidgets('renders MEDIUM priority badge', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(body: PriorityBadge(priority: 'medium')),
        ),
      );

      expect(find.text('P3 MEDIUM'), findsOneWidget);
    });
  });

  group('EmptyStateWidget Tests', () {
    testWidgets('renders title and subtitle', (tester) async {
      await tester.pumpWidget(
        const MaterialApp(
          home: Scaffold(
            body: EmptyStateWidget(
              icon: Icons.inbox_rounded,
              title: 'No Items Found',
              subtitle: 'Operational queue is empty.',
            ),
          ),
        ),
      );

      expect(find.text('No Items Found'), findsOneWidget);
      expect(find.text('Operational queue is empty.'), findsOneWidget);
      expect(find.byIcon(Icons.inbox_rounded), findsOneWidget);
    });
  });

  group('ErrorRetryWidget Tests', () {
    testWidgets('renders error message and triggers callback', (tester) async {
      bool retried = false;

      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: ErrorRetryWidget(
              message: 'Network unreachable',
              onRetry: () => retried = true,
            ),
          ),
        ),
      );

      expect(find.text('Network unreachable'), findsOneWidget);
      expect(find.text('Retry Request'), findsOneWidget);

      await tester.tap(find.text('Retry Request'));
      expect(retried, isTrue);
    });
  });

  group('LoginScreen Widget Tests', () {
    testWidgets('renders login form and inputs', (tester) async {
      await tester.pumpWidget(
        const ProviderScope(child: MaterialApp(home: LoginScreen())),
      );
      await tester.pump();

      expect(find.text('Opsora SRE'), findsOneWidget);
      expect(
        find.text('Site Reliability Engineering Mobile Cockpit'),
        findsOneWidget,
      );
      expect(find.byType(TextFormField), findsNWidgets(2));
      expect(find.text('Authenticate & Enter Cockpit'), findsOneWidget);
    });
  });
}
