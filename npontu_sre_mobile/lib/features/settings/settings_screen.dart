// lib/features/settings/settings_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:local_auth/local_auth.dart';

import '../../core/constants/app_constants.dart';
import '../../core/services/permission_service.dart';
import '../../core/theme/npontu_theme.dart';
import '../auth/presentation/auth_controller.dart';
import 'settings_controller.dart';

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settings = ref.watch(settingsControllerProvider);
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;

    return Scaffold(
      appBar: AppBar(title: const Text('Settings')),
      body: ListView(
        children: [
          // ── Profile Section ──────────────────────────────────────────────
          _SectionHeader(title: 'Profile'),
          _ProfileCard(
            name: user?.name ?? '—',
            email: user?.email ?? '—',
            role: user?.role ?? '—',
            grade: user?.gradeLabel ?? '—',
            designation: user?.designation,
            department: user?.department,
          ),

          // ── Appearance ──────────────────────────────────────────────────
          _SectionHeader(title: 'Appearance'),
          _SettingsTile(
            icon: Icons.brightness_6_rounded,
            title: 'Theme',
            subtitle: _themeLabel(settings.themeMode),
            trailing: DropdownButton<ThemeMode>(
              value: settings.themeMode,
              underline: const SizedBox.shrink(),
              items: const [
                DropdownMenuItem(
                  value: ThemeMode.system,
                  child: Text('System'),
                ),
                DropdownMenuItem(value: ThemeMode.light, child: Text('Light')),
                DropdownMenuItem(value: ThemeMode.dark, child: Text('Dark')),
              ],
              onChanged: (mode) {
                if (mode != null) {
                  ref
                      .read(settingsControllerProvider.notifier)
                      .setThemeMode(mode);
                }
              },
            ),
          ),

          // ── Notifications ────────────────────────────────────────────────
          _SectionHeader(title: 'Notifications'),
          _SettingsSwitchTile(
            icon: Icons.notifications_rounded,
            title: 'Enable Notifications',
            subtitle: 'Receive push alerts from the SRE platform',
            value: settings.notificationsEnabled,
            onChanged: (v) async {
              if (v) {
                final svc = ref.read(permissionServiceProvider);
                final result = await svc.requestNotificationPermission(context);
                if (result == PermissionResult.granted) {
                  ref
                      .read(settingsControllerProvider.notifier)
                      .setNotificationsEnabled(true);
                }
              } else {
                ref
                    .read(settingsControllerProvider.notifier)
                    .setNotificationsEnabled(false);
              }
            },
          ),
          if (settings.notificationsEnabled) ...[
            _SettingsSwitchTile(
              icon: Icons.warning_amber_rounded,
              title: 'Incident Alerts',
              subtitle: 'P1/P2 incidents and escalations',
              value: settings.notifyIncidents,
              onChanged: (v) => ref
                  .read(settingsControllerProvider.notifier)
                  .setNotifyIncidents(v),
              iconColor: NpontuColors.criticalP1,
              indent: true,
            ),
            _SettingsSwitchTile(
              icon: Icons.swap_horiz_rounded,
              title: 'Shift Handovers',
              subtitle: 'Handover sign-on requests',
              value: settings.notifyHandovers,
              onChanged: (v) => ref
                  .read(settingsControllerProvider.notifier)
                  .setNotifyHandovers(v),
              iconColor: NpontuColors.goldWarm,
              indent: true,
            ),
            _SettingsSwitchTile(
              icon: Icons.assignment_ind_rounded,
              title: 'Task Assignments',
              subtitle: 'When a task is assigned to you',
              value: settings.notifyAssignments,
              onChanged: (v) => ref
                  .read(settingsControllerProvider.notifier)
                  .setNotifyAssignments(v),
              iconColor: NpontuColors.greenLight,
              indent: true,
            ),
          ],

          // ── Security ─────────────────────────────────────────────────────
          _SectionHeader(title: 'Security'),
          _SettingsSwitchTile(
            icon: Icons.fingerprint_rounded,
            title: 'Biometric Sign-in',
            subtitle: 'Use fingerprint or Face ID to authenticate',
            value: settings.biometricEnabled,
            onChanged: (v) async {
              if (v) {
                // Verify device actually supports biometrics before enabling
                final localAuth = LocalAuthentication();
                final canCheck = await localAuth.canCheckBiometrics;
                final isSupported = await localAuth.isDeviceSupported();

                if (!canCheck || !isSupported) {
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text(
                          'Biometric authentication is not available on this device.',
                        ),
                      ),
                    );
                  }
                  return;
                }

                // Biometric is gated by local_auth — no separate OS permission call needed.
              }

              ref
                  .read(settingsControllerProvider.notifier)
                  .setBiometricEnabled(v);
            },
          ),
          _SettingsTile(
            icon: Icons.location_on_rounded,
            title: 'Location Access',
            subtitle: 'Used for on-site check-in tagging',
            onTap: () async {
              final svc = ref.read(permissionServiceProvider);
              final result = await svc.requestLocationPermission(context);
              if (context.mounted) {
                final msg = result == PermissionResult.granted
                    ? 'Location permission granted.'
                    : 'Location permission not granted.';
                ScaffoldMessenger.of(context)
                    .showSnackBar(SnackBar(content: Text(msg)));
              }
            },
          ),
          _SettingsTile(
            icon: Icons.camera_alt_rounded,
            title: 'Camera Access',
            subtitle: 'QR scanning and photographic evidence',
            onTap: () async {
              final svc = ref.read(permissionServiceProvider);
              final result = await svc.requestCameraPermission(context);
              if (context.mounted) {
                final msg = result == PermissionResult.granted
                    ? 'Camera permission granted.'
                    : 'Camera permission not granted.';
                ScaffoldMessenger.of(context)
                    .showSnackBar(SnackBar(content: Text(msg)));
              }
            },
          ),

          // ── About ────────────────────────────────────────────────────────
          _SectionHeader(title: 'About'),
          _SettingsTile(
            icon: Icons.info_outline_rounded,
            title: 'App Version',
            subtitle:
                '${AppConstants.appVersion} (Build ${AppConstants.buildNumber})',
          ),
          _SettingsTile(
            icon: Icons.cloud_rounded,
            title: 'API Endpoint',
            subtitle: AppConstants.baseUrl,
          ),
          _SettingsTile(
            icon: Icons.business_rounded,
            title: 'Organisation',
            subtitle: 'Npontu Technologies · SRE Operations',
          ),

          // ── Sign Out ─────────────────────────────────────────────────────
          const SizedBox(height: 24),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: OutlinedButton.icon(
              onPressed: () => _confirmSignOut(context, ref),
              icon: const Icon(
                Icons.logout_rounded,
                color: NpontuColors.danger,
              ),
              label: const Text(
                'Sign Out',
                style: TextStyle(
                  color: NpontuColors.danger,
                  fontWeight: FontWeight.w600,
                ),
              ),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: NpontuColors.danger),
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
            ),
          ),
          const SizedBox(height: 40),
        ],
      ),
    );
  }

  String _themeLabel(ThemeMode mode) {
    switch (mode) {
      case ThemeMode.light:
        return 'Light';
      case ThemeMode.dark:
        return 'Dark';
      case ThemeMode.system:
        return 'System default';
    }
  }

  Future<void> _confirmSignOut(BuildContext context, WidgetRef ref) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Sign Out'),
        content: const Text(
          'Are you sure you want to sign out? '
          'You will need to enter your credentials again.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: NpontuColors.danger),
            onPressed: () => Navigator.of(ctx).pop(true),
            child: const Text('Sign Out'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await ref.read(authControllerProvider.notifier).logout();
      if (context.mounted) context.go('/login');
    }
  }
}

// ─── Reusable Sub-widgets ──────────────────────────────────────────────────

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.title});
  final String title;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 6),
      child: Text(
        title.toUpperCase(),
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.8,
          color: NpontuColors.green,
        ),
      ),
    );
  }
}

class _ProfileCard extends StatelessWidget {
  const _ProfileCard({
    required this.name,
    required this.email,
    required this.role,
    required this.grade,
    this.designation,
    this.department,
  });

  final String name;
  final String email;
  final String role;
  final String grade;
  final String? designation;
  final String? department;

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            CircleAvatar(
              radius: 28,
              backgroundColor: NpontuColors.green,
              child: Text(
                name.isNotEmpty ? name[0].toUpperCase() : '?',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    name,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(email, style: const TextStyle(fontSize: 13)),
                  const SizedBox(height: 4),
                  Wrap(
                    spacing: 6,
                    children: [
                      _RoleBadge(label: role.toUpperCase()),
                      _RoleBadge(label: grade, color: NpontuColors.goldWarm),
                      if (department != null)
                        _RoleBadge(
                          label: department!,
                          color: NpontuColors.greenLight,
                        ),
                    ],
                  ),
                  if (designation != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 4),
                      child: Text(
                        designation!,
                        style: TextStyle(
                          fontSize: 12,
                          color: isDark
                              ? NpontuColors.textSecondaryDark
                              : NpontuColors.textSecondaryLight,
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _RoleBadge extends StatelessWidget {
  const _RoleBadge({required this.label, this.color = NpontuColors.green});
  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color.withAlpha(20),
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: color.withAlpha(60)),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w700,
          color: color,
        ),
      ),
    );
  }
}

class _SettingsTile extends StatelessWidget {
  const _SettingsTile({
    required this.icon,
    required this.title,
    this.subtitle,
    this.trailing,
    this.onTap,
  });

  final IconData icon;
  final String title;
  final String? subtitle;
  final Widget? trailing;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Icon(icon, color: NpontuColors.green, size: 22),
      title: Text(
        title,
        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
      ),
      subtitle: subtitle != null
          ? Text(
              subtitle!,
              style: TextStyle(
                fontSize: 12,
                color: Theme.of(context).brightness == Brightness.dark
                    ? NpontuColors.textSecondaryDark
                    : NpontuColors.textSecondaryLight,
              ),
            )
          : null,
      trailing:
          trailing ??
          (onTap != null ? const Icon(Icons.chevron_right_rounded) : null),
      onTap: onTap,
    );
  }
}

class _SettingsSwitchTile extends StatelessWidget {
  const _SettingsSwitchTile({
    required this.icon,
    required this.title,
    this.subtitle,
    required this.value,
    required this.onChanged,
    this.iconColor = NpontuColors.green,
    this.indent = false,
  });

  final IconData icon;
  final String title;
  final String? subtitle;
  final bool value;
  final ValueChanged<bool> onChanged;
  final Color iconColor;
  final bool indent;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: EdgeInsets.only(left: indent ? 32 : 16, right: 16),
      leading: Icon(icon, color: iconColor, size: 22),
      title: Text(
        title,
        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
      ),
      subtitle: subtitle != null
          ? Text(
              subtitle!,
              style: TextStyle(
                fontSize: 12,
                color: Theme.of(context).brightness == Brightness.dark
                    ? NpontuColors.textSecondaryDark
                    : NpontuColors.textSecondaryLight,
              ),
            )
          : null,
      trailing: Switch(
        value: value,
        onChanged: onChanged,
        activeColor: NpontuColors.green,
      ),
    );
  }
}
