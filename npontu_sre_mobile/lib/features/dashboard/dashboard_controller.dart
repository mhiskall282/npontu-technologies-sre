// lib/features/dashboard/dashboard_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/api_client.dart';
import '../../core/network/api_client_provider.dart';
import '../../core/services/cache_service.dart';
import '../../core/services/connectivity_service.dart';
import '../../shared/models/activity_model.dart';
import '../../shared/models/shift_handover_model.dart';

class DashboardDataState {
  final String date;
  final String currentShift;
  final int totalChecks;
  final int completedChecks;
  final int pendingChecks;
  final double completionRate;
  final int criticalP1;
  final int highP2;
  final int mediumP3;
  final int lowP4;
  final int totalAssignedToMe;
  final int pendingAssignedToMe;
  final int completedAssignedToMe;
  final List<ActivityModel> personalTasks;
  final int activeIncidentsCount;
  final List<ActivityModel> activeIncidents;
  final ShiftHandoverModel? latestHandover;
  final int unreadMessagesCount;
  final String systemHealthStatus;
  final double dbLatencyMs;
  final String uptimeSla;
  final bool isLoading;
  final bool isOffline;
  final String? errorMessage;

  const DashboardDataState({
    this.date = '',
    this.currentShift = 'morning',
    this.totalChecks = 0,
    this.completedChecks = 0,
    this.pendingChecks = 0,
    this.completionRate = 0.0,
    this.criticalP1 = 0,
    this.highP2 = 0,
    this.mediumP3 = 0,
    this.lowP4 = 0,
    this.totalAssignedToMe = 0,
    this.pendingAssignedToMe = 0,
    this.completedAssignedToMe = 0,
    this.personalTasks = const [],
    this.activeIncidentsCount = 0,
    this.activeIncidents = const [],
    this.latestHandover,
    this.unreadMessagesCount = 0,
    this.systemHealthStatus = 'ok',
    this.dbLatencyMs = 0.0,
    this.uptimeSla = '99.98%',
    this.isLoading = false,
    this.isOffline = false,
    this.errorMessage,
  });

  DashboardDataState copyWith({
    String? date,
    String? currentShift,
    int? totalChecks,
    int? completedChecks,
    int? pendingChecks,
    double? completionRate,
    int? criticalP1,
    int? highP2,
    int? mediumP3,
    int? lowP4,
    int? totalAssignedToMe,
    int? pendingAssignedToMe,
    int? completedAssignedToMe,
    List<ActivityModel>? personalTasks,
    int? activeIncidentsCount,
    List<ActivityModel>? activeIncidents,
    ShiftHandoverModel? latestHandover,
    int? unreadMessagesCount,
    String? systemHealthStatus,
    double? dbLatencyMs,
    String? uptimeSla,
    bool? isLoading,
    bool? isOffline,
    String? errorMessage,
  }) {
    return DashboardDataState(
      date: date ?? this.date,
      currentShift: currentShift ?? this.currentShift,
      totalChecks: totalChecks ?? this.totalChecks,
      completedChecks: completedChecks ?? this.completedChecks,
      pendingChecks: pendingChecks ?? this.pendingChecks,
      completionRate: completionRate ?? this.completionRate,
      criticalP1: criticalP1 ?? this.criticalP1,
      highP2: highP2 ?? this.highP2,
      mediumP3: mediumP3 ?? this.mediumP3,
      lowP4: lowP4 ?? this.lowP4,
      totalAssignedToMe: totalAssignedToMe ?? this.totalAssignedToMe,
      pendingAssignedToMe: pendingAssignedToMe ?? this.pendingAssignedToMe,
      completedAssignedToMe:
          completedAssignedToMe ?? this.completedAssignedToMe,
      personalTasks: personalTasks ?? this.personalTasks,
      activeIncidentsCount: activeIncidentsCount ?? this.activeIncidentsCount,
      activeIncidents: activeIncidents ?? this.activeIncidents,
      latestHandover: latestHandover ?? this.latestHandover,
      unreadMessagesCount: unreadMessagesCount ?? this.unreadMessagesCount,
      systemHealthStatus: systemHealthStatus ?? this.systemHealthStatus,
      dbLatencyMs: dbLatencyMs ?? this.dbLatencyMs,
      uptimeSla: uptimeSla ?? this.uptimeSla,
      isLoading: isLoading ?? this.isLoading,
      isOffline: isOffline ?? this.isOffline,
      errorMessage: errorMessage,
    );
  }
}

class DashboardController extends StateNotifier<DashboardDataState> {
  final ApiClient _apiClient;
  final CacheService _cacheService;
  final Ref _ref;

  DashboardController({
    required ApiClient apiClient,
    required CacheService cacheService,
    required Ref ref,
  }) : _apiClient = apiClient,
       _cacheService = cacheService,
       _ref = ref,
       super(const DashboardDataState(isLoading: true)) {
    _loadInitial();
  }

  void _loadInitial() {
    // 1. Immediately hydrate from offline cache if available
    final cached = _cacheService.getMap(CacheService.keyDashboard);
    if (cached != null) {
      state = _parseData(cached).copyWith(isLoading: false);
    }
    // 2. Fetch fresh from server
    loadDashboard();
  }

  DashboardDataState _parseData(Map<String, dynamic> data) {
    final metrics = data['metrics'] as Map<String, dynamic>? ?? {};
    final personalQueue = data['personal_queue'] as Map<String, dynamic>? ?? {};
    final health = data['system_health'] as Map<String, dynamic>? ?? {};

    final personalTasksList =
        (personalQueue['tasks'] as List<dynamic>?)
            ?.map((e) => ActivityModel.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];

    final incidentsList =
        (data['active_incidents'] as List<dynamic>?)
            ?.map((e) => ActivityModel.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];

    final handoverJson = data['latest_handover'] as Map<String, dynamic>?;

    return DashboardDataState(
      date: data['date'] as String? ?? '',
      currentShift: data['current_shift'] as String? ?? 'morning',
      totalChecks: metrics['total_checks'] as int? ?? 0,
      completedChecks: metrics['completed_checks'] as int? ?? 0,
      pendingChecks: metrics['pending_checks'] as int? ?? 0,
      completionRate: (metrics['completion_rate'] is num)
          ? (metrics['completion_rate'] as num).toDouble()
          : 0.0,
      criticalP1: metrics['critical_p1'] as int? ?? 0,
      highP2: metrics['high_p2'] as int? ?? 0,
      mediumP3: metrics['medium_p3'] as int? ?? 0,
      lowP4: metrics['low_p4'] as int? ?? 0,
      totalAssignedToMe: personalQueue['total_assigned'] as int? ?? 0,
      pendingAssignedToMe: personalQueue['pending_assigned'] as int? ?? 0,
      completedAssignedToMe: personalQueue['completed_assigned'] as int? ?? 0,
      personalTasks: personalTasksList,
      activeIncidentsCount: data['active_incidents_count'] as int? ?? 0,
      activeIncidents: incidentsList,
      latestHandover: handoverJson != null
          ? ShiftHandoverModel.fromJson(handoverJson)
          : null,
      unreadMessagesCount: data['unread_messages_count'] as int? ?? 0,
      systemHealthStatus: health['status'] as String? ?? 'ok',
      dbLatencyMs: (health['db_latency_ms'] is num)
          ? (health['db_latency_ms'] as num).toDouble()
          : 0.0,
      uptimeSla: health['uptime_sla'] as String? ?? '99.98%',
      isLoading: false,
      isOffline: false,
    );
  }

  Future<void> loadDashboard({String? date}) async {
    // Only show full loading spinner if we don't already have data to display
    if (state.date.isEmpty) {
      state = state.copyWith(isLoading: true, errorMessage: null);
    }

    try {
      final response = await _apiClient.get(
        '/dashboard',
        queryParameters: date != null ? {'date': date} : null,
      );

      final data = response.data['data'] as Map<String, dynamic>;
      // Save fresh payload to cache
      await _cacheService.saveMap(CacheService.keyDashboard, data);
      _ref.read(connectivityServiceProvider.notifier).markOnline();

      state = _parseData(data).copyWith(isLoading: false, isOffline: false);
    } catch (e) {
      _ref.read(connectivityServiceProvider.notifier).markOffline();

      if (state.date.isNotEmpty) {
        // We have cached data, keep displaying it and indicate offline mode
        state = state.copyWith(isLoading: false, isOffline: true);
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: 'Failed to load SRE dashboard. Tap retry.',
        );
      }
    }
  }
}

final dashboardControllerProvider =
    StateNotifierProvider<DashboardController, DashboardDataState>((ref) {
      final apiClient = ref.watch(apiClientProvider);
      final cacheService = ref.watch(cacheServiceProvider);
      return DashboardController(
        apiClient: apiClient,
        cacheService: cacheService,
        ref: ref,
      );
    });
