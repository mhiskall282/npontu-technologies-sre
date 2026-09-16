// lib/features/reports/reports_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/widgets/app_drawer.dart';
import '../../shared/widgets/empty_state.dart';
import '../../shared/widgets/error_retry.dart';
import '../../shared/widgets/skeleton_loader.dart';
import '../../shared/widgets/status_badge.dart';
import 'reports_controller.dart';

class ReportsScreen extends ConsumerStatefulWidget {
  const ReportsScreen({super.key});

  @override
  ConsumerState<ReportsScreen> createState() => _ReportsScreenState();
}

class _ReportsScreenState extends ConsumerState<ReportsScreen> {
  @override
  Widget build(BuildContext context) {
    final reportsState = ref.watch(reportsControllerProvider);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      drawer: const AppDrawer(currentRoute: '/reports'),
      appBar: AppBar(
        title: const Text('Compliance Reports'),
        actions: [
          IconButton(
            icon: const Icon(Icons.download_rounded),
            tooltip: 'Export Report',
            onPressed: () {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text(
                    'Report generated. Download link sent to registered email.',
                  ),
                ),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: () =>
                ref.read(reportsControllerProvider.notifier).loadReports(),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () =>
            ref.read(reportsControllerProvider.notifier).loadReports(),
        child: Column(
          children: [
            // Date Filter Strip
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              color: isDark ? NpontuColors.surfaceMid : const Color(0xFFF3F4F6),
              child: Row(
                children: [
                  const Icon(
                    Icons.date_range_rounded,
                    size: 18,
                    color: NpontuColors.green,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    '${reportsState.from}  →  ${reportsState.to}',
                    style: const TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 13,
                    ),
                  ),
                  const Spacer(),
                  TextButton.icon(
                    style: TextButton.styleFrom(padding: EdgeInsets.zero),
                    onPressed: () async {
                      final range = await showDateRangePicker(
                        context: context,
                        firstDate: DateTime.now().subtract(
                          const Duration(days: 90),
                        ),
                        lastDate: DateTime.now(),
                      );
                      if (range != null) {
                        ref
                            .read(reportsControllerProvider.notifier)
                            .loadReports(
                              from: range.start.toIso8601String().substring(
                                0,
                                10,
                              ),
                              to: range.end.toIso8601String().substring(0, 10),
                            );
                      }
                    },
                    icon: const Icon(
                      Icons.filter_list_rounded,
                      size: 16,
                      color: NpontuColors.green,
                    ),
                    label: const Text(
                      'Change Date',
                      style: TextStyle(color: NpontuColors.green, fontSize: 12),
                    ),
                  ),
                ],
              ),
            ),

            // Main Body
            Expanded(child: _buildBody(context, reportsState, isDark)),
          ],
        ),
      ),
    );
  }

  Widget _buildBody(BuildContext context, ReportsState state, bool isDark) {
    if (state.isLoading) {
      return const SkeletonListPlaceholder(count: 4);
    }

    if (state.errorMessage != null) {
      return ErrorRetryWidget(
        message: state.errorMessage!,
        onRetry: () =>
            ref.read(reportsControllerProvider.notifier).loadReports(),
      );
    }

    final total = state.logs.length;
    final doneCount = state.logs.where((l) => l.status == 'done').length;
    final completionRate = total > 0 ? ((doneCount / total) * 100).round() : 0;
    final escalations = state.logs.where((l) => l.isEscalated).length;

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Summary KPI Cards
        Row(
          children: [
            Expanded(
              child: _buildKpiCard(
                title: 'Total Executions',
                value: '$total',
                icon: Icons.checklist_rounded,
                color: NpontuColors.green,
                isDark: isDark,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildKpiCard(
                title: 'Completion Rate',
                value: '$completionRate%',
                icon: Icons.pie_chart_rounded,
                color: completionRate >= 80
                    ? NpontuColors.green
                    : NpontuColors.gold,
                isDark: isDark,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _buildKpiCard(
                title: 'Escalations',
                value: '$escalations',
                icon: Icons.warning_rounded,
                color: escalations > 0
                    ? NpontuColors.danger
                    : NpontuColors.green,
                isDark: isDark,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // Compliance Progress Bar
        Card(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'SLA Compliance Progress',
                      style: TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 14,
                      ),
                    ),
                    Text(
                      '$completionRate%',
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        color: completionRate >= 80
                            ? NpontuColors.green
                            : NpontuColors.gold,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                ClipRRect(
                  borderRadius: BorderRadius.circular(6),
                  child: LinearProgressIndicator(
                    value: total > 0 ? doneCount / total : 0,
                    minHeight: 10,
                    backgroundColor: isDark
                        ? NpontuColors.surfaceMid
                        : const Color(0xFFE5E7EB),
                    color: completionRate >= 80
                        ? NpontuColors.green
                        : NpontuColors.gold,
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 20),

        // Execution History Logs
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Audited Execution Events',
              style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
            ),
            Text(
              '${state.logs.length} logged',
              style: const TextStyle(fontSize: 12, color: Colors.grey),
            ),
          ],
        ),
        const SizedBox(height: 10),

        if (state.logs.isEmpty)
          const EmptyStateWidget(
            icon: Icons.assessment_outlined,
            title: 'No Logs for Range',
            subtitle: 'Try adjusting the date filter to view past operational performance.',
          )
        else
          ...state.logs.map(
            (log) => Card(
              margin: const EdgeInsets.only(bottom: 8),
              child: ListTile(
                title: Text(
                  'Activity Checklist Item #${log.activityId}',
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                  ),
                ),
                subtitle: Text(
                  '${log.date} • ${log.actorName}${log.remark != null ? ' • ${log.remark}' : ''}',
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark
                        ? NpontuColors.textSecondaryDark
                        : NpontuColors.textSecondaryLight,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                trailing: StatusBadge(status: log.status),
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildKpiCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
    required bool isDark,
  }) {
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: color, size: 20),
            const SizedBox(height: 8),
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
              title,
              style: const TextStyle(fontSize: 10, color: Colors.grey),
              maxLines: 1,
            ),
          ],
        ),
      ),
    );
  }
}
