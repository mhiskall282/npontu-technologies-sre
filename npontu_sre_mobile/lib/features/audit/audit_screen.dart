// lib/features/audit/audit_screen.dart

import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/models/audit_log_model.dart';
import '../../shared/widgets/app_drawer.dart';
import '../../shared/widgets/empty_state.dart';
import '../../shared/widgets/error_retry.dart';
import '../../shared/widgets/skeleton_loader.dart';
import 'audit_controller.dart';

class AuditScreen extends ConsumerStatefulWidget {
  const AuditScreen({super.key});

  @override
  ConsumerState<AuditScreen> createState() => _AuditScreenState();
}

class _AuditScreenState extends ConsumerState<AuditScreen> {
  String _selectedEvent = '';

  @override
  Widget build(BuildContext context) {
    final auditState = ref.watch(auditControllerProvider);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      drawer: const AppDrawer(currentRoute: '/audit'),
      appBar: AppBar(
        leading: Navigator.of(context).canPop()
            ? IconButton(
                icon: const Icon(Icons.arrow_back_rounded),
                tooltip: 'Go Back',
                onPressed: () => Navigator.of(context).pop(),
              )
            : null,
        title: const Text('Compliance Audit Trail'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: () => ref
                .read(auditControllerProvider.notifier)
                .loadAuditLogs(
                  event: _selectedEvent.isEmpty ? null : _selectedEvent,
                ),
          ),
        ],
      ),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () => ref
              .read(auditControllerProvider.notifier)
              .loadAuditLogs(
                event: _selectedEvent.isEmpty ? null : _selectedEvent,
              ),
          child: Column(
            children: [
              // Filter Bar
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                color: isDark ? NpontuColors.surfaceMid : const Color(0xFFF3F4F6),
                child: SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildEventChip('All Events', ''),
                      _buildEventChip('Created', 'created'),
                      _buildEventChip('Updated', 'updated'),
                      _buildEventChip('Status Changed', 'status_changed'),
                      _buildEventChip('Deleted', 'deleted'),
                      _buildEventChip('Accepted', 'accepted'),
                    ],
                  ),
                ),
              ),

              // Content
              Expanded(child: _buildBody(context, auditState, isDark)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEventChip(String label, String value) {
    final isSelected = _selectedEvent == value;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        selectedColor: NpontuColors.green,
        labelStyle: TextStyle(
          color: isSelected ? Colors.white : null,
          fontWeight: isSelected ? FontWeight.w700 : FontWeight.normal,
          fontSize: 12,
        ),
        onSelected: (selected) {
          setState(() => _selectedEvent = selected ? value : '');
          ref
              .read(auditControllerProvider.notifier)
              .loadAuditLogs(
                event: _selectedEvent.isEmpty ? null : _selectedEvent,
              );
        },
      ),
    );
  }

  Widget _buildBody(BuildContext context, AuditState state, bool isDark) {
    if (state.isLoading) {
      return const SkeletonListPlaceholder(count: 5);
    }

    if (state.errorMessage != null) {
      return ErrorRetryWidget(
        message: state.errorMessage!,
        onRetry: () => ref
            .read(auditControllerProvider.notifier)
            .loadAuditLogs(
              event: _selectedEvent.isEmpty ? null : _selectedEvent,
            ),
      );
    }

    if (state.logs.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.security_rounded,
        title: 'No Audit Records Found',
        subtitle:
            'All state mutation events are recorded immutably in this log.',
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: state.logs.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (ctx, index) {
        final log = state.logs[index];
        return _buildAuditCard(context, log, isDark);
      },
    );
  }

  Widget _buildAuditCard(BuildContext context, AuditLogModel log, bool isDark) {
    Color badgeColor;
    switch (log.event.toLowerCase()) {
      case 'created':
        badgeColor = NpontuColors.green;
        break;
      case 'updated':
        badgeColor = Colors.blue;
        break;
      case 'status_changed':
        badgeColor = NpontuColors.gold;
        break;
      case 'deleted':
        badgeColor = NpontuColors.danger;
        break;
      default:
        badgeColor = Colors.purple;
    }

    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      child: ExpansionTile(
        tilePadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        leading: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          decoration: BoxDecoration(
            color: badgeColor.withAlpha(30),
            borderRadius: BorderRadius.circular(6),
          ),
          child: Text(
            log.event.toUpperCase(),
            style: TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.w800,
              color: badgeColor,
            ),
          ),
        ),
        title: Text(
          '${log.subjectType.split(r'\').last} #${log.subjectId}',
          style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
        ),
        subtitle: Row(
          children: [
            Icon(
              Icons.account_circle_outlined,
              size: 13,
              color: isDark
                  ? NpontuColors.textSecondaryDark
                  : NpontuColors.textSecondaryLight,
            ),
            const SizedBox(width: 4),
            Text(
              log.actorName,
              style: TextStyle(
                fontSize: 12,
                color: isDark
                    ? NpontuColors.textSecondaryDark
                    : NpontuColors.textSecondaryLight,
              ),
            ),
            const Spacer(),
            Text(
              log.createdAt != null
                  ? '${log.createdAt!.year}-${log.createdAt!.month.toString().padLeft(2, '0')}-${log.createdAt!.day.toString().padLeft(2, '0')} ${log.createdAt!.hour.toString().padLeft(2, '0')}:${log.createdAt!.minute.toString().padLeft(2, '0')}'
                  : '',
              style: const TextStyle(fontSize: 11, color: Colors.grey),
            ),
          ],
        ),
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Divider(),
                if (log.ipAddress != null)
                  Text(
                    'IP Address: ${log.ipAddress}',
                    style: const TextStyle(fontSize: 11, color: Colors.grey),
                  ),
                const SizedBox(height: 8),
                if (log.oldValues != null && log.oldValues!.isNotEmpty) ...[
                  const Text(
                    'Previous Values (Old):',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                  ),
                  const SizedBox(height: 4),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: isDark
                          ? NpontuColors.surfaceDark
                          : const Color(0xFFF3F4F6),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      const JsonEncoder.withIndent('  ').convert(log.oldValues),
                      style: const TextStyle(
                        fontFamily: 'monospace',
                        fontSize: 11,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                ],
                if (log.newValues != null && log.newValues!.isNotEmpty) ...[
                  const Text(
                    'New Values:',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                  ),
                  const SizedBox(height: 4),
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: isDark
                          ? NpontuColors.surfaceDark
                          : const Color(0xFFF3F4F6),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      const JsonEncoder.withIndent('  ').convert(log.newValues),
                      style: const TextStyle(
                        fontFamily: 'monospace',
                        fontSize: 11,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
