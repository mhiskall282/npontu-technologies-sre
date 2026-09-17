// lib/features/onboarding/onboarding_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/services/cache_service.dart';
import '../../core/theme/npontu_theme.dart';
import '../auth/presentation/auth_controller.dart';
import '../legal/privacy_policy_sheet.dart';

/// Interactive, animated introduction onboarding screen showcasing
/// 24/7 shift operations, zero-loss handovers, war rooms, and offline sync.
class OnboardingScreen extends ConsumerStatefulWidget {
  const OnboardingScreen({super.key});

  @override
  ConsumerState<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends ConsumerState<OnboardingScreen> {
  final PageController _pageController = PageController();
  int _currentPage = 0;

  final List<_OnboardingItem> _items = const [
    _OnboardingItem(
      badge: '24/7 SRE OPERATIONS',
      title: 'Real-Time Shift Execution',
      description: 'Track and verify mission-critical operational checks across Morning, Afternoon, and Night shifts. Monitor SLA target windows and log immutable checkoff events in seconds.',
      icon: Icons.checklist_rtl_rounded,
      primaryColor: NpontuColors.green,
      accentColor: NpontuColors.gold,
    ),
    _OnboardingItem(
      badge: 'SHIFT CONTINUITY',
      title: 'Zero-Loss Dual Handovers',
      description: 'Eliminate dropped context with structured two-way handover protocols. Outstanding tasks and incident alerts carry forward with digital sign-offs from outgoing and incoming leads.',
      icon: Icons.swap_horizontal_circle_rounded,
      primaryColor: NpontuColors.gold,
      accentColor: NpontuColors.green,
    ),
    _OnboardingItem(
      badge: 'INCIDENT WAR ROOMS',
      title: 'Live Comms & Instant Dispatch',
      description: 'Collaborate directly with standby engineers in real-time chat channels. Escalate P1/P2 incidents, attach tracking tickets, and trigger instant security notifications.',
      icon: Icons.forum_rounded,
      primaryColor: Color(0xFF2563EB),
      accentColor: NpontuColors.gold,
    ),
    _OnboardingItem(
      badge: 'OFFLINE-FIRST RESILIENCE',
      title: 'Reliable Everywhere',
      description: 'Perform operational duties even without active internet coverage. Shift data is securely stored locally on your device and synchronizes automatically upon reconnection.',
      icon: Icons.cloud_sync_rounded,
      primaryColor: Color(0xFF059669),
      accentColor: NpontuColors.gold,
    ),
  ];

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _completeOnboarding() async {
    final cache = ref.read(cacheServiceProvider);
    await cache.setHasSeenOnboarding(true);

    if (!mounted) return;
    final authState = ref.read(authControllerProvider);
    if (authState.isAuthenticated) {
      context.go('/');
    } else {
      context.go('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final isLastPage = _currentPage == _items.length - 1;

    return Scaffold(
      backgroundColor: isDark ? NpontuColors.surfaceDark : Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            // Top Navigation Bar (Brand + Skip)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: NpontuColors.green,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                            color: NpontuColors.gold,
                            width: 1.5,
                          ),
                        ),
                        child: const Center(
                          child: Text(
                            'N',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w900,
                              fontSize: 18,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'NPONTU SRE',
                            style: TextStyle(
                              fontWeight: FontWeight.w900,
                              fontSize: 13,
                              letterSpacing: 1.2,
                              color: NpontuColors.green,
                            ),
                          ),
                          Text(
                            'Support Activity Tracker',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w500,
                              color: isDark
                                  ? Colors.grey
                                  : const Color(0xFF6B7280),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  if (!isLastPage)
                    TextButton(
                      onPressed: _completeOnboarding,
                      child: Text(
                        'Skip',
                        style: TextStyle(
                          color: isDark ? Colors.grey : const Color(0xFF4B5563),
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    )
                  else
                    const SizedBox(width: 48),
                ],
              ),
            ),

            // Page View Carousel
            Expanded(
              child: PageView.builder(
                controller: _pageController,
                itemCount: _items.length,
                onPageChanged: (idx) => setState(() => _currentPage = idx),
                itemBuilder: (context, index) {
                  final item = _items[index];
                  return _buildPage(context, item, isDark);
                },
              ),
            ),

            // Bottom Indicator & Action Buttons
            Padding(
              padding: const EdgeInsets.fromLTRB(24, 16, 24, 28),
              child: Column(
                children: [
                  // Animated Dots Indicator
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(_items.length, (index) {
                      final isActive = index == _currentPage;
                      return AnimatedContainer(
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeOutCubic,
                        margin: const EdgeInsets.symmetric(horizontal: 4),
                        width: isActive ? 28 : 8,
                        height: 8,
                        decoration: BoxDecoration(
                          color: isActive
                              ? NpontuColors.green
                              : (isDark
                                    ? const Color(0xFF374151)
                                    : const Color(0xFFD1D5DB)),
                          borderRadius: BorderRadius.circular(4),
                          boxShadow: isActive
                              ? [
                                  BoxShadow(
                                    color: NpontuColors.green.withAlpha(80),
                                    blurRadius: 6,
                                  ),
                                ]
                              : null,
                        ),
                      );
                    }),
                  ),
                  const SizedBox(height: 28),

                  // Action Buttons Row
                  Row(
                    children: [
                      if (_currentPage > 0) ...[
                        OutlinedButton(
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 14,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: () {
                            _pageController.previousPage(
                              duration: const Duration(milliseconds: 300),
                              curve: Curves.easeInOut,
                            );
                          },
                          child: const Icon(Icons.arrow_back_rounded),
                        ),
                        const SizedBox(width: 12),
                      ],
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: NpontuColors.green,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 3,
                            shadowColor: NpontuColors.green.withAlpha(100),
                          ),
                          onPressed: () {
                            if (isLastPage) {
                              _completeOnboarding();
                            } else {
                              _pageController.nextPage(
                                duration: const Duration(milliseconds: 300),
                                curve: Curves.easeInOut,
                              );
                            }
                          },
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                isLastPage
                                    ? 'Access SRE Platform'
                                    : 'Next Feature',
                                style: const TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 0.3,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Icon(
                                isLastPage
                                    ? Icons.rocket_launch_rounded
                                    : Icons.arrow_forward_rounded,
                                size: 18,
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 16),

                  // Statutory App Store / Play Store Privacy Assurance
                  InkWell(
                    onTap: () => PrivacyPolicySheet.show(context),
                    borderRadius: BorderRadius.circular(6),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      child: Text(
                        'By continuing, you agree to Npontu SRE Operational Policies & Privacy Terms.',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark ? Colors.white38 : Colors.grey.shade500,
                          decoration: TextDecoration.underline,
                        ),
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

  Widget _buildPage(BuildContext context, _OnboardingItem item, bool isDark) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 28),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          // Animated Graphic Container with Gradient Glow
          Container(
            width: 180,
            height: 180,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [
                  item.primaryColor.withAlpha(40),
                  item.primaryColor.withAlpha(10),
                  Colors.transparent,
                ],
                stops: const [0.3, 0.7, 1.0],
              ),
            ),
            child: Center(
              child: Container(
                width: 110,
                height: 110,
                decoration: BoxDecoration(
                  color: isDark ? NpontuColors.surfaceMid : Colors.white,
                  shape: BoxShape.circle,
                  border: Border.all(color: item.accentColor, width: 2.5),
                  boxShadow: [
                    BoxShadow(
                      color: item.primaryColor.withAlpha(60),
                      blurRadius: 24,
                      spreadRadius: 4,
                    ),
                  ],
                ),
                child: Icon(item.icon, size: 52, color: item.primaryColor),
              ),
            ),
          ),
          const SizedBox(height: 32),

          // Category Badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(
              color: item.primaryColor.withAlpha(20),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: item.primaryColor.withAlpha(60)),
            ),
            child: Text(
              item.badge,
              style: TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w900,
                letterSpacing: 1.0,
                color: item.primaryColor,
              ),
            ),
          ),
          const SizedBox(height: 14),

          // Title
          Text(
            item.title,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w900,
              height: 1.25,
            ),
          ),
          const SizedBox(height: 14),

          // Description
          Text(
            item.description,
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 14,
              height: 1.5,
              color: isDark ? Colors.white70 : const Color(0xFF4B5563),
            ),
          ),
        ],
      ),
    );
  }
}

class _OnboardingItem {
  final String badge;
  final String title;
  final String description;
  final IconData icon;
  final Color primaryColor;
  final Color accentColor;

  const _OnboardingItem({
    required this.badge,
    required this.title,
    required this.description,
    required this.icon,
    required this.primaryColor,
    required this.accentColor,
  });
}
