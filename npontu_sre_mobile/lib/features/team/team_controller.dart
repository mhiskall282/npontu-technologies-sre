// lib/features/team/team_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/api_client.dart';
import '../../core/network/api_client_provider.dart';
import '../../shared/models/user_model.dart';

class TeamState {
  final List<UserModel> members;
  final bool isLoading;
  final String? errorMessage;

  const TeamState({
    this.members = const [],
    this.isLoading = false,
    this.errorMessage,
  });

  TeamState copyWith({
    List<UserModel>? members,
    bool? isLoading,
    String? errorMessage,
  }) {
    return TeamState(
      members: members ?? this.members,
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
    );
  }
}

class TeamController extends StateNotifier<TeamState> {
  final ApiClient _apiClient;

  TeamController({required ApiClient apiClient})
    : _apiClient = apiClient,
      super(const TeamState(isLoading: true)) {
    loadTeam();
  }

  Future<void> loadTeam({String? role, String? search}) async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final queryParams = <String, dynamic>{};
      if (role != null && role.isNotEmpty) queryParams['role'] = role;
      if (search != null && search.isNotEmpty) queryParams['search'] = search;

      final response = await _apiClient.get(
        '/team',
        queryParameters: queryParams,
      );
      final dataList = response.data['data'] as List<dynamic>? ?? [];

      final members = dataList
          .map((e) => UserModel.fromJson(e as Map<String, dynamic>))
          .toList();

      state = state.copyWith(members: members, isLoading: false);
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Failed to load team directory.',
      );
    }
  }
}

final teamControllerProvider = StateNotifierProvider<TeamController, TeamState>(
  (ref) {
    final apiClient = ref.watch(apiClientProvider);
    return TeamController(apiClient: apiClient);
  },
);
