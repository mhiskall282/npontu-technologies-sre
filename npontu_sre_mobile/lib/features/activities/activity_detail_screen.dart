// lib/features/activities/activity_detail_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/models/activity_model.dart';
import '../../shared/widgets/priority_badge.dart';
import '../../shared/widgets/status_badge.dart';
import '../auth/presentation/auth_controller.dart';
import 'activities_controller.dart';

class ActivityDetailScreen extends ConsumerStatefulWidget {
  final int activityId;

  const ActivityDetailScreen({super.key, required this.activityId});

  @override
  ConsumerState<ActivityDetailScreen> createState() =>
      _ActivityDetailScreenState();
}

class _ActivityDetailScreenState extends ConsumerState<ActivityDetailScreen> {
  bool _isFetching = false;
  bool _fetchAttempted = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _checkAndFetch();
    });
  }

  Future<void> _checkAndFetch() async {
    final activitiesState = ref.read(activitiesControllerProvider);
    final exists = activitiesState.activities.any(
      (a) => a.id == widget.activityId,
    );
    if (!exists && !_fetchAttempted) {
      setState(() {
        _isFetching = true;
        _fetchAttempted = true;
      });
      await ref
          .read(activitiesControllerProvider.notifier)
          .fetchActivity(widget.activityId);
      if (mounted) {
        setState(() => _isFetching = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final activitiesState = ref.watch(activitiesControllerProvider);
    final user = ref.watch(authControllerProvider).user;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    if (_isFetching) {
      return Scaffold(
        appBar: AppBar(title: const Text('Activity Details')),
        body: const Center(
          child: CircularProgressIndicator(color: NpontuColors.green),
        ),
      );
    }

    final activity = activitiesState.activities
        .cast<ActivityModel?>()
        .firstWhere((a) => a?.id == widget.activityId, orElse: () => null);

    if (activity == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Activity Details')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.search_off_rounded,
                size: 64,
                color: Colors.grey,
              ),
              const SizedBox(height: 16),
              const Text('Activity not found in current shift view.'),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: () => context.pop(),
                child: const Text('Go Back'),
              ),
            ],
          ),
        ),
      );
    }

    final canManage = user?.canManageChecklists ?? false;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          activity.title,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        actions: [
          if (canManage) ...[
            IconButton(
              icon: const Icon(Icons.edit_rounded),
              tooltip: 'Edit Activity',
              onPressed: () => context.push('/activities/${activity.id}/edit'),
            ),
            IconButton(
              icon: const Icon(
                Icons.delete_outline_rounded,
                color: NpontuColors.danger,
              ),
              tooltip: 'Delete Activity',
              onPressed: () async {
                final confirm = await showDialog<bool>(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    title: const Text('Delete Activity'),
                    content: Text(
                      'Are you sure you want to delete "${activity.title}"? This cannot be undone.',
                    ),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.pop(ctx, false),
                        child: const Text('Cancel'),
                      ),
                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: NpontuColors.danger,
                        ),
                        onPressed: () => Navigator.pop(ctx, true),
                        child: const Text('Delete'),
                      ),
                    ],
                  ),
                );

                if (confirm == true && context.mounted) {
                  final success = await ref
                      .read(activitiesControllerProvider.notifier)
                      .deleteActivity(activity.id);
                  if (success && context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Activity deleted successfully.'),
                      ),
                    );
                    context.pop();
                  }
                }
              },
            ),
          ],
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () =>
            ref.read(activitiesControllerProvider.notifier).loadActivities(),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Status & Priority Card
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
                        Row(
                          children: [
                            if (activity.isPinned) ...[
                              const Icon(
                                Icons.star_rounded,
                                color: NpontuColors.gold,
                                size: 22,
                              ),
                              const SizedBox(width: 6),
                            ],
                            PriorityBadge(priority: activity.priority),
                          ],
                        ),
                        StatusBadge(status: activity.currentStatus),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text(
                      activity.title,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    if (activity.description != null &&
                        activity.description!.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Text(
                        activity.description!,
                        style: TextStyle(
                          fontSize: 14,
                          color: isDark
                              ? NpontuColors.textSecondaryDark
                              : NpontuColors.textSecondaryLight,
                        ),
                      ),
                    ],
                    const Divider(height: 24),
                    // Metadata grid
                    Row(
                      children: [
                        Expanded(
                          child: _buildMetaItem(
                            icon: Icons.schedule_rounded,
                            label: 'Shift',
                            value: activity.shift.toUpperCase(),
                          ),
                        ),
                        Expanded(
                          child: _buildMetaItem(
                            icon: Icons.alarm_rounded,
                            label: 'SLA Window',
                            value: activity.slaTime ?? 'Anytime',
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: _buildMetaItem(
                            icon: Icons.account_circle_outlined,
                            label: 'Assigned Operator',
                            value: activity.assignee?.name ?? 'Unassigned Pool',
                          ),
                        ),
                        Expanded(
                          child: _buildMetaItem(
                            icon: Icons.repeat_rounded,
                            label: 'Cadence',
                            value: activity.recurrence.toUpperCase(),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Check-off Action Button
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: activity.isDone
                    ? NpontuColors.surfaceMid
                    : NpontuColors.green,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              onPressed: () => _openCheckoffModal(context, activity),
              icon: Icon(
                activity.isDone
                    ? Icons.edit_note_rounded
                    : Icons.check_circle_rounded,
              ),
              label: Text(
                activity.isDone
                    ? 'Update Execution Remark'
                    : 'Sign-Off & Complete Activity',
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Timeline & Event Logs Section
            Row(
              children: [
                const Icon(
                  Icons.history_rounded,
                  size: 20,
                  color: NpontuColors.green,
                ),
                const SizedBox(width: 8),
                const Text(
                  'Execution Audit Trail',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                ),
                const Spacer(),
                Text(
                  '${activity.logs.length} Events',
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark
                        ? NpontuColors.textSecondaryDark
                        : NpontuColors.textSecondaryLight,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            if (activity.logs.isEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Center(
                    child: Text(
                      'No execution logs recorded yet for this shift.',
                      style: TextStyle(
                        color: isDark
                            ? NpontuColors.textSecondaryDark
                            : NpontuColors.textSecondaryLight,
                      ),
                    ),
                  ),
                ),
              )
            else
              ...activity.logs.map((log) => _buildLogCard(log, isDark)),
          ],
        ),
      ),
    );
  }

  Widget _buildMetaItem({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 18, color: NpontuColors.green),
        const SizedBox(width: 8),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: const TextStyle(fontSize: 11, color: Colors.grey),
            ),
            const SizedBox(height: 2),
            Text(
              value,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildLogCard(ActivityLogModel log, bool isDark) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                StatusBadge(status: log.status),
                Text(
                  log.loggedDate,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w500,
                    color: Colors.grey,
                  ),
                ),
              ],
            ),
            if (log.remark != null && log.remark!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(log.remark!, style: const TextStyle(fontSize: 13)),
            ],
            if (log.isEscalated) ...[
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: NpontuColors.danger.withAlpha(25),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.warning_amber_rounded,
                      size: 14,
                      color: NpontuColors.danger,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Escalated: ${log.incidentTicket ?? 'Incident'}',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: NpontuColors.danger,
                      ),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 8),
            Row(
              children: [
                Icon(
                  Icons.person_pin_circle_outlined,
                  size: 14,
                  color: isDark
                      ? NpontuColors.textSecondaryDark
                      : NpontuColors.textSecondaryLight,
                ),
                const SizedBox(width: 4),
                Text(
                  'Operator: ${log.actor?.name ?? 'System'}',
                  style: TextStyle(
                    fontSize: 11,
                    color: isDark
                        ? NpontuColors.textSecondaryDark
                        : NpontuColors.textSecondaryLight,
                  ),
                ),
                const Spacer(),
                Text(
                  log.createdAt != null
                      ? '${log.createdAt!.hour.toString().padLeft(2, '0')}:${log.createdAt!.minute.toString().padLeft(2, '0')}'
                      : '',
                  style: const TextStyle(fontSize: 11, color: Colors.grey),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _openCheckoffModal(BuildContext context, ActivityModel activity) {
    String selectedStatus = activity.isDone ? 'pending' : 'done';
    final remarkController = TextEditingController(
      text: activity.latestLog?.remark ?? '',
    );
    final ticketController = TextEditingController(
      text: activity.latestLog?.incidentTicket ?? '',
    );
    bool isEscalated = activity.latestLog?.isEscalated ?? false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
                left: 20,
                right: 20,
                top: 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            activity.title,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                            ),
                            maxLines: 2,
                          ),
                        ),
                        PriorityBadge(priority: activity.priority),
                      ],
                    ),
                    const SizedBox(height: 16),
                    DropdownButtonFormField<String>(
                      value: selectedStatus,
                      decoration: const InputDecoration(
                        labelText: 'Target Status',
                      ),
                      items: const [
                        DropdownMenuItem(
                          value: 'done',
                          child: Text('COMPLETED / VERIFIED'),
                        ),
                        DropdownMenuItem(
                          value: 'pending',
                          child: Text('PENDING / NOT STARTED'),
                        ),
                        DropdownMenuItem(
                          value: 'in_progress',
                          child: Text('IN PROGRESS'),
                        ),
                        DropdownMenuItem(
                          value: 'skipped',
                          child: Text('SKIPPED / DEFERRED'),
                        ),
                      ],
                      onChanged: (val) {
                        if (val != null)
                          setModalState(() => selectedStatus = val);
                      },
                    ),
                    const SizedBox(height: 14),
                    TextField(
                      controller: remarkController,
                      decoration: const InputDecoration(
                        labelText: 'Execution Remark / Verification Output',
                        hintText:
                            'e.g., Checked all clusters, healthy 0 errors.',
                      ),
                      maxLines: 3,
                    ),
                    const SizedBox(height: 14),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text(
                        'Flag Incident Escalation',
                        style: TextStyle(fontSize: 14),
                      ),
                      value: isEscalated,
                      activeColor: NpontuColors.danger,
                      onChanged: (val) =>
                          setModalState(() => isEscalated = val),
                    ),
                    if (isEscalated) ...[
                      const SizedBox(height: 8),
                      TextField(
                        controller: ticketController,
                        decoration: const InputDecoration(
                          labelText: 'Incident Ticket #',
                          hintText: 'INC-88912',
                        ),
                      ),
                    ],
                    const SizedBox(height: 20),
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: NpontuColors.green,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                      onPressed: () async {
                        Navigator.pop(ctx);
                        final success = await ref
                            .read(activitiesControllerProvider.notifier)
                            .updateStatus(
                              activityId: activity.id,
                              status: selectedStatus,
                              remark: remarkController.text.trim(),
                              incidentTicket: isEscalated
                                  ? ticketController.text.trim()
                                  : null,
                              isEscalated: isEscalated,
                            );

                        if (context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(
                                success
                                    ? 'Activity verified & logged to audit trail.'
                                    : 'Failed to update activity status.',
                              ),
                              backgroundColor: success
                                  ? NpontuColors.green
                                  : NpontuColors.danger,
                            ),
                          );
                        }
                      },
                      child: const Text(
                        'Save Event & Audit Entry',
                        style: TextStyle(fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }
}
