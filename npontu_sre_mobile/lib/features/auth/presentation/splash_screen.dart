// lib/features/auth/presentation/splash_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/services/cache_service.dart';
import '../../../core/theme/npontu_theme.dart';
import 'auth_controller.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _animController;
  late Animation<double> _scaleAnimation;
  late Animation<double> _fadeAnimation;
  String _statusMessage = 'Initializing SRE runtime...';

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1000),
    );

    _scaleAnimation = CurvedAnimation(
      parent: _animController,
      curve: Curves.easeOutBack,
    );

    _fadeAnimation = CurvedAnimation(
      parent: _animController,
      curve: Curves.easeIn,
    );

    _animController.forward();
    _bootSequence();
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  bool _hasNavigated = false;

  void _navigateToNextScreen() {
    if (!mounted || _hasNavigated) return;
    _hasNavigated = true;

    final authState = ref.read(authControllerProvider);
    final cacheService = ref.read(cacheServiceProvider);
    bool hasSeenOnboarding = false;
    try {
      hasSeenOnboarding = cacheService.hasSeenOnboarding();
    } catch (_) {
      hasSeenOnboarding = false;
    }

    if (authState.isAuthenticated) {
      context.go('/');
    } else if (!hasSeenOnboarding) {
      context.go('/onboarding');
    } else {
      context.go('/login');
    }
  }

  Future<void> _bootSequence() async {
    // Master watchdog: guarantee transition within 3.5 seconds under all conditions
    Future.delayed(const Duration(milliseconds: 3500), () {
      if (mounted && !_hasNavigated) {
        _navigateToNextScreen();
      }
    });

    try {
      // 1. Initialise local offline cache
      await Future.delayed(const Duration(milliseconds: 300));
      if (!mounted || _hasNavigated) return;
      setState(() => _statusMessage = 'Hydrating offline telemetry...');
      try {
        await ref
            .read(cacheServiceProvider)
            .initialise()
            .timeout(const Duration(seconds: 2));
      } catch (e) {
        debugPrint('Cache init skipped or timed out: $e');
      }

      // 2. Check token and credentials
      await Future.delayed(const Duration(milliseconds: 350));
      if (!mounted || _hasNavigated) return;
      setState(() => _statusMessage = 'Verifying security credentials...');
      try {
        await ref
            .read(authControllerProvider.notifier)
            .checkAuthStatus()
            .timeout(const Duration(seconds: 2));
      } catch (e) {
        debugPrint('Auth check skipped or timed out: $e');
      }

      // 3. Complete splash presentation
      await Future.delayed(const Duration(milliseconds: 300));
      if (!mounted || _hasNavigated) return;
      setState(() => _statusMessage = 'Ready.');
      await Future.delayed(const Duration(milliseconds: 150));
    } catch (e) {
      debugPrint('Boot sequence exception: $e');
    } finally {
      if (mounted) {
        _navigateToNextScreen();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF07100B),
      body: Stack(
        children: [
          // Background ambient gradient glow
          Positioned(
            top: -100,
            right: -100,
            child: Container(
              width: 300,
              height: 300,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    NpontuColors.green.withAlpha(60),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),
          Positioned(
            bottom: -80,
            left: -80,
            child: Container(
              width: 260,
              height: 260,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [NpontuColors.gold.withAlpha(40), Colors.transparent],
                ),
              ),
            ),
          ),

          // Center branding and loader
          Center(
            child: FadeTransition(
              opacity: _fadeAnimation,
              child: ScaleTransition(
                scale: _scaleAnimation,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // SRE Shield Crest
                    Container(
                      width: 88,
                      height: 88,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: const LinearGradient(
                          colors: [NpontuColors.green, Color(0xFF0A2B15)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        border: Border.all(
                          color: NpontuColors.gold.withAlpha(180),
                          width: 2.5,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: NpontuColors.green.withAlpha(120),
                            blurRadius: 28,
                            spreadRadius: 4,
                          ),
                        ],
                      ),
                      child: const Center(
                        child: Icon(
                          Icons.shield_rounded,
                          size: 46,
                          color: NpontuColors.gold,
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // App Title
                    const Text(
                      'NPONTU SRE',
                      style: TextStyle(
                        fontFamily: 'Inter',
                        fontSize: 26,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 3.5,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'HIGH RELIABILITY OPERATIONS COCKPIT',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        letterSpacing: 1.5,
                        color: NpontuColors.gold,
                      ),
                    ),
                    const SizedBox(height: 40),

                    // Loading progress
                    SizedBox(
                      width: 140,
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(4),
                        child: const LinearProgressIndicator(
                          backgroundColor: Color(0xFF14261B),
                          valueColor: AlwaysStoppedAnimation<Color>(
                            NpontuColors.gold,
                          ),
                          minHeight: 3,
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),

                    // Status text
                    Text(
                      _statusMessage,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: Colors.white60,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Bottom version tag
          Positioned(
            bottom: 24,
            left: 0,
            right: 0,
            child: Center(
              child: Text(
                'v${AppConstants.appVersion} • Enterprise Edition',
                style: const TextStyle(
                  fontSize: 11,
                  fontFamily: 'JetBrains Mono',
                  color: Colors.white30,
                  letterSpacing: 0.8,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
