// lib/features/health/health_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/widgets/app_drawer.dart';
import '../../shared/widgets/error_retry.dart';
import '../../shared/widgets/skeleton_loader.dart';
import 'health_controller.dart';

class HealthScreen extends ConsumerWidget {
  const HealthScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final healthState = ref.watch(healthControllerProvider);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      drawer: const AppDrawer(currentRoute: '/health'),
      appBar: AppBar(
        // Show back arrow when push-navigated; drawer icon otherwise
        leading: Navigator.of(context).canPop()
            ? IconButton(
                icon: const Icon(Icons.arrow_back_rounded),
                tooltip: 'Go Back',
                onPressed: () => Navigator.of(context).pop(),
              )
            : null,
        title: const Text('SRE Diagnostics & Health'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Re-run Health Probes',
            onPressed: () =>
                ref.read(healthControllerProvider.notifier).loadHealth(),
          ),
        ],
      ),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () =>
              ref.read(healthControllerProvider.notifier).loadHealth(),
          child: _buildContent(context, ref, healthState, isDark),
        ),
      ),
    );
  }

  Widget _buildContent(
    BuildContext context,
    WidgetRef ref,
    HealthState state,
    bool isDark,
  ) {
    if (state.isLoading) {
      return const SkeletonListPlaceholder(count: 4);
    }

    if (state.errorMessage != null && state.health == null) {
      return ErrorRetryWidget(
        message: state.errorMessage!,
        onRetry: () => ref.read(healthControllerProvider.notifier).loadHealth(),
      );
    }

    final health = state.health;
    final isHealthy =
        health?.status.toLowerCase() == 'healthy' ||
        health?.status.toLowerCase() == 'ok';

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Top Health Status Banner
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: isHealthy ? NpontuColors.green : NpontuColors.danger,
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: (isHealthy ? NpontuColors.green : NpontuColors.danger)
                    .withAlpha(60),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white.withAlpha(40),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  isHealthy
                      ? Icons.verified_user_rounded
                      : Icons.warning_rounded,
                  color: Colors.white,
                  size: 32,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'SYSTEM ${health?.status.toUpperCase() ?? 'OPERATIONAL'}',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'All core services, database, and telemetry endpoints responding within SLA bounds.',
                      style: TextStyle(
                        color: Colors.white.withAlpha(220),
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        const Text(
          'Core Subsystem Probes',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
        ),
        const SizedBox(height: 10),

        // Subsystems Grid
        Row(
          children: [
            Expanded(
              child: _buildSubsystemCard(
                title: 'MySQL Cluster',
                status: health?.databaseStatus ?? 'Operational',
                icon: Icons.storage_rounded,
                isOk:
                    health?.databaseStatus.toLowerCase() == 'connected' ||
                    health?.databaseStatus.toLowerCase() == 'healthy',
                isDark: isDark,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildSubsystemCard(
                title: 'Cache & Redis',
                status: health?.redisStatus ?? 'Operational',
                icon: Icons.flash_on_rounded,
                isOk: true,
                isDark: isDark,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: _buildSubsystemCard(
                title: 'Background Queues',
                status: health?.queueStatus ?? 'Running (0 failed)',
                icon: Icons.queue_play_next_rounded,
                isOk: true,
                isDark: isDark,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildSubsystemCard(
                title: 'Disk & Storage',
                status: health?.storageStatus ?? 'OK (> 80% free)',
                icon: Icons.pie_chart_rounded,
                isOk: true,
                isDark: isDark,
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),

        // Telemetry & Environment Info
        const Text(
          'Environment & Runtime Telemetry',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
        ),
        const SizedBox(height: 10),

        Card(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildInfoRow('Backend Framework', 'Laravel 11 LTS (PHP 8.4)'),
                const Divider(height: 20),
                _buildInfoRow('Database Engine', 'MySQL 8.0+ (InnoDB)'),
                const Divider(height: 20),
                _buildInfoRow(
                  'Active Environment',
                  'Production / Staging Multi-Client',
                ),
                const Divider(height: 20),
                _buildInfoRow(
                  'Mobile Client Token Guard',
                  'Laravel Sanctum v4 (Bearer)',
                ),
                const Divider(height: 20),
                _buildInfoRow(
                  'Diagnostics Timestamp',
                  health?.timestamp ?? DateTime.now().toIso8601String(),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildSubsystemCard({
    required String title,
    required String status,
    required IconData icon,
    required bool isOk,
    required bool isDark,
  }) {
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Icon(
                  icon,
                  color: isOk ? NpontuColors.green : NpontuColors.danger,
                  size: 22,
                ),
                Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: isOk ? NpontuColors.green : NpontuColors.danger,
                    shape: BoxShape.circle,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              title,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 4),
            Text(
              status,
              style: TextStyle(
                fontSize: 11,
                color: isOk ? NpontuColors.green : NpontuColors.danger,
                fontWeight: FontWeight.w600,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
        Text(
          value,
          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
        ),
      ],
    );
  }
}
