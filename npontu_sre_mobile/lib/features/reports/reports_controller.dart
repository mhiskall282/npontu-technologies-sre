// lib/features/reports/reports_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/api_client.dart';
import '../../core/network/api_client_provider.dart';
import '../../shared/models/activity_model.dart';

class ReportsState {
  final List<ActivityLogModel> logs;
  final Map<String, dynamic> charts;
  final String from;
  final String to;
  final String? status;
  final bool isLoading;
  final String? errorMessage;

  const ReportsState({
    this.logs = const [],
    this.charts = const {},
    required this.from,
    required this.to,
    this.status,
    this.isLoading = false,
    this.errorMessage,
  });

  ReportsState copyWith({
    List<ActivityLogModel>? logs,
    Map<String, dynamic>? charts,
    String? from,
    String? to,
    String? status,
    bool? isLoading,
    String? errorMessage,
  }) {
    return ReportsState(
      logs: logs ?? this.logs,
      charts: charts ?? this.charts,
      from: from ?? this.from,
      to: to ?? this.to,
      status: status ?? this.status,
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
    );
  }
}

class ReportsController extends StateNotifier<ReportsState> {
  final ApiClient _apiClient;

  ReportsController({required ApiClient apiClient})
    : _apiClient = apiClient,
      super(
        ReportsState(
          from: DateTime.now()
              .subtract(const Duration(days: 14))
              .toIso8601String()
              .substring(0, 10),
          to: DateTime.now().toIso8601String().substring(0, 10),
          isLoading: true,
        ),
      ) {
    loadReports();
  }

  Future<void> loadReports({String? from, String? to, String? status}) async {
    final activeFrom = from ?? state.from;
    final activeTo = to ?? state.to;
    final activeStatus = status ?? state.status;

    state = state.copyWith(
      from: activeFrom,
      to: activeTo,
      status: activeStatus,
      isLoading: true,
      errorMessage: null,
    );

    try {
      final queryParams = <String, dynamic>{'from': activeFrom, 'to': activeTo};
      if (activeStatus != null && activeStatus.isNotEmpty) {
        queryParams['status'] = activeStatus;
      }

      final response = await _apiClient.get(
        '/reports',
        queryParameters: queryParams,
      );

      final dataList = response.data['data'] as List<dynamic>? ?? [];
      final meta = response.data['meta'] as Map<String, dynamic>? ?? {};
      final charts = meta['charts'] as Map<String, dynamic>? ?? {};

      final logs = dataList
          .map((e) => ActivityLogModel.fromJson(e as Map<String, dynamic>))
          .toList();

      state = state.copyWith(logs: logs, charts: charts, isLoading: false);
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Failed to load operational compliance reports.',
      );
    }
  }
}

final reportsControllerProvider =
    StateNotifierProvider<ReportsController, ReportsState>((ref) {
      final apiClient = ref.watch(apiClientProvider);
      return ReportsController(apiClient: apiClient);
    });
