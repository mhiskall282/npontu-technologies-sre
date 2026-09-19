import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/constants/app_constants.dart';
import '../../core/services/notification_service.dart';
import '../../core/theme/npontu_theme.dart';
import '../../features/auth/presentation/auth_controller.dart';
import '../../features/workspaces/workspace_switcher_sheet.dart';
import 'user_profile_sheet.dart';

class AppDrawer extends ConsumerWidget {
  final String currentRoute;

  const AppDrawer({super.key, required this.currentRoute});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Drawer(
      backgroundColor: isDark ? NpontuColors.surfaceDark : Colors.white,
      child: Column(
        children: [
          // Operator Bio Header (Clickable to view Profile & Seniority)
          Material(
            color: NpontuColors.green,
            child: InkWell(
              onTap: user != null
                  ? () {
                      Navigator.pop(context);
                      UserProfileSheet.show(context, user: user);
                    }
                  : null,
              child: Container(
                width: double.infinity,
                padding: EdgeInsets.only(
                  top: MediaQuery.of(context).padding.top + 20,
                  bottom: 20,
                  left: 20,
                  right: 20,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        CircleAvatar(
                          radius: 24,
                          backgroundColor: Colors.white,
                          child: Text(
                            (user?.name.isNotEmpty == true)
                                ? user!.name[0].toUpperCase()
                                : 'S',
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.w800,
                              color: NpontuColors.green,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                user?.name ?? 'SRE Operator',
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w700,
                                  color: Colors.white,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                user?.email ?? '',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: Colors.white.withAlpha(200),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                        const Icon(
                          Icons.chevron_right_rounded,
                          color: Colors.white70,
                          size: 20,
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: NpontuColors.gold,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            user?.role.toUpperCase() ?? 'OPERATOR',
                            style: const TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF1F2937),
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.white.withAlpha(50),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            user?.gradeLabel ?? 'L1 Support',
                            style: const TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Active SaaS Tenant & Workspace Card
          Container(
            margin: const EdgeInsets.fromLTRB(12, 10, 12, 4),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF16241B) : const Color(0xFFEBF5EE),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: NpontuColors.green.withAlpha(isDark ? 80 : 50),
              ),
            ),
            child: InkWell(
              onTap: () {
                Navigator.pop(context);
                WorkspaceSwitcherSheet.show(context);
              },
              borderRadius: BorderRadius.circular(8),
              child: Row(
                children: [
                  Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: NpontuColors.green,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.corporate_fare_rounded,
                      color: Colors.white,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Flexible(
                              child: Text(
                                user?.organizationName ?? 'Npontu Technologies',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                  color: isDark ? Colors.white : const Color(0xFF111827),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            if (user?.companyCode != null) ...[
                              const SizedBox(width: 4),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                decoration: BoxDecoration(
                                  color: NpontuColors.gold.withAlpha(40),
                                  borderRadius: BorderRadius.circular(4),
                                  border: Border.all(color: NpontuColors.gold.withAlpha(120), width: 0.5),
                                ),
                                child: Text(
                                  user!.companyCode!,
                                  style: TextStyle(
                                    fontSize: 9,
                                    fontWeight: FontWeight.w800,
                                    fontFamily: 'monospace',
                                    color: isDark ? NpontuColors.gold : const Color(0xFF92400E),
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                        const SizedBox(height: 2),
                        Row(
                          children: [
                            Icon(
                              Icons.layers_outlined,
                              size: 11,
                              color: isDark ? NpontuColors.textSecondaryDark : NpontuColors.textSecondaryLight,
                            ),
                            const SizedBox(width: 3),
                            Expanded(
                              child: Text(
                                user?.currentWorkspaceName ?? 'Production Cockpit',
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w500,
                                  color: isDark ? NpontuColors.textSecondaryDark : NpontuColors.textSecondaryLight,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                              decoration: BoxDecoration(
                                color: NpontuColors.green.withAlpha(30),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                (user?.tier ?? 'Enterprise').toUpperCase(),
                                style: const TextStyle(
                                  fontSize: 8,
                                  fontWeight: FontWeight.w800,
                                  color: NpontuColors.green,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 4),
                  const Icon(
                    Icons.swap_horiz_rounded,
                    color: NpontuColors.gold,
                    size: 20,
                  ),
                ],
              ),
            ),
          ),

          // Drawer Navigation Items
          Expanded(
            child: ListView(
              padding: const EdgeInsets.symmetric(vertical: 8),
              children: [
                _buildDrawerItem(
                  context,
                  title: 'Operations Cockpit',
                  icon: Icons.dashboard_rounded,
                  route: '/',
                  isSelected: currentRoute == '/',
                ),
                _buildDrawerItem(
                  context,
                  title: 'Shift Checklist',
                  icon: Icons.checklist_rounded,
                  route: '/activities',
                  isSelected: currentRoute == '/activities',
                ),
                _buildDrawerItem(
                  context,
                  title: 'Shift Handovers',
                  icon: Icons.swap_horiz_rounded,
                  route: '/handovers',
                  isSelected: currentRoute == '/handovers',
                ),
                _buildDrawerItem(
                  context,
                  title: 'Ops Chat & War Rooms',
                  icon: Icons.forum_rounded,
                  route: '/messaging',
                  isSelected: currentRoute == '/messaging',
                  badgeCount: user?.unreadMessagesCount ?? 0,
                ),
                _buildDrawerItem(
                  context,
                  title: 'SRE Diagnostics',
                  icon: Icons.speed_rounded,
                  route: '/health',
                  isSelected: currentRoute == '/health',
                ),
                // Compliance Reports (only visible to users with export_reports or lead/admin)
                if (user?.canExportReports == true ||
                    user?.isAdmin == true ||
                    user?.isLead == true)
                  _buildDrawerItem(
                    context,
                    title: 'Compliance Reports',
                    icon: Icons.analytics_rounded,
                    route: '/reports',
                    isSelected: currentRoute == '/reports',
                  ),
                _buildDrawerItem(
                  context,
                  title: 'Team Directory',
                  icon: Icons.people_alt_rounded,
                  route: '/team',
                  isSelected: currentRoute == '/team',
                ),
                // Security Audit Trail (strictly gated to view_audit_logs / admin)
                if (user?.canViewAuditLogs == true || user?.isAdmin == true)
                  _buildDrawerItem(
                    context,
                    title: 'Security Audit Trail',
                    icon: Icons.security_rounded,
                    route: '/audit',
                    isSelected: currentRoute == '/audit',
                  ),
                const Divider(height: 1, indent: 20, endIndent: 20),
                Consumer(
                  builder: (ctx, ref, _) {
                    final unread = ref.watch(notificationBadgeCountProvider);
                    return _buildDrawerItem(
                      context,
                      title: 'Notifications',
                      icon: Icons.notifications_outlined,
                      route: '/notifications',
                      isSelected: currentRoute == '/notifications',
                      badgeCount: unread,
                    );
                  },
                ),
                _buildDrawerItem(
                  context,
                  title: 'Settings',
                  icon: Icons.settings_outlined,
                  route: '/settings',
                  isSelected: currentRoute == '/settings',
                ),
              ],
            ),
          ),

          const Divider(height: 1),

          // Logout Action
          ListTile(
            leading: const Icon(
              Icons.logout_rounded,
              color: NpontuColors.danger,
            ),
            title: const Text(
              'Sign Out',
              style: TextStyle(
                color: NpontuColors.danger,
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
            ),
            onTap: () async {
              final confirm = await showDialog<bool>(
                context: context,
                builder: (ctx) => AlertDialog(
                  title: const Text('Confirm Sign Out'),
                  content: const Text(
                    'Are you sure you want to revoke this session and sign out?',
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
                      child: const Text('Sign Out'),
                    ),
                  ],
                ),
              );

              if (confirm == true) {
                await ref.read(authControllerProvider.notifier).logout();
                if (context.mounted) {
                  context.go('/login');
                }
              }
            },
          ),
          Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(context).padding.bottom + 16,
              top: 4,
            ),
            child: Text(
              '${AppConstants.appName} Mobile • v${AppConstants.appVersion}',
              style: TextStyle(
                fontSize: 11,
                color: isDark
                    ? NpontuColors.textSecondaryDark
                    : NpontuColors.textSecondaryLight,
              ),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDrawerItem(
    BuildContext context, {
    required String title,
    required IconData icon,
    required String route,
    required bool isSelected,
    int badgeCount = 0,
  }) {
    final color = isSelected ? NpontuColors.green : null;

    return ListTile(
      leading: Icon(icon, color: color),
      title: Text(
        title,
        style: TextStyle(
          fontSize: 14,
          fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
          color: color,
        ),
      ),
      trailing: badgeCount > 0
          ? Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
              decoration: BoxDecoration(
                color: NpontuColors.danger,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                '$badgeCount',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
            )
          : null,
      selected: isSelected,
      selectedTileColor: NpontuColors.green.withAlpha(20),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 2),
      onTap: () {
        Navigator.pop(context); // close drawer
        if (!isSelected) {
          // Use push navigation for sub-sections so that the
          // back button works correctly in their AppBars.
          // Main sections (dashboard) use go() for root navigation.
          if (route == '/') {
            context.go(route);
          } else {
            context.push(route);
          }
        }
      },
    );
  }
}
