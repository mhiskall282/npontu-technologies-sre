// lib/features/health/health_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/api_client.dart';
import '../../core/network/api_client_provider.dart';
import '../../shared/models/system_health_model.dart';

class HealthState {
  final SystemHealthModel? health;
  final Map<String, dynamic> telemetry;
  final bool isLoading;
  final String? errorMessage;

  const HealthState({
    this.health,
    this.telemetry = const {},
    this.isLoading = false,
    this.errorMessage,
  });

  HealthState copyWith({
    SystemHealthModel? health,
    Map<String, dynamic>? telemetry,
    bool? isLoading,
    String? errorMessage,
  }) {
    return HealthState(
      health: health ?? this.health,
      telemetry: telemetry ?? this.telemetry,
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
    );
  }
}

class HealthController extends StateNotifier<HealthState> {
  final ApiClient _apiClient;

  HealthController({required ApiClient apiClient})
    : _apiClient = apiClient,
      super(const HealthState(isLoading: true)) {
    loadHealth();
  }

  Future<void> loadHealth() async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final telemetryRes = await _apiClient.get('/health/telemetry');
      final telemetryData =
          telemetryRes.data['data'] as Map<String, dynamic>? ?? {};

      SystemHealthModel? fullHealth;
      try {
        final diagRes = await _apiClient.get('/health/diagnostics');
        final diagData = diagRes.data['data'] as Map<String, dynamic>? ?? {};
        fullHealth = SystemHealthModel.fromJson(diagData);
      } catch (_) {
        // If user is agent without diagnostics permission, fallback to public health probe
        final probeRes = await _apiClient.get('/health');
        fullHealth = SystemHealthModel.fromJson(
          probeRes.data as Map<String, dynamic>,
        );
      }

      state = state.copyWith(
        health: fullHealth,
        telemetry: telemetryData,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Failed to retrieve system health diagnostics.',
      );
    }
  }
}

final healthControllerProvider =
    StateNotifierProvider<HealthController, HealthState>((ref) {
      final apiClient = ref.watch(apiClientProvider);
      return HealthController(apiClient: apiClient);
    });
