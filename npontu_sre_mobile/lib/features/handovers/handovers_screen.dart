// lib/features/handovers/handovers_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/models/shift_handover_model.dart';
import '../../shared/widgets/app_drawer.dart';
import '../../shared/widgets/empty_state.dart';
import '../../shared/widgets/error_retry.dart';
import '../../shared/widgets/skeleton_loader.dart';
import '../../shared/widgets/status_badge.dart';
import '../auth/presentation/auth_controller.dart';
import 'handovers_controller.dart';

class HandoversScreen extends ConsumerStatefulWidget {
  const HandoversScreen({super.key});

  @override
  ConsumerState<HandoversScreen> createState() => _HandoversScreenState();
}

class _HandoversScreenState extends ConsumerState<HandoversScreen> {
  String _selectedShift = '';
  String _selectedStatus = '';

  @override
  Widget build(BuildContext context) {
    final handoversState = ref.watch(handoversControllerProvider);
    final user = ref.watch(authControllerProvider).user;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      drawer: const AppDrawer(currentRoute: '/handovers'),
      appBar: AppBar(
        title: const Text('Shift Handovers'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: () => ref
                .read(handoversControllerProvider.notifier)
                .loadHandovers(
                  shift: _selectedShift.isEmpty ? null : _selectedShift,
                  status: _selectedStatus.isEmpty ? null : _selectedStatus,
                ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: NpontuColors.green,
        foregroundColor: Colors.white,
        onPressed: () => context.push('/handovers/new'),
        icon: const Icon(Icons.swap_horiz_rounded),
        label: const Text(
          'Initiate Handover',
          style: TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () => ref
            .read(handoversControllerProvider.notifier)
            .loadHandovers(
              shift: _selectedShift.isEmpty ? null : _selectedShift,
              status: _selectedStatus.isEmpty ? null : _selectedStatus,
            ),
        child: Column(
          children: [
            // Filter Strip
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              color: isDark ? NpontuColors.surfaceMid : const Color(0xFFF3F4F6),
              child: Row(
                children: [
                  Expanded(
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        value: _selectedShift,
                        isExpanded: true,
                        items: const [
                          DropdownMenuItem(
                            value: '',
                            child: Text('All Shifts'),
                          ),
                          DropdownMenuItem(
                            value: 'morning',
                            child: Text('Morning'),
                          ),
                          DropdownMenuItem(
                            value: 'afternoon',
                            child: Text('Afternoon'),
                          ),
                          DropdownMenuItem(
                            value: 'night',
                            child: Text('Night'),
                          ),
                        ],
                        onChanged: (val) {
                          setState(() => _selectedShift = val ?? '');
                          ref
                              .read(handoversControllerProvider.notifier)
                              .loadHandovers(
                                shift: _selectedShift.isEmpty
                                    ? null
                                    : _selectedShift,
                                status: _selectedStatus.isEmpty
                                    ? null
                                    : _selectedStatus,
                              );
                        },
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        value: _selectedStatus,
                        isExpanded: true,
                        items: const [
                          DropdownMenuItem(
                            value: '',
                            child: Text('All Statuses'),
                          ),
                          DropdownMenuItem(
                            value: 'initiated',
                            child: Text('Initiated (Pending)'),
                          ),
                          DropdownMenuItem(
                            value: 'acknowledged',
                            child: Text('Acknowledged'),
                          ),
                          DropdownMenuItem(
                            value: 'rejected',
                            child: Text('Rejected'),
                          ),
                        ],
                        onChanged: (val) {
                          setState(() => _selectedStatus = val ?? '');
                          ref
                              .read(handoversControllerProvider.notifier)
                              .loadHandovers(
                                shift: _selectedShift.isEmpty
                                    ? null
                                    : _selectedShift,
                                status: _selectedStatus.isEmpty
                                    ? null
                                    : _selectedStatus,
                              );
                        },
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // Content Area
            Expanded(
              child: _buildBody(context, handoversState, user?.id, isDark),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBody(
    BuildContext context,
    HandoversState state,
    int? currentUserId,
    bool isDark,
  ) {
    if (state.isLoading) {
      return const SkeletonListPlaceholder(count: 4);
    }

    if (state.errorMessage != null) {
      return ErrorRetryWidget(
        message: state.errorMessage!,
        onRetry: () => ref
            .read(handoversControllerProvider.notifier)
            .loadHandovers(
              shift: _selectedShift.isEmpty ? null : _selectedShift,
              status: _selectedStatus.isEmpty ? null : _selectedStatus,
            ),
      );
    }

    if (state.handovers.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.assignment_turned_in_rounded,
        title: 'No Shift Handovers Found',
        subtitle: 'Initiate a new handover briefing when signing off your active shift.',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 80),
      itemCount: state.handovers.length,
      itemBuilder: (ctx, index) {
        final item = state.handovers[index];
        return _buildHandoverCard(context, item, currentUserId, isDark);
      },
    );
  }

  Widget _buildHandoverCard(
    BuildContext context,
    ShiftHandoverModel item,
    int? currentUserId,
    bool isDark,
  ) {
    final canAccept =
        !item.isAcknowledged &&
        (item.incomingUserId == null || item.incomingUserId == currentUserId);

    return Card(
      margin: const EdgeInsets.only(bottom: 14),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Row: Shift Badge + Date + Status
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: NpontuColors.green.withAlpha(25),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    '${item.shift.toUpperCase()} SHIFT',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: NpontuColors.green,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  item.handoverDate,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Colors.grey,
                  ),
                ),
                const Spacer(),
                StatusBadge(status: item.status),
              ],
            ),
            const SizedBox(height: 12),

            // Operator Handoff Chain: Outgoing -> Incoming
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Outgoing Sign-Off',
                        style: TextStyle(fontSize: 11, color: Colors.grey),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        item.outgoingUser?.name ??
                            'Operator #${item.outgoingUserId}',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const Icon(
                  Icons.arrow_forward_rounded,
                  size: 18,
                  color: NpontuColors.gold,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      const Text(
                        'Incoming Operator',
                        style: TextStyle(fontSize: 11, color: Colors.grey),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        item.incomingUser?.name ?? 'Awaiting Sign-Off',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
              ],
            ),

            if (item.summary.isNotEmpty) ...[
              const Divider(height: 20),
              Text(
                item.summary,
                style: const TextStyle(fontSize: 13),
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
              ),
            ],

            if (item.outstandingItems != null &&
                item.outstandingItems!.isNotEmpty) ...[
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: isDark
                      ? NpontuColors.surfaceMid
                      : const Color(0xFFFEF2F2),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: NpontuColors.danger.withAlpha(50)),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(
                      Icons.warning_amber_rounded,
                      size: 16,
                      color: NpontuColors.danger,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Outstanding: ${item.outstandingItems!}',
                        style: TextStyle(
                          fontSize: 12,
                          color: isDark
                              ? Colors.white70
                              : const Color(0xFF991B1B),
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            if (canAccept) ...[
              const SizedBox(height: 14),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: NpontuColors.gold,
                    foregroundColor: const Color(0xFF1F2937),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                  onPressed: () => _openAcceptModal(context, item),
                  icon: const Icon(Icons.how_to_reg_rounded, size: 18),
                  label: const Text(
                    'Accept & Sign Off Incoming Shift',
                    style: TextStyle(fontWeight: FontWeight.w800),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  void _openAcceptModal(BuildContext context, ShiftHandoverModel item) {
    final remarksController = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('Sign Off Handover (#${item.id})'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'Confirming receipt of ${item.shift.toUpperCase()} shift briefing from ${item.outgoingUser?.name ?? 'Outgoing Operator'}.',
              style: const TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: remarksController,
              decoration: const InputDecoration(
                labelText: 'Acceptance Notes / Verification Comments',
                hintText:
                    'e.g. All alerts acknowledged, taking over active shift.',
              ),
              maxLines: 3,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: NpontuColors.green,
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              final success = await ref
                  .read(handoversControllerProvider.notifier)
                  .acceptHandover(item.id, remarksController.text.trim());

              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      success
                          ? 'Shift handover accepted and logged.'
                          : 'Failed to accept handover briefing.',
                    ),
                    backgroundColor: success
                        ? NpontuColors.green
                        : NpontuColors.danger,
                  ),
                );
              }
            },
            child: const Text('Sign & Accept'),
          ),
        ],
      ),
    );
  }
}
