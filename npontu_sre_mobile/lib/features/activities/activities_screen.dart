// lib/features/activities/activities_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/models/activity_model.dart';
import '../../shared/widgets/app_drawer.dart';
import '../../shared/widgets/empty_state.dart';
import '../../shared/widgets/error_retry.dart';
import '../../shared/widgets/priority_badge.dart';
import '../../shared/widgets/skeleton_loader.dart';
import '../../shared/widgets/status_badge.dart';
import '../auth/presentation/auth_controller.dart';
import 'activities_controller.dart';

class ActivitiesScreen extends ConsumerStatefulWidget {
  const ActivitiesScreen({super.key});

  @override
  ConsumerState<ActivitiesScreen> createState() => _ActivitiesScreenState();
}

class _ActivitiesScreenState extends ConsumerState<ActivitiesScreen> {
  final _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _openCheckoffModal(ActivityModel activity) {
    final statusNotifier = ref.read(activitiesControllerProvider.notifier);
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

                    // Status Selection Radio
                    const Text(
                      'TRANSITION STATUS',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 8),
                    SegmentedButton<String>(
                      segments: const [
                        ButtonSegment(
                          value: 'done',
                          label: Text('Mark as Done'),
                          icon: Icon(Icons.check_circle_rounded),
                        ),
                        ButtonSegment(
                          value: 'pending',
                          label: Text('Keep Pending'),
                          icon: Icon(Icons.pending_actions_rounded),
                        ),
                      ],
                      selected: {selectedStatus},
                      onSelectionChanged: (val) {
                        setModalState(() => selectedStatus = val.first);
                      },
                    ),
                    const SizedBox(height: 16),

                    // Remark input
                    TextField(
                      controller: remarkController,
                      maxLines: 2,
                      decoration: const InputDecoration(
                        labelText: 'Operational Remark (Optional)',
                        hintText: 'e.g. Logs match monitoring dashboard',
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Incident Escalation Toggle
                    CheckboxListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text(
                        'Flag Incident Escalation',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      subtitle: const Text(
                        'Attach SRE incident tracking ticket reference',
                        style: TextStyle(fontSize: 12),
                      ),
                      value: isEscalated,
                      activeColor: NpontuColors.danger,
                      onChanged: (val) {
                        setModalState(() => isEscalated = val ?? false);
                      },
                    ),

                    if (isEscalated) ...[
                      const SizedBox(height: 8),
                      TextField(
                        controller: ticketController,
                        decoration: const InputDecoration(
                          labelText: 'Incident Tracking Ticket ID',
                          hintText: 'e.g. INC-1042',
                          prefixIcon: Icon(
                            Icons.confirmation_number_outlined,
                            color: NpontuColors.danger,
                          ),
                        ),
                      ),
                    ],

                    const SizedBox(height: 20),
                    ElevatedButton(
                      onPressed: () async {
                        Navigator.pop(ctx);
                        await statusNotifier.updateActivityStatus(
                          activityId: activity.id,
                          status: selectedStatus,
                          remark: remarkController.text.trim().isEmpty
                              ? null
                              : remarkController.text.trim(),
                          incidentTicket:
                              isEscalated &&
                                  ticketController.text.trim().isNotEmpty
                              ? ticketController.text.trim()
                              : null,
                          isEscalated: isEscalated,
                        );
                      },
                      child: const Text('Confirm Status Checkoff'),
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

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(activitiesControllerProvider);
    final user = ref.watch(authControllerProvider).user;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Shift Checklist'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: () => ref
                .read(activitiesControllerProvider.notifier)
                .loadActivities(),
          ),
        ],
      ),
      drawer: const AppDrawer(currentRoute: '/activities'),
      floatingActionButton: user?.canManageActivities == true
          ? FloatingActionButton.extended(
              backgroundColor: NpontuColors.green,
              foregroundColor: Colors.white,
              icon: const Icon(Icons.add_rounded),
              label: const Text('New Check'),
              onPressed: () => context.push('/activities/new'),
            )
          : null,
      body: Column(
        children: [
          // Filter Tabs (All, Pending, Done, Mine)
          Container(
            color: isDark ? NpontuColors.surfaceMid : Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Column(
              children: [
                // Quick Status Segment Bar
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('All Checks (${state.total})', null),
                      const SizedBox(width: 8),
                      _buildFilterChip(
                        'Pending (${state.pendingCount})',
                        'pending',
                      ),
                      const SizedBox(width: 8),
                      _buildFilterChip('Done (${state.doneCount})', 'done'),
                      const SizedBox(width: 8),
                      _buildAssignedChip('Assigned to Me', 'me'),
                    ],
                  ),
                ),
                const SizedBox(height: 8),

                // Search Bar
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search checks by title or category...',
                    prefixIcon: const Icon(Icons.search_rounded, size: 20),
                    contentPadding: const EdgeInsets.symmetric(vertical: 8),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () {
                              _searchController.clear();
                              ref
                                  .read(activitiesControllerProvider.notifier)
                                  .setSearchFilter(null);
                            },
                          )
                        : null,
                  ),
                  onChanged: (val) {
                    ref
                        .read(activitiesControllerProvider.notifier)
                        .setSearchFilter(val);
                  },
                ),
              ],
            ),
          ),

          // Checklist Content
          Expanded(
            child: state.isLoading
                ? const SkeletonListPlaceholder(count: 5)
                : state.errorMessage != null
                ? ErrorRetryWidget(
                    message: state.errorMessage!,
                    onRetry: () => ref
                        .read(activitiesControllerProvider.notifier)
                        .loadActivities(),
                  )
                : state.activities.isEmpty
                ? const EmptyStateWidget(
                    icon: Icons.done_all_rounded,
                    title: 'No Matching Operational Checks',
                    subtitle: 'All checks for this filter criteria have been signed or are not scheduled.',
                  )
                : RefreshIndicator(
                    color: NpontuColors.green,
                    onRefresh: () => ref
                        .read(activitiesControllerProvider.notifier)
                        .loadActivities(),
                    child: ListView.builder(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      itemCount: state.activities.length,
                      itemBuilder: (context, index) {
                        final activity = state.activities[index];
                        return _buildActivityCard(context, activity);
                      },
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String? statusVal) {
    final state = ref.watch(activitiesControllerProvider);
    final isSelected =
        state.filter.status == statusVal && state.filter.assigned == null;

    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        if (selected) {
          ref
              .read(activitiesControllerProvider.notifier)
              .setAssignedFilter(null);
          ref
              .read(activitiesControllerProvider.notifier)
              .setStatusFilter(statusVal);
        }
      },
    );
  }

  Widget _buildAssignedChip(String label, String assignedVal) {
    final state = ref.watch(activitiesControllerProvider);
    final isSelected = state.filter.assigned == assignedVal;

    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        ref
            .read(activitiesControllerProvider.notifier)
            .setAssignedFilter(selected ? assignedVal : null);
      },
    );
  }

  Widget _buildActivityCard(BuildContext context, ActivityModel activity) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(10),
        onTap: () => _openCheckoffModal(activity),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top Row: Pinned Star + Priority + SLA Time + Status Badge
              Row(
                children: [
                  if (activity.isPinned) ...[
                    const Icon(
                      Icons.star_rounded,
                      color: NpontuColors.gold,
                      size: 20,
                    ),
                    const SizedBox(width: 6),
                  ],
                  PriorityBadge(priority: activity.priority),
                  if (activity.slaTime != null) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 6,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: isDark
                            ? NpontuColors.surfaceMid
                            : const Color(0xFFF3F4F6),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.alarm_rounded, size: 12),
                          const SizedBox(width: 3),
                          Text(
                            activity.slaTime!,
                            style: const TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                  const Spacer(),
                  StatusBadge(status: activity.currentStatus),
                ],
              ),
              const SizedBox(height: 10),

              // Title
              Text(
                activity.title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                ),
              ),

              // Description (if present)
              if (activity.description != null &&
                  activity.description!.isNotEmpty) ...[
                const SizedBox(height: 4),
                Text(
                  activity.description!,
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark
                        ? NpontuColors.textSecondaryDark
                        : NpontuColors.textSecondaryLight,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
              const SizedBox(height: 12),

              // Bottom Row: Assignee + Checkoff Action
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.account_circle_outlined,
                        size: 16,
                        color: isDark
                            ? NpontuColors.textSecondaryDark
                            : NpontuColors.textSecondaryLight,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        activity.assignee?.name ?? 'Unassigned Pool',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: isDark
                              ? NpontuColors.textSecondaryDark
                              : NpontuColors.textSecondaryLight,
                        ),
                      ),
                    ],
                  ),
                  TextButton.icon(
                    onPressed: () => _openCheckoffModal(activity),
                    icon: Icon(
                      activity.isDone
                          ? Icons.edit_note_rounded
                          : Icons.check_circle_outline_rounded,
                      size: 16,
                      color: NpontuColors.green,
                    ),
                    label: Text(
                      activity.isDone ? 'Edit Remark' : 'Check Off',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: NpontuColors.green,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
