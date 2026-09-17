import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../core/theme/npontu_theme.dart';

/// In-app Privacy Policy, Data Handling & App Store / Google Play Compliance Sheet.
///
/// Complies with Apple App Store Review Guideline 5.1.1 (Data Collection & Storage),
/// Apple Guideline 5.1.1(v) (Account Deletion), Google Play User Data Policies,
/// and Ghana Data Protection Act 2012 (Act 843).
class PrivacyPolicySheet extends StatelessWidget {
  const PrivacyPolicySheet({super.key});

  /// Displays the privacy policy modal bottom sheet.
  static Future<void> show(BuildContext context) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const PrivacyPolicySheet(),
    );
  }

  /// Displays the account deletion request dialogue.
  static Future<void> showAccountDeletionDialog(BuildContext context) {
    return showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: const Color(0xFF0F1E15),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: Color(0xFF234B32)),
        ),
        title: Row(
          children: [
            Icon(Icons.delete_forever_rounded, color: NpontuColors.danger),
            const SizedBox(width: 10),
            const Text(
              'Account Deletion',
              style: TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.bold,
                fontSize: 16,
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'In compliance with Apple App Store Guideline 5.1.1(v), Google Play User Data Policy, and Data Protection Act (Act 843):',
              style: TextStyle(color: Colors.white70, fontSize: 13),
            ),
            const SizedBox(height: 12),
            const Text(
              'To deactivate your SRE operator credentials or delete your personal profile data, email your request to:',
              style: TextStyle(color: Colors.white70, fontSize: 12),
            ),
            const SizedBox(height: 6),
            SelectableText(
              'hello@johnokyere.xyz',
              style: TextStyle(
                color: NpontuColors.goldWarm,
                fontWeight: FontWeight.bold,
                fontFamily: 'monospace',
              ),
            ),
            const SizedBox(height: 10),
            const Text(
              '• SLA: 48hr acknowledgment, 30-day completion.\n'
              '• Profile and tokens are purged.\n'
              '• Historic system audit logs retained per 7-year statutory requirement.',
              style: TextStyle(
                color: Colors.white60,
                fontSize: 11,
                height: 1.4,
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Clipboard.setData(const ClipboardData(text: 'hello@johnokyere.xyz'));
              ScaffoldMessenger.of(ctx).showSnackBar(
                const SnackBar(
                  content: Text('DPO email copied to clipboard.'),
                  duration: Duration(seconds: 2),
                ),
              );
              Navigator.of(ctx).pop();
            },
            child: Text(
              'Copy DPO Email',
              style: TextStyle(color: NpontuColors.goldWarm),
            ),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: NpontuColors.green),
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return DraggableScrollableSheet(
      initialChildSize: 0.88,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      builder: (context, scrollController) {
        return Container(
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF0A140E) : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            border: Border.all(
              color: isDark ? const Color(0xFF1E3A28) : Colors.grey.shade200,
            ),
            boxShadow: const [
              BoxShadow(
                color: Colors.black45,
                blurRadius: 20,
                offset: Offset(0, -4),
              ),
            ],
          ),
          child: Column(
            children: [
              // Grab handle
              Container(
                margin: const EdgeInsets.only(top: 12, bottom: 8),
                width: 44,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.withOpacity(0.4),
                  borderRadius: BorderRadius.circular(2),
                ),
              ),

              // Header
              Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 8,
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: NpontuColors.green.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Icon(
                        Icons.privacy_tip_rounded,
                        color: NpontuColors.green,
                        size: 22,
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Privacy & Data Handling',
                            style: theme.textTheme.titleMedium?.copyWith(
                              fontWeight: FontWeight.bold,
                              letterSpacing: -0.2,
                            ),
                          ),
                          Text(
                            'App Store & Play Store Compliance',
                            style: TextStyle(
                              fontSize: 11,
                              color: isDark
                                  ? Colors.white60
                                  : Colors.grey.shade600,
                              fontFamily: 'monospace',
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.of(context).pop(),
                      tooltip: 'Close',
                    ),
                  ],
                ),
              ),

              const Divider(height: 1),

              // Policy content
              Expanded(
                child: ListView(
                  controller: scrollController,
                  padding: const EdgeInsets.all(20),
                  children: [
                    // Compliance Badge Card
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: NpontuColors.green.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: NpontuColors.green.withOpacity(0.3),
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            Icons.verified_user_rounded,
                            color: NpontuColors.green,
                            size: 22,
                          ),
                          const SizedBox(width: 12),
                          const Expanded(
                            child: Text(
                              'Production Policy OPS-SRE-POL-01\nGoverned by Ghana Data Protection Act 2012 (Act 843)',
                              style: TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w600,
                                height: 1.35,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 18),

                    _buildSectionHeader(
                      '1. Purpose & Scope',
                      Icons.business_center_rounded,
                    ),
                    const Text(
                      'The Opsora SRE Mobile Companion application is strictly designed for internal site reliability engineering, daily activity tracking, shift handovers, and real-time operational communications across telecommunications and payment gateway nodes operated by Opsora SRE.',
                      style: TextStyle(fontSize: 13, height: 1.45),
                    ),

                    const SizedBox(height: 18),

                    _buildSectionHeader(
                      '2. Permissions & Data Usage',
                      Icons.security_rounded,
                    ),
                    _buildBullet(
                      'Local Encrypted Cache',
                      'Checklists, handovers, and active war room chats are securely stored locally via encrypted storage to guarantee zero-latency launches and offline availability during datacenter blackouts. No personal user data is ever sold or shared with third-party data brokers.',
                    ),
                    _buildBullet(
                      'Camera & Photo Library (Optional)',
                      'Only accessed upon your explicit action to attach screenshots, monitoring charts, or log snapshots directly into SRE incident war rooms.',
                    ),
                    _buildBullet(
                      'Push Notifications (Optional)',
                      'Used exclusively for critical operational alerts: P1/P2 production incidents, urgent shift handover requests, and direct @mentions. Zero promotional notifications.',
                    ),
                    _buildBullet(
                      'Telemetry & Performance Probes',
                      'Measures API latency and database probe ping times to diagnose network bottlenecks. Strictly diagnostic, never linked to personal identities.',
                    ),

                    const SizedBox(height: 18),

                    _buildSectionHeader(
                      '3. Third-Party Disclosures & Trackers',
                      Icons.policy_rounded,
                    ),
                    const Text(
                      'Zero commercial advertising trackers, third-party analytics SDKs, or data brokers are integrated into this application. All API communications are strictly encrypted in transit via TLS 1.3 and authenticated using Sanctum Bearer tokens.',
                      style: TextStyle(fontSize: 13, height: 1.45),
                    ),

                    const SizedBox(height: 18),

                    _buildSectionHeader(
                      '4. Account Deletion Rights (Apple 5.1.1(v))',
                      Icons.person_remove_rounded,
                    ),
                    const Text(
                      'You have the statutory right to request deactivation of your account and erasure of personal profile data. Requests are processed within 30 days. To initiate a deletion request, use the button below or email hello@johnokyere.xyz.',
                      style: TextStyle(fontSize: 13, height: 1.45),
                    ),
                    const SizedBox(height: 10),
                    OutlinedButton.icon(
                      onPressed: () => showAccountDeletionDialog(context),
                      icon: Icon(
                        Icons.delete_outline_rounded,
                        color: NpontuColors.danger,
                        size: 18,
                      ),
                      label: Text(
                        'Request Account Deletion',
                        style: TextStyle(
                          color: NpontuColors.danger,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        side: BorderSide(color: NpontuColors.danger),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),

                    const SizedBox(height: 18),

                    _buildSectionHeader(
                      '5. Statutory Retention & Non-Repudiation',
                      Icons.lock_clock_rounded,
                    ),
                    const Text(
                      'In accordance with PCI-DSS v4.0 Requirement 10 and ISO 27001 Control A.8.15, historic immutable audit trail logs and formal shift handover custody records are archived in immutable cold storage for 7 years to satisfy statutory compliance.',
                      style: TextStyle(
                        fontSize: 12,
                        height: 1.4,
                        color: Colors.grey,
                      ),
                    ),

                    const SizedBox(height: 18),

                    _buildSectionHeader(
                      '6. Data Protection Officer',
                      Icons.contact_mail_rounded,
                    ),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.grey.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.grey.withOpacity(0.2)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Opsora SRE Data Protection Office',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Email: hello@johnokyere.xyz',
                            style: TextStyle(
                              fontFamily: 'monospace',
                              fontSize: 11,
                              color: NpontuColors.green,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Accra Digital Centre, Ring Road West, Accra, Ghana',
                            style: TextStyle(
                              fontSize: 11,
                              color: isDark
                                  ? Colors.white60
                                  : Colors.grey.shade600,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSectionHeader(String title, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        children: [
          Icon(icon, size: 16, color: NpontuColors.goldWarm),
          const SizedBox(width: 8),
          Text(
            title,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
          ),
        ],
      ),
    );
  }

  Widget _buildBullet(String title, String description) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            margin: const EdgeInsets.only(top: 4),
            width: 5,
            height: 5,
            decoration: const BoxDecoration(
              color: NpontuColors.gold,
              shape: BoxShape.circle,
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: RichText(
              text: TextSpan(
                style: const TextStyle(
                  fontSize: 12.5,
                  height: 1.4,
                  color: Colors.white70,
                ),
                children: [
                  TextSpan(
                    text: '$title: ',
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  TextSpan(text: description),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
