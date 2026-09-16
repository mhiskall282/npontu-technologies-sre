// lib/features/team/team_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/models/user_model.dart';
import '../../shared/widgets/app_drawer.dart';
import '../../shared/widgets/empty_state.dart';
import '../../shared/widgets/error_retry.dart';
import '../../shared/widgets/skeleton_loader.dart';
import 'team_controller.dart';

class TeamScreen extends ConsumerStatefulWidget {
  const TeamScreen({super.key});

  @override
  ConsumerState<TeamScreen> createState() => _TeamScreenState();
}

class _TeamScreenState extends ConsumerState<TeamScreen> {
  final _searchController = TextEditingController();
  String _selectedRole = '';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final teamState = ref.watch(teamControllerProvider);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      drawer: const AppDrawer(currentRoute: '/team'),
      appBar: AppBar(
        title: const Text('SRE Operations Team'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: () => ref
                .read(teamControllerProvider.notifier)
                .loadTeam(
                  role: _selectedRole.isEmpty ? null : _selectedRole,
                  search: _searchController.text.trim().isEmpty
                      ? null
                      : _searchController.text.trim(),
                ),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => ref
            .read(teamControllerProvider.notifier)
            .loadTeam(
              role: _selectedRole.isEmpty ? null : _selectedRole,
              search: _searchController.text.trim().isEmpty
                  ? null
                  : _searchController.text.trim(),
            ),
        child: Column(
          children: [
            // Search & Filter Box
            Container(
              padding: const EdgeInsets.all(12),
              color: isDark ? NpontuColors.surfaceMid : const Color(0xFFF3F4F6),
              child: Column(
                children: [
                  TextField(
                    controller: _searchController,
                    onChanged: (val) {
                      ref
                          .read(teamControllerProvider.notifier)
                          .loadTeam(
                            role: _selectedRole.isEmpty ? null : _selectedRole,
                            search: val.trim().isEmpty ? null : val.trim(),
                          );
                    },
                    decoration: InputDecoration(
                      hintText: 'Search by operator name or email...',
                      prefixIcon: const Icon(Icons.search_rounded),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 10,
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(24),
                        borderSide: BorderSide.none,
                      ),
                      filled: true,
                      fillColor: isDark
                          ? NpontuColors.surfaceDark
                          : Colors.white,
                    ),
                  ),
                  const SizedBox(height: 8),
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _buildFilterChip('All Members', ''),
                        _buildFilterChip('Admins', 'admin'),
                        _buildFilterChip('Leads', 'lead'),
                        _buildFilterChip('Engineers', 'engineer'),
                        _buildFilterChip('Agents', 'agent'),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // Main List
            Expanded(child: _buildBody(context, teamState, isDark)),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _selectedRole == value;
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
          setState(() => _selectedRole = selected ? value : '');
          ref
              .read(teamControllerProvider.notifier)
              .loadTeam(
                role: _selectedRole.isEmpty ? null : _selectedRole,
                search: _searchController.text.trim().isEmpty
                    ? null
                    : _searchController.text.trim(),
              );
        },
      ),
    );
  }

  Widget _buildBody(BuildContext context, TeamState state, bool isDark) {
    if (state.isLoading) {
      return const SkeletonListPlaceholder(count: 5);
    }

    if (state.errorMessage != null) {
      return ErrorRetryWidget(
        message: state.errorMessage!,
        onRetry: () => ref
            .read(teamControllerProvider.notifier)
            .loadTeam(
              role: _selectedRole.isEmpty ? null : _selectedRole,
              search: _searchController.text.trim().isEmpty
                  ? null
                  : _searchController.text.trim(),
            ),
      );
    }

    if (state.members.isEmpty) {
      return const EmptyStateWidget(
        icon: Icons.person_off_rounded,
        title: 'No Team Members Found',
        subtitle: 'Try adjusting your search or role filters.',
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.all(16),
      itemCount: state.members.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (ctx, index) {
        final member = state.members[index];
        return _buildMemberCard(context, member, isDark);
      },
    );
  }

  Widget _buildMemberCard(BuildContext context, UserModel member, bool isDark) {
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          children: [
            CircleAvatar(
              radius: 22,
              backgroundColor: NpontuColors.green,
              child: Text(
                member.name.isNotEmpty ? member.name[0].toUpperCase() : 'U',
                style: const TextStyle(
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                  fontSize: 18,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    member.name,
                    style: const TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 15,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    member.email,
                    style: const TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: NpontuColors.gold,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          member.role.toUpperCase(),
                          style: const TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF1F2937),
                          ),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? NpontuColors.surfaceMid
                              : const Color(0xFFE5E7EB),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          member.gradeLabel,
                          style: const TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            IconButton(
              icon: const Icon(
                Icons.chat_bubble_outline_rounded,
                color: NpontuColors.green,
              ),
              tooltip: 'Message Operator',
              onPressed: () {
                context.push('/messaging');
              },
            ),
          ],
        ),
      ),
    );
  }
}
