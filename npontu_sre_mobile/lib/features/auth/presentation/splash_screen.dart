// lib/features/auth/presentation/splash_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/constants/app_constants.dart';
import '../../../core/theme/npontu_theme.dart';
import '../../../core/services/cache_service.dart';
import '../../../shared/widgets/opsora_logo.dart';
import 'auth_controller.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen>
    with TickerProviderStateMixin {
  late AnimationController _animController;
  late AnimationController _pulseController;
  late Animation<Offset> _slideAnimation;
  late Animation<double> _scaleAnimation;
  late Animation<double> _fadeAnimation;
  late Animation<double> _pulseAnimation;

  String _statusMessage = 'Initializing Opsora SRE runtime...';
  double _progressValue = 0.2;

  @override
  void initState() {
    super.initState();

    // 1. Entrance animation: slide in, scale, and fade
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.20),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _animController,
      curve: Curves.easeOutCubic,
    ));

    _scaleAnimation = Tween<double>(
      begin: 0.88,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _animController,
      curve: Curves.easeOutCubic,
    ));

    _fadeAnimation = CurvedAnimation(
      parent: _animController,
      curve: Curves.easeIn,
    );

    // 2. Ambient breathing pulse for the Opsora logo emblem
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1800),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(
      begin: 0.95,
      end: 1.06,
    ).animate(CurvedAnimation(
      parent: _pulseController,
      curve: Curves.easeInOutSine,
    ));

    _animController.forward();
    _bootSequence();
  }

  @override
  void dispose() {
    _animController.dispose();
    _pulseController.dispose();
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
      // Step 1: Initialise local offline cache
      await Future.delayed(const Duration(milliseconds: 300));
      if (!mounted || _hasNavigated) return;
      setState(() {
        _statusMessage = 'Hydrating offline telemetry nodes...';
        _progressValue = 0.55;
      });
      try {
        await ref
            .read(cacheServiceProvider)
            .initialise()
            .timeout(const Duration(seconds: 2));
      } catch (e) {
        debugPrint('Cache init skipped or timed out: $e');
      }

      // Step 2: Check token and security credentials
      await Future.delayed(const Duration(milliseconds: 350));
      if (!mounted || _hasNavigated) return;
      setState(() {
        _statusMessage = 'Verifying security credentials & session...';
        _progressValue = 0.85;
      });
      try {
        await ref
            .read(authControllerProvider.notifier)
            .checkAuthStatus()
            .timeout(const Duration(seconds: 2));
      } catch (e) {
        debugPrint('Auth check skipped or timed out: $e');
      }

      // Step 3: Complete splash presentation
      await Future.delayed(const Duration(milliseconds: 300));
      if (!mounted || _hasNavigated) return;
      setState(() {
        _statusMessage = 'Opsora SRE Ready.';
        _progressValue = 1.0;
      });
      await Future.delayed(const Duration(milliseconds: 180));
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
            top: -120,
            right: -120,
            child: Container(
              width: 340,
              height: 340,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    NpontuColors.green.withAlpha(70),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),
          Positioned(
            bottom: -100,
            left: -100,
            child: Container(
              width: 300,
              height: 300,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    NpontuColors.gold.withAlpha(45),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),

          // Center branding, slide-in animation & smooth loading
          Center(
            child: SingleChildScrollView(
              physics: const NeverScrollableScrollPhysics(),
              child: SlideTransition(
                position: _slideAnimation,
                child: FadeTransition(
                  opacity: _fadeAnimation,
                  child: ScaleTransition(
                    scale: _scaleAnimation,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Animated pulsing emblem halo
                        AnimatedBuilder(
                          animation: _pulseAnimation,
                          builder: (context, child) {
                            return Transform.scale(
                              scale: _pulseAnimation.value,
                              child: child,
                            );
                          },
                          child: Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: NpontuColors.green.withAlpha(140),
                                  blurRadius: 36,
                                  spreadRadius: 6,
                                ),
                                BoxShadow(
                                  color: NpontuColors.gold.withAlpha(50),
                                  blurRadius: 18,
                                  spreadRadius: 1,
                                ),
                              ],
                            ),
                            child: const OpsoraIcon(size: 84),
                          ),
                        ),
                        const SizedBox(height: 26),

                        // Opsora SRE Title
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Text(
                              'OPSORA',
                              style: TextStyle(
                                fontFamily: 'Inter',
                                fontSize: 30,
                                fontWeight: FontWeight.w900,
                                letterSpacing: 3.5,
                                color: Colors.white,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 7,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: NpontuColors.gold.withAlpha(35),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(
                                  color: NpontuColors.gold.withAlpha(160),
                                  width: 1.2,
                                ),
                              ),
                              child: const Text(
                                'SRE',
                                style: TextStyle(
                                  fontFamily: 'monospace',
                                  fontSize: 16,
                                  fontWeight: FontWeight.w900,
                                  color: NpontuColors.gold,
                                  letterSpacing: 1.5,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),

                        // Subtitle
                        const Text(
                          'MISSION-CRITICAL RELIABILITY COCKPIT',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                            letterSpacing: 2.0,
                            color: NpontuColors.greenLight,
                          ),
                        ),
                        const SizedBox(height: 44),

                        // Smooth animated loading indicator
                        SizedBox(
                          width: 170,
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(6),
                            child: TweenAnimationBuilder<double>(
                              tween: Tween<double>(begin: 0.1, end: _progressValue),
                              duration: const Duration(milliseconds: 350),
                              curve: Curves.easeOutCubic,
                              builder: (context, value, _) {
                                return LinearProgressIndicator(
                                  value: value,
                                  backgroundColor: const Color(0xFF14261B),
                                  valueColor: const AlwaysStoppedAnimation<Color>(
                                    NpontuColors.gold,
                                  ),
                                  minHeight: 4,
                                );
                              },
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Status message with subtle transition
                        AnimatedSwitcher(
                          duration: const Duration(milliseconds: 250),
                          child: Text(
                            _statusMessage,
                            key: ValueKey<String>(_statusMessage),
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                              color: Colors.white70,
                              letterSpacing: 0.4,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),

          // Bottom version tag & enterprise signature
          Positioned(
            bottom: 24,
            left: 0,
            right: 0,
            child: Center(
              child: Text(
                'Opsora SRE v${AppConstants.appVersion} • Enterprise Edition',
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
