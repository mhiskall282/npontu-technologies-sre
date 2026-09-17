// lib/shared/widgets/user_profile_sheet.dart

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/network/api_client_provider.dart';
import '../../core/theme/npontu_theme.dart';
import '../../features/auth/presentation/auth_controller.dart';
import '../../features/team/team_controller.dart';
import '../models/user_model.dart';

/// Interactive modal sheet displaying detailed operator profile, SRE seniority tier,
/// operational roles, departmental pod, and granular security privileges.
class UserProfileSheet extends ConsumerWidget {
  final UserModel user;

  const UserProfileSheet({super.key, required this.user});

  /// Displays the profile modal sheet for a given [UserModel].
  static Future<void> show(BuildContext context, {required UserModel user}) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => UserProfileSheet(user: user),
    );
  }

  /// Displays the profile modal sheet by user ID, fetching if necessary.
  static Future<void> showById(
    BuildContext context, {
    required int userId,
    required WidgetRef ref,
  }) async {
    // Check if it matches currently logged-in user
    final current = ref.read(authControllerProvider).user;
    if (current != null && current.id == userId) {
      return show(context, user: current);
    }

    // Check loaded team members
    final teamState = ref.read(teamControllerProvider);
    final member = teamState.members.cast<UserModel?>().firstWhere(
      (m) => m?.id == userId,
      orElse: () => null,
    );
    if (member != null) {
      return show(context, user: member);
    }

    // If not in memory, show loading dialog while fetching
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(
        child: CircularProgressIndicator(color: NpontuColors.green),
      ),
    );

    try {
      final client = ref.read(apiClientProvider);
      final res = await client.get('/team/$userId');
      if (context.mounted) {
        Navigator.pop(context); // Dismiss loading
      }
      final data = res.data['data'] as Map<String, dynamic>;
      final fetchedUser = UserModel.fromJson(data);
      if (context.mounted) {
        await show(context, user: fetchedUser);
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.pop(context); // Dismiss loading
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to load profile for operator #$userId'),
            backgroundColor: NpontuColors.danger,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final currentUser = ref.watch(authControllerProvider).user;
    final isSelf = currentUser?.id == user.id;

    final bgColor = isDark ? NpontuColors.surfaceDark : Colors.white;
    final cardBg = isDark ? NpontuColors.surfaceMid : const Color(0xFFF9FAFB);
    final borderColor = isDark
        ? const Color(0xFF374151)
        : const Color(0xFFE5E7EB);

    return DraggableScrollableSheet(
      initialChildSize: 0.82,
      maxChildSize: 0.95,
      minChildSize: 0.5,
      builder: (context, scrollController) {
        return Container(
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withAlpha(50),
                blurRadius: 20,
                offset: const Offset(0, -5),
              ),
            ],
          ),
          child: Column(
            children: [
              // Drag Handle
              Center(
                child: Container(
                  margin: const EdgeInsets.only(top: 12, bottom: 8),
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.withAlpha(100),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),

              // Sheet Content
              Expanded(
                child: ListView(
                  controller: scrollController,
                  padding: const EdgeInsets.fromLTRB(20, 4, 20, 28),
                  children: [
                    // Header Bar with Close Button
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          isSelf ? 'My SRE Profile' : 'Operator Profile',
                          style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 0.8,
                            color: Colors.grey,
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close_rounded, size: 22),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),

                    // Avatar & Primary Info Block
                    Center(
                      child: Column(
                        children: [
                          Stack(
                            alignment: Alignment.bottomRight,
                            children: [
                              Container(
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: NpontuColors.gold,
                                    width: 3,
                                  ),
                                  boxShadow: [
                                    BoxShadow(
                                      color: NpontuColors.green.withAlpha(40),
                                      blurRadius: 16,
                                      spreadRadius: 2,
                                    ),
                                  ],
                                ),
                                child: CircleAvatar(
                                  radius: 40,
                                  backgroundColor: NpontuColors.green,
                                  child: Text(
                                    user.name.isNotEmpty
                                        ? user.name[0].toUpperCase()
                                        : 'U',
                                    style: const TextStyle(
                                      fontSize: 32,
                                      fontWeight: FontWeight.w900,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
                              ),
                              Container(
                                width: 22,
                                height: 22,
                                decoration: BoxDecoration(
                                  color: const Color(0xFF10B981),
                                  shape: BoxShape.circle,
                                  border: Border.all(
                                    color: bgColor,
                                    width: 2.5,
                                  ),
                                ),
                                child: const Icon(
                                  Icons.check,
                                  size: 13,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Text(
                            user.name,
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.w800,
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 4),
                          InkWell(
                            onTap: () {
                              Clipboard.setData(
                                ClipboardData(text: user.email),
                              );
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(
                                  content: Text('Email copied to clipboard'),
                                  duration: Duration(seconds: 2),
                                ),
                              );
                            },
                            borderRadius: BorderRadius.circular(6),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.email_outlined,
                                    size: 14,
                                    color: Colors.grey,
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    user.email,
                                    style: const TextStyle(
                                      fontSize: 13,
                                      color: Colors.grey,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 10),

                          // Role & Grade Chips
                          Wrap(
                            alignment: WrapAlignment.center,
                            spacing: 8,
                            runSpacing: 6,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: NpontuColors.gold,
                                  borderRadius: BorderRadius.circular(20),
                                  boxShadow: [
                                    BoxShadow(
                                      color: NpontuColors.gold.withAlpha(60),
                                      blurRadius: 4,
                                    ),
                                  ],
                                ),
                                child: Text(
                                  user.role.toUpperCase(),
                                  style: const TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF1F2937),
                                    letterSpacing: 0.5,
                                  ),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: NpontuColors.green,
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  user.grade,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w800,
                                    color: Colors.white,
                                  ),
                                ),
                              ),
                              if (user.department != null &&
                                  user.department!.isNotEmpty)
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 10,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: isDark
                                        ? const Color(0xFF374151)
                                        : const Color(0xFFE5E7EB),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(
                                    user.department!,
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: isDark
                                          ? Colors.white70
                                          : const Color(0xFF374151),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),

                    // Seniority & Engineering Grade Card
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: cardBg,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: borderColor),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color: NpontuColors.green.withAlpha(25),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: const Icon(
                                  Icons.military_tech_rounded,
                                  color: NpontuColors.green,
                                  size: 20,
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'SRE SENIORITY & GRADE',
                                      style: TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w800,
                                        letterSpacing: 0.8,
                                        color: Colors.grey,
                                      ),
                                    ),
                                    Text(
                                      user.gradeLabel,
                                      style: const TextStyle(
                                        fontSize: 14,
                                        fontWeight: FontWeight.w800,
                                        color: NpontuColors.green,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Text(
                            user.seniorityDescription,
                            style: TextStyle(
                              fontSize: 12,
                              height: 1.4,
                              color: isDark
                                  ? Colors.white70
                                  : const Color(0xFF4B5563),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 14),

                    // Operational Assignment & Contact Info
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: cardBg,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: borderColor),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'ASSIGNMENT & CONTACT',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 0.8,
                              color: Colors.grey,
                            ),
                          ),
                          const SizedBox(height: 12),
                          _buildDetailRow(
                            icon: Icons.business_rounded,
                            label: 'Department / Pod',
                            value: user.department ?? 'Core Operations (NOC)',
                            isDark: isDark,
                          ),
                          const Divider(height: 18),
                          _buildDetailRow(
                            icon: Icons.badge_outlined,
                            label: 'Designation',
                            value: user.designation ?? user.roleLabel,
                            isDark: isDark,
                          ),
                          const Divider(height: 18),
                          _buildDetailRow(
                            icon: Icons.phone_outlined,
                            label: 'Operational Hotline',
                            value: user.phone != null && user.phone!.isNotEmpty
                                ? user.phone!
                                : 'Not registered',
                            isDark: isDark,
                            onTap: user.phone != null && user.phone!.isNotEmpty
                                ? () {
                                    Clipboard.setData(
                                      ClipboardData(text: user.phone!),
                                    );
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(
                                        content: Text(
                                          'Phone copied to clipboard',
                                        ),
                                        duration: Duration(seconds: 2),
                                      ),
                                    );
                                  }
                                : null,
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 14),

                    // System Privileges & Clearance
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: cardBg,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: borderColor),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'SYSTEM PRIVILEGES & CLEARANCES',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.8,
                                  color: Colors.grey,
                                ),
                              ),
                              Text(
                                user.isAdmin
                                    ? 'ROOT ACCESS'
                                    : '${user.privileges.length} GRANTED',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w800,
                                  color: user.isAdmin
                                      ? NpontuColors.danger
                                      : NpontuColors.green,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          if (user.isAdmin) ...[
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: NpontuColors.danger.withAlpha(20),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                  color: NpontuColors.danger.withAlpha(50),
                                ),
                              ),
                              child: const Row(
                                children: [
                                  Icon(
                                    Icons.shield_rounded,
                                    size: 18,
                                    color: NpontuColors.danger,
                                  ),
                                  SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'Full Administrator privileges enabled. Possesses unrestricted mutation clearance across activities, handovers, and audit trails.',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ] else ...[
                            Wrap(
                              spacing: 6,
                              runSpacing: 6,
                              children: UserModel.privilegeCatalog.entries.map((
                                entry,
                              ) {
                                final isGranted = user.hasPrivilege(entry.key);
                                if (!isGranted) return const SizedBox.shrink();

                                return Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: isDark
                                        ? const Color(0xFF1E3A2B)
                                        : const Color(0xFFECFDF5),
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(
                                      color: isDark
                                          ? const Color(0xFF059669)
                                          : const Color(0xFFA7F3D0),
                                    ),
                                  ),
                                  child: Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      const Icon(
                                        Icons.check_circle_rounded,
                                        size: 13,
                                        color: Color(0xFF10B981),
                                      ),
                                      const SizedBox(width: 5),
                                      Text(
                                        entry.value,
                                        style: TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w600,
                                          color: isDark
                                              ? const Color(0xFFA7F3D0)
                                              : const Color(0xFF065F46),
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              }).toList(),
                            ),
                          ],
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),

                    // Quick Action Buttons
                    if (!isSelf) ...[
                      ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: NpontuColors.green,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        onPressed: () {
                          Navigator.pop(context); // close sheet
                          context.push('/messaging');
                        },
                        icon: const Icon(Icons.chat_bubble_outline_rounded),
                        label: Text(
                          'Direct Message ${user.name.split(' ').first}',
                          style: const TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ),
                    ] else ...[
                      ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: NpontuColors.green,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        onPressed: () {
                          Navigator.pop(context); // close sheet
                          context.push('/settings');
                        },
                        icon: const Icon(Icons.settings_outlined),
                        label: const Text(
                          'Manage Account & Team Settings',
                          style: TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildDetailRow({
    required IconData icon,
    required String label,
    required String value,
    required bool isDark,
    VoidCallback? onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(6),
      child: Row(
        children: [
          Icon(icon, size: 18, color: Colors.grey),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(fontSize: 10, color: Colors.grey),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
          if (onTap != null)
            const Icon(Icons.copy_rounded, size: 14, color: Colors.grey),
        ],
      ),
    );
  }
}
