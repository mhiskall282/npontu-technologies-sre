// lib/features/activities/activities_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/errors/api_exception.dart';
import '../../core/network/api_client.dart';
import '../../core/network/api_client_provider.dart';
import '../../shared/models/activity_model.dart';

class ActivitiesFilter {
  final String date;
  final String? status; // 'pending' | 'done' | null
  final String? priority; // 'critical' | 'high' | 'medium' | 'low' | null
  final String? assigned; // 'me' | 'pool' | null
  final String? search;

  const ActivitiesFilter({
    required this.date,
    this.status,
    this.priority,
    this.assigned,
    this.search,
  });

  ActivitiesFilter copyWith({
    String? date,
    String? status,
    String? priority,
    String? assigned,
    String? search,
  }) {
    return ActivitiesFilter(
      date: date ?? this.date,
      status: status ?? this.status,
      priority: priority ?? this.priority,
      assigned: assigned ?? this.assigned,
      search: search ?? this.search,
    );
  }

  Map<String, dynamic> toQueryParams() {
    final params = <String, dynamic>{'date': date};
    if (status != null && status!.isNotEmpty) params['status'] = status;
    if (priority != null && priority!.isNotEmpty) params['priority'] = priority;
    if (assigned != null && assigned!.isNotEmpty) params['assigned'] = assigned;
    if (search != null && search!.isNotEmpty) params['search'] = search;
    return params;
  }
}

class ActivitiesState {
  final List<ActivityModel> activities;
  final ActivitiesFilter filter;
  final int total;
  final int pendingCount;
  final int doneCount;
  final bool isLoading;
  final String? errorMessage;
  final String? successMessage;

  const ActivitiesState({
    this.activities = const [],
    required this.filter,
    this.total = 0,
    this.pendingCount = 0,
    this.doneCount = 0,
    this.isLoading = false,
    this.errorMessage,
    this.successMessage,
  });

  ActivitiesState copyWith({
    List<ActivityModel>? activities,
    ActivitiesFilter? filter,
    int? total,
    int? pendingCount,
    int? doneCount,
    bool? isLoading,
    String? errorMessage,
    String? successMessage,
  }) {
    return ActivitiesState(
      activities: activities ?? this.activities,
      filter: filter ?? this.filter,
      total: total ?? this.total,
      pendingCount: pendingCount ?? this.pendingCount,
      doneCount: doneCount ?? this.doneCount,
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      successMessage: successMessage,
    );
  }
}

class ActivitiesController extends StateNotifier<ActivitiesState> {
  final ApiClient _apiClient;

  ActivitiesController({required ApiClient apiClient})
    : _apiClient = apiClient,
      super(
        ActivitiesState(
          filter: ActivitiesFilter(
            date: DateTime.now().toIso8601String().substring(0, 10),
          ),
          isLoading: true,
        ),
      ) {
    loadActivities();
  }

  Future<void> loadActivities([ActivitiesFilter? newFilter]) async {
    final activeFilter = newFilter ?? state.filter;
    state = state.copyWith(
      filter: activeFilter,
      isLoading: true,
      errorMessage: null,
    );

    try {
      final response = await _apiClient.get(
        '/activities',
        queryParameters: activeFilter.toQueryParams(),
      );

      final dataList = response.data['data'] as List<dynamic>? ?? [];
      final meta = response.data['meta'] as Map<String, dynamic>? ?? {};

      final activities = dataList
          .map((e) => ActivityModel.fromJson(e as Map<String, dynamic>))
          .toList();

      state = state.copyWith(
        activities: activities,
        total: meta['total'] as int? ?? activities.length,
        pendingCount:
            meta['pending_count'] as int? ??
            activities.where((a) => a.isPending).count(),
        doneCount:
            meta['done_count'] as int? ??
            activities.where((a) => a.isDone).count(),
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Failed to load operational checks. Please retry.',
      );
    }
  }

  void setStatusFilter(String? status) {
    loadActivities(state.filter.copyWith(status: status));
  }

  void setPriorityFilter(String? priority) {
    loadActivities(state.filter.copyWith(priority: priority));
  }

  void setAssignedFilter(String? assigned) {
    loadActivities(state.filter.copyWith(assigned: assigned));
  }

  void setSearchFilter(String? search) {
    loadActivities(state.filter.copyWith(search: search));
  }

  void setDateFilter(String date) {
    loadActivities(state.filter.copyWith(date: date));
  }

  Future<bool> updateActivityStatus({
    required int activityId,
    required String status,
    String? remark,
    String? incidentTicket,
    bool isEscalated = false,
  }) async {
    try {
      await _apiClient.post(
        '/activities/$activityId/status',
        data: {
          'status': status,
          'remark': remark,
          'date': state.filter.date,
          'incident_ticket': incidentTicket,
          'is_escalated': isEscalated,
        },
      );

      await loadActivities();
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(errorMessage: e.firstErrorMessage);
      return false;
    } catch (_) {
      state = state.copyWith(errorMessage: 'Failed to update check status.');
      return false;
    }
  }

  Future<bool> createActivity(Map<String, dynamic> payload) async {
    try {
      await _apiClient.post('/activities', data: payload);
      await loadActivities();
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(errorMessage: e.firstErrorMessage);
      return false;
    } catch (_) {
      state = state.copyWith(
        errorMessage: 'Failed to create operational check.',
      );
      return false;
    }
  }

  Future<bool> bulkAssign(List<int> activityIds, int? assignedTo) async {
    try {
      await _apiClient.post(
        '/activities/bulk-assign',
        data: {'activity_ids': activityIds, 'assigned_to': assignedTo},
      );

      await loadActivities();
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(errorMessage: e.firstErrorMessage);
      return false;
    } catch (_) {
      state = state.copyWith(
        errorMessage: 'Failed to delegate selected checks.',
      );
      return false;
    }
  }

  Future<bool> updateStatus({
    required int activityId,
    required String status,
    String? remark,
    String? incidentTicket,
    bool isEscalated = false,
  }) => updateActivityStatus(
    activityId: activityId,
    status: status,
    remark: remark,
    incidentTicket: incidentTicket,
    isEscalated: isEscalated,
  );

  Future<bool> updateActivity(int id, Map<String, dynamic> payload) async {
    try {
      await _apiClient.put('/activities/$id', data: payload);
      await loadActivities();
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(errorMessage: e.firstErrorMessage);
      return false;
    } catch (_) {
      state = state.copyWith(errorMessage: 'Failed to update activity.');
      return false;
    }
  }

  Future<bool> deleteActivity(int id) async {
    try {
      await _apiClient.delete('/activities/$id');
      await loadActivities();
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(errorMessage: e.firstErrorMessage);
      return false;
    } catch (_) {
      state = state.copyWith(errorMessage: 'Failed to delete activity.');
      return false;
    }
  }
}

final activitiesControllerProvider =
    StateNotifierProvider<ActivitiesController, ActivitiesState>((ref) {
      final apiClient = ref.watch(apiClientProvider);
      return ActivitiesController(apiClient: apiClient);
    });

extension IterableCount<T> on Iterable<T> {
  int count([bool Function(T element)? predicate]) {
    if (predicate == null) return length;
    var count = 0;
    for (final element in this) {
      if (predicate(element)) count++;
    }
    return count;
  }
}
