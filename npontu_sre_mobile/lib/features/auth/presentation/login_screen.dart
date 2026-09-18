// lib/features/auth/presentation/login_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/config/app_config.dart';
import '../../../core/constants/app_constants.dart';
import '../../../core/network/api_client_provider.dart';
import '../../../core/theme/npontu_theme.dart';
import '../../../shared/widgets/opsora_logo.dart';
import '../../legal/privacy_policy_sheet.dart';
import 'auth_controller.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    final success = await ref
        .read(authControllerProvider.notifier)
        .login(_emailController.text, _passwordController.text);

    if (success && mounted) {
      context.go('/');
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authControllerProvider);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: Form(
                key: _formKey,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Brand Logo
                    Center(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: NpontuColors.green.withAlpha(80),
                              blurRadius: 20,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: const OpsoraIcon(size: 64),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      AppConstants.appName,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 26,
                        fontWeight: FontWeight.w800,
                        color: NpontuColors.green,
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Site Reliability Engineering Mobile Cockpit',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 13,
                        color: isDark
                            ? NpontuColors.textSecondaryDark
                            : NpontuColors.textSecondaryLight,
                      ),
                    ),
                    const SizedBox(height: 12),

                    // Environment & Server Link Selector Pill
                    Center(
                      child: InkWell(
                        onTap: () => _showServerSwitcherDialog(context),
                        borderRadius: BorderRadius.circular(20),
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? NpontuColors.surfaceDark
                                : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(
                              color: isDark
                                  ? Colors.white12
                                  : Colors.grey.shade300,
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.cloud_outlined,
                                size: 14,
                                color: NpontuColors.green,
                              ),
                              const SizedBox(width: 6),
                              Text(
                                _getServerLabel(AppConfig.baseUrl),
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: isDark
                                      ? Colors.white70
                                      : Colors.grey.shade700,
                                ),
                              ),
                              const SizedBox(width: 4),
                              Icon(
                                Icons.arrow_drop_down_rounded,
                                size: 16,
                                color: isDark
                                    ? Colors.white54
                                    : Colors.grey.shade500,
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Error Banner
                    if (authState.errorMessage != null) ...[
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: NpontuColors.danger.withAlpha(25),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: NpontuColors.danger.withAlpha(80),
                          ),
                        ),
                        child: Row(
                          children: [
                            const Icon(
                              Icons.error_outline_rounded,
                              color: NpontuColors.danger,
                              size: 20,
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                authState.errorMessage!,
                                style: const TextStyle(
                                  color: NpontuColors.danger,
                                  fontSize: 13,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],

                    // Email Input
                    TextFormField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      decoration: const InputDecoration(
                        labelText: 'Operator Email',
                        hintText: 'email@email.com',
                        prefixIcon: Icon(Icons.email_outlined),
                      ),
                      validator: (val) {
                        if (val == null || val.trim().isEmpty)
                          return 'Email is required.';
                        if (!val.contains('@'))
                          return 'Enter a valid email address.';
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),

                    // Password Input
                    TextFormField(
                      controller: _passwordController,
                      obscureText: _obscurePassword,
                      decoration: InputDecoration(
                        labelText: 'Password',
                        prefixIcon: const Icon(Icons.lock_outline_rounded),
                        suffixIcon: IconButton(
                          icon: Icon(
                            _obscurePassword
                                ? Icons.visibility_outlined
                                : Icons.visibility_off_outlined,
                          ),
                          onPressed: () => setState(
                            () => _obscurePassword = !_obscurePassword,
                          ),
                        ),
                      ),
                      validator: (val) => (val == null || val.isEmpty)
                          ? 'Password is required.'
                          : null,
                    ),
                    const SizedBox(height: 24),

                    // Submit Button
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: NpontuColors.green,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                      onPressed: authState.isLoading ? null : _handleLogin,
                      child: authState.isLoading
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Text(
                              'Authenticate & Enter Cockpit',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                    ),
                    const SizedBox(height: 32),

                    // Privacy & Legal Footer Row (Play Store & App Store requirement)
                    Wrap(
                      alignment: WrapAlignment.center,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        TextButton(
                          onPressed: () => PrivacyPolicySheet.show(context),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 4,
                            ),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                          child: Text(
                            'Privacy & Data Policy',
                            style: TextStyle(
                              fontSize: 11,
                              color: isDark
                                  ? Colors.white60
                                  : Colors.grey.shade600,
                              decoration: TextDecoration.underline,
                            ),
                          ),
                        ),
                        Text(
                          '•',
                          style: TextStyle(
                            fontSize: 10,
                            color: isDark
                                ? Colors.white38
                                : Colors.grey.shade400,
                          ),
                        ),
                        TextButton(
                          onPressed: () => context.push('/onboarding'),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 6,
                              vertical: 4,
                            ),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                          child: const Text(
                            'Platform Tour',
                            style: TextStyle(
                              fontSize: 11,
                              color: NpontuColors.green,
                              fontWeight: FontWeight.w600,
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
        ),
      ),
    );
  }

  String _getServerLabel(String url) {
    if (url.contains('onrender.com')) return 'Cloud Live';
    if (url.contains('10.0.2.2')) return 'Android Emulator';
    if (url.contains('127.0.0.1') || url.contains('localhost')) {
      return 'Localhost';
    }
    return 'Custom (${Uri.tryParse(url)?.host ?? url})';
  }

  void _showServerSwitcherDialog(BuildContext context) {
    final customController = TextEditingController();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.dns_rounded, color: NpontuColors.green),
                  const SizedBox(width: 8),
                  const Text(
                    'Target Backend Environment',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              _buildServerOption(
                title: 'Render Cloud (Production Live)',
                subtitle: 'https://npontu-support-tracker.onrender.com/api/v1',
                url: 'https://npontu-support-tracker.onrender.com/api/v1',
                ctx: ctx,
              ),
              _buildServerOption(
                title: 'Android Emulator Loopback',
                subtitle: 'http://10.0.2.2:8000/api/v1',
                url: 'http://10.0.2.2:8000/api/v1',
                ctx: ctx,
              ),
              _buildServerOption(
                title: 'iOS Simulator / Desktop (Localhost)',
                subtitle: 'http://127.0.0.1:8000/api/v1',
                url: 'http://127.0.0.1:8000/api/v1',
                ctx: ctx,
              ),
              const Divider(height: 20),
              TextField(
                controller: customController,
                decoration: InputDecoration(
                  labelText: 'Or custom API URL',
                  hintText: 'http://192.168.1.100:8000/api/v1',
                  suffixIcon: IconButton(
                    icon: const Icon(
                      Icons.check_circle_rounded,
                      color: NpontuColors.green,
                    ),
                    onPressed: () {
                      final text = customController.text.trim();
                      if (text.isNotEmpty) {
                        ref.read(apiClientProvider).updateBaseUrl(text);
                        setState(() {});
                        Navigator.pop(ctx);
                      }
                    },
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildServerOption({
    required String title,
    required String subtitle,
    required String url,
    required BuildContext ctx,
  }) {
    final isCurrent = AppConfig.baseUrl == url;
    return ListTile(
      contentPadding: EdgeInsets.zero,
      leading: Icon(
        isCurrent ? Icons.radio_button_checked : Icons.radio_button_off,
        color: isCurrent ? NpontuColors.green : Colors.grey,
      ),
      title: Text(
        title,
        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
      ),
      subtitle: Text(subtitle, style: const TextStyle(fontSize: 11)),
      onTap: () {
        ref.read(apiClientProvider).updateBaseUrl(url);
        setState(() {});
        Navigator.pop(ctx);
      },
    );
  }
}
