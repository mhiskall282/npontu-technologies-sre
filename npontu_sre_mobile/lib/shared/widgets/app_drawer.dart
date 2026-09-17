import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/constants/app_constants.dart';
import '../../core/services/notification_service.dart';
import '../../core/theme/npontu_theme.dart';
import '../../features/auth/presentation/auth_controller.dart';

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
          // Operator Bio Header
          Container(
            width: double.infinity,
            padding: EdgeInsets.only(
              top: MediaQuery.of(context).padding.top + 20,
              bottom: 20,
              left: 20,
              right: 20,
            ),
            decoration: const BoxDecoration(color: NpontuColors.green),
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
            padding: const EdgeInsets.only(bottom: 16, top: 4),
            child: Text(
              '${AppConstants.appName} Mobile • v${AppConstants.version}',
              style: TextStyle(
                fontSize: 11,
                color: isDark
                    ? NpontuColors.textSecondaryDark
                    : NpontuColors.textSecondaryLight,
              ),
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
          context.go(route);
        }
      },
    );
  }
}
