// lib/features/handovers/handovers_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/errors/api_exception.dart';
import '../../core/network/api_client.dart';
import '../../core/network/api_client_provider.dart';
import '../../shared/models/shift_handover_model.dart';

class HandoversState {
  final List<ShiftHandoverModel> handovers;
  final Map<String, dynamic> metrics;
  final bool isLoading;
  final String? errorMessage;
  final String? successMessage;

  const HandoversState({
    this.handovers = const [],
    this.metrics = const {},
    this.isLoading = false,
    this.errorMessage,
    this.successMessage,
  });

  HandoversState copyWith({
    List<ShiftHandoverModel>? handovers,
    Map<String, dynamic>? metrics,
    bool? isLoading,
    String? errorMessage,
    String? successMessage,
  }) {
    return HandoversState(
      handovers: handovers ?? this.handovers,
      metrics: metrics ?? this.metrics,
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      successMessage: successMessage,
    );
  }
}

class HandoversController extends StateNotifier<HandoversState> {
  final ApiClient _apiClient;

  HandoversController({required ApiClient apiClient})
    : _apiClient = apiClient,
      super(const HandoversState(isLoading: true)) {
    loadHandovers();
  }

  Future<void> loadHandovers({String? shift, String? status}) async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final queryParams = <String, dynamic>{};
      if (shift != null && shift.isNotEmpty) queryParams['shift'] = shift;
      if (status != null && status.isNotEmpty) queryParams['status'] = status;

      final response = await _apiClient.get(
        '/handovers',
        queryParameters: queryParams,
      );

      final dataList = response.data['data'] as List<dynamic>? ?? [];
      final meta = response.data['meta'] as Map<String, dynamic>? ?? {};
      final metrics = meta['metrics'] as Map<String, dynamic>? ?? {};

      final handovers = dataList
          .map((e) => ShiftHandoverModel.fromJson(e as Map<String, dynamic>))
          .toList();

      state = state.copyWith(
        handovers: handovers,
        metrics: metrics,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Failed to load shift handovers.',
      );
    }
  }

  Future<bool> createHandover(Map<String, dynamic> payload) async {
    try {
      await _apiClient.post('/handovers', data: payload);
      await loadHandovers();
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(errorMessage: e.firstErrorMessage);
      return false;
    } catch (_) {
      state = state.copyWith(
        errorMessage: 'Failed to sign off shift handover briefing.',
      );
      return false;
    }
  }

  Future<bool> acceptHandover(int handoverId, String? remarks) async {
    try {
      await _apiClient.post(
        '/handovers/$handoverId/accept',
        data: {'remarks': remarks},
      );
      await loadHandovers();
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(errorMessage: e.firstErrorMessage);
      return false;
    } catch (_) {
      state = state.copyWith(errorMessage: 'Failed to accept shift handover.');
      return false;
    }
  }
}

final handoversControllerProvider =
    StateNotifierProvider<HandoversController, HandoversState>((ref) {
      final apiClient = ref.watch(apiClientProvider);
      return HandoversController(apiClient: apiClient);
    });
