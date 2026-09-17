// lib/features/dashboard/dashboard_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/services/notification_service.dart';
import '../../core/theme/npontu_theme.dart';
import '../../core/utils/responsive.dart';
import '../../shared/widgets/app_drawer.dart';
import '../../shared/widgets/error_retry.dart';
import '../../shared/widgets/priority_badge.dart';
import '../../shared/widgets/skeleton_loader.dart';
import '../../shared/widgets/status_badge.dart';
import 'dashboard_controller.dart';

class DashboardScreen extends ConsumerWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(dashboardControllerProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Operations Cockpit'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh Cockpit',
            onPressed: () =>
                ref.read(dashboardControllerProvider.notifier).loadDashboard(),
          ),
          Consumer(
            builder: (ctx, watchRef, _) {
              final unread = watchRef.watch(notificationBadgeCountProvider);
              return Stack(
                children: [
                  IconButton(
                    icon: const Icon(Icons.notifications_outlined),
                    tooltip: 'Notifications',
                    onPressed: () => context.push('/notifications'),
                  ),
                  if (unread > 0)
                    Positioned(
                      top: 6,
                      right: 6,
                      child: Container(
                        width: 16,
                        height: 16,
                        decoration: const BoxDecoration(
                          color: NpontuColors.danger,
                          shape: BoxShape.circle,
                        ),
                        child: Center(
                          child: Text(
                            unread > 9 ? '9+' : '$unread',
                            style: const TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w800,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.chat_bubble_outline_rounded),
            tooltip: 'Operational Chat',
            onPressed: () => context.push('/messaging'),
          ),
        ],
      ),
      drawer: const AppDrawer(currentRoute: '/'),
      body: SafeArea(
        child: state.isLoading
            ? const SkeletonListPlaceholder(count: 3)
            : state.errorMessage != null
            ? ErrorRetryWidget(
                message: state.errorMessage!,
                onRetry: () => ref
                    .read(dashboardControllerProvider.notifier)
                    .loadDashboard(),
              )
            : RefreshIndicator(
                color: NpontuColors.green,
                onRefresh: () => ref
                    .read(dashboardControllerProvider.notifier)
                    .loadDashboard(),
                child: LayoutBuilder(
                  builder: (context, constraints) {
                    if (Responsive.isTabletOrDesktop(context)) {
                      return _buildTabletLayout(context, state);
                    }
                    return _buildPhoneLayout(context, state);
                  },
                ),
              ),
      ),
    );
  }

  Widget _buildPhoneLayout(BuildContext context, DashboardDataState state) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 24),
      children: [
        if (state.isOffline) _buildOfflineBanner(),
        _buildShiftBanner(context, state),
        _buildMetricsGrid(context, state),
        if (state.activeIncidentsCount > 0)
          _buildIncidentsSection(context, state),
        _buildPriorityBreakdown(context, state),
        _buildPersonalQueue(context, state),
        if (state.latestHandover != null)
          _buildLatestHandoverCard(context, state),
      ],
    );
  }

  Widget _buildTabletLayout(BuildContext context, DashboardDataState state) {
    return ListView(
      padding: const EdgeInsets.only(bottom: 24),
      children: [
        if (state.isOffline) _buildOfflineBanner(),
        _buildShiftBanner(context, state),
        _buildMetricsGrid(context, state),
        if (state.activeIncidentsCount > 0)
          _buildIncidentsSection(context, state),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(child: _buildPriorityBreakdown(context, state)),
            Expanded(child: _buildPersonalQueue(context, state)),
          ],
        ),
        if (state.latestHandover != null)
          _buildLatestHandoverCard(context, state),
      ],
    );
  }

  Widget _buildOfflineBanner() {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 8, 16, 0),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFFF59E0B).withAlpha(30),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: const Color(0xFFF59E0B).withAlpha(120)),
      ),
      child: const Row(
        children: [
          Icon(Icons.cloud_off_rounded, color: Color(0xFFF59E0B), size: 18),
          SizedBox(width: 8),
          Expanded(
            child: Text(
              'Offline Mode — Viewing cached shift data. Will sync when reconnected.',
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: Color(0xFFB45309),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildShiftBanner(BuildContext context, DashboardDataState state) {
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: NpontuColors.green,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: NpontuColors.green.withAlpha(50),
            blurRadius: 8,
            offset: const Offset(0, 2),
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
            child: const Icon(
              Icons.wb_sunny_rounded,
              color: NpontuColors.gold,
              size: 28,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Flexible(
                      child: Text(
                        '${state.currentShift.toUpperCase()} SHIFT',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                          fontSize: 15,
                          letterSpacing: 0.5,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 6,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: state.systemHealthStatus == 'ok'
                            ? NpontuColors.greenLight
                            : NpontuColors.danger,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        state.systemHealthStatus == 'ok'
                            ? 'HEALTHY'
                            : 'DEGRADED',
                        style: const TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 2),
                Text(
                  'Date: ${state.date} • DB Ping: ${state.dbLatencyMs}ms',
                  style: TextStyle(
                    color: Colors.white.withAlpha(210),
                    fontSize: 12,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: NpontuColors.gold,
              foregroundColor: const Color(0xFF1F2937),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              minimumSize: Size.zero,
            ),
            onPressed: () => context.push('/activities'),
            child: const Text(
              'Checklist',
              style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMetricsGrid(BuildContext context, DashboardDataState state) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          Expanded(
            child: _buildMetricTile(
              context,
              label: 'Done Checks',
              value: '${state.completedChecks}',
              icon: Icons.check_circle_outline_rounded,
              color: NpontuColors.green,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: _buildMetricTile(
              context,
              label: 'Pending Checks',
              value: '${state.pendingChecks}',
              icon: Icons.pending_actions_rounded,
              color: state.pendingChecks > 0
                  ? NpontuColors.goldWarm
                  : NpontuColors.green,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: _buildMetricTile(
              context,
              label: 'Completion',
              value: '${state.completionRate}%',
              icon: Icons.donut_large_rounded,
              color: NpontuColors.greenLight,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMetricTile(
    BuildContext context, {
    required String label,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
      decoration: BoxDecoration(
        color: isDark ? NpontuColors.surfaceCardDark : Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: isDark ? const Color(0xFF1F2E24) : const Color(0xFFE5E7EB),
        ),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 22),
          const SizedBox(height: 6),
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w800,
              color: color,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w500,
              color: isDark
                  ? NpontuColors.textSecondaryDark
                  : NpontuColors.textSecondaryLight,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildIncidentsSection(
    BuildContext context,
    DashboardDataState state,
  ) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: NpontuColors.danger.withAlpha(20),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: NpontuColors.danger.withAlpha(80)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(
                Icons.warning_amber_rounded,
                color: NpontuColors.danger,
                size: 20,
              ),
              const SizedBox(width: 8),
              Text(
                'ACTIVE OPERATIONAL ESCALATIONS (${state.activeIncidentsCount})',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: NpontuColors.danger,
                  letterSpacing: 0.3,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          ...state.activeIncidents.map((inc) {
            return Padding(
              padding: const EdgeInsets.only(top: 4),
              child: Row(
                children: [
                  const Icon(
                    Icons.arrow_right_rounded,
                    color: NpontuColors.danger,
                    size: 20,
                  ),
                  Expanded(
                    child: Text(
                      '${inc.title} [Ticket: ${inc.latestLog?.incidentTicket ?? 'INC-TBD'}]',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildPriorityBreakdown(
    BuildContext context,
    DashboardDataState state,
  ) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'SRE PRIORITY TIERS',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                _buildPriorityChip(
                  'P1',
                  'Critical',
                  state.criticalP1,
                  NpontuColors.criticalP1,
                ),
                const SizedBox(width: 6),
                _buildPriorityChip(
                  'P2',
                  'High',
                  state.highP2,
                  NpontuColors.highP2,
                ),
                const SizedBox(width: 6),
                _buildPriorityChip(
                  'P3',
                  'Medium',
                  state.mediumP3,
                  NpontuColors.mediumP3,
                ),
                const SizedBox(width: 6),
                _buildPriorityChip(
                  'P4',
                  'Low',
                  state.lowP4,
                  NpontuColors.lowP4,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPriorityChip(String tier, String label, int count, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 2),
        decoration: BoxDecoration(
          color: color.withAlpha(20),
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: color.withAlpha(60)),
        ),
        child: Column(
          children: [
            Text(
              '$count',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w800,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              '$tier $label',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 9,
                fontWeight: FontWeight.w700,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPersonalQueue(BuildContext context, DashboardDataState state) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'ASSIGNED TO YOU',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.5,
                  ),
                ),
                Text(
                  '${state.completedAssignedToMe}/${state.totalAssignedToMe} Done',
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: NpontuColors.green,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            if (state.personalTasks.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 8),
                child: Text(
                  'No operational checks assigned directly to your queue.',
                  style: TextStyle(fontSize: 13),
                ),
              )
            else
              ...state.personalTasks.map((task) {
                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 6),
                  child: Row(
                    children: [
                      PriorityBadge(priority: task.priority, compact: true),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          task.title,
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 8),
                      StatusBadge(
                        status: task.currentStatus,
                        fontSize: 10,
                        compact: true,
                      ),
                    ],
                  ),
                );
              }),
          ],
        ),
      ),
    );
  }

  Widget _buildLatestHandoverCard(
    BuildContext context,
    DashboardDataState state,
  ) {
    final handover = state.latestHandover!;
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'LATEST SHIFT HANDOVER',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.5,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 2,
                  ),
                  decoration: BoxDecoration(
                    color: handover.isAccepted
                        ? NpontuColors.green
                        : NpontuColors.gold,
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    handover.isAccepted ? 'ACCEPTED' : 'PENDING SIGN-ON',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: handover.isAccepted
                          ? Colors.white
                          : const Color(0xFF1F2937),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              '${handover.shiftLabel} • Signed by: ${handover.outgoingLead?.name ?? 'Lead'}',
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 4),
            Text(
              handover.summary,
              style: const TextStyle(fontSize: 12),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
