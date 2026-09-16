// lib/features/audit/audit_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/api_client.dart';
import '../../core/network/api_client_provider.dart';
import '../../shared/models/audit_log_model.dart';

class AuditState {
  final List<AuditLogModel> logs;
  final bool isLoading;
  final String? errorMessage;

  const AuditState({
    this.logs = const [],
    this.isLoading = false,
    this.errorMessage,
  });

  AuditState copyWith({
    List<AuditLogModel>? logs,
    bool? isLoading,
    String? errorMessage,
  }) {
    return AuditState(
      logs: logs ?? this.logs,
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
    );
  }
}

class AuditController extends StateNotifier<AuditState> {
  final ApiClient _apiClient;

  AuditController({required ApiClient apiClient})
    : _apiClient = apiClient,
      super(const AuditState(isLoading: true)) {
    loadAuditLogs();
  }

  Future<void> loadAuditLogs({String? event, String? subjectType}) async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final queryParams = <String, dynamic>{};
      if (event != null && event.isNotEmpty) queryParams['event'] = event;
      if (subjectType != null && subjectType.isNotEmpty)
        queryParams['subject_type'] = subjectType;

      final response = await _apiClient.get(
        '/audit-logs',
        queryParameters: queryParams,
      );
      final dataList = response.data['data'] as List<dynamic>? ?? [];

      final logs = dataList
          .map((e) => AuditLogModel.fromJson(e as Map<String, dynamic>))
          .toList();

      state = state.copyWith(logs: logs, isLoading: false);
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Failed to retrieve compliance audit logs.',
      );
    }
  }
}

final auditControllerProvider =
    StateNotifierProvider<AuditController, AuditState>((ref) {
      final apiClient = ref.watch(apiClientProvider);
      return AuditController(apiClient: apiClient);
    });
