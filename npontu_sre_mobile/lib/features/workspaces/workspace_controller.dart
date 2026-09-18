// lib/features/workspaces/workspace_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/api_client_provider.dart';
import 'workspace_model.dart';

final activeWorkspaceIdProvider = StateProvider<String?>((ref) => null);

final workspacesControllerProvider =
    AsyncNotifierProvider<WorkspaceController, List<WorkspaceModel>>(
      WorkspaceController.new,
    );

class WorkspaceController extends AsyncNotifier<List<WorkspaceModel>> {
  @override
  Future<List<WorkspaceModel>> build() async {
    // Check if an active workspace is stored
    final storage = ref.read(apiClientProvider).storageService;
    final storedId = await storage.getActiveWorkspaceId();
    if (storedId != null) {
      ref.read(activeWorkspaceIdProvider.notifier).state = storedId;
    }

    return _fetchWorkspaces();
  }

  Future<List<WorkspaceModel>> _fetchWorkspaces() async {
    final client = ref.read(apiClientProvider);
    final response = await client.get<Map<String, dynamic>>('/workspaces');

    final rawList = response.data?['data'] as List<dynamic>? ?? [];
    final list = rawList
        .map((e) => WorkspaceModel.fromJson(e as Map<String, dynamic>))
        .toList();

    // If no active workspace is selected yet, default to first available
    final currentActive = ref.read(activeWorkspaceIdProvider);
    if ((currentActive == null || currentActive.isEmpty) && list.isNotEmpty) {
      final defaultId = list.first.id.toString();
      await ref.read(apiClientProvider).storageService.saveActiveWorkspaceId(defaultId);
      ref.read(activeWorkspaceIdProvider.notifier).state = defaultId;
    }

    return list;
  }

  Future<void> switchWorkspace(WorkspaceModel workspace) async {
    final client = ref.read(apiClientProvider);
    final idStr = workspace.id.toString();

    // Save to local storage so future requests include X-Workspace-Id header
    await client.storageService.saveActiveWorkspaceId(idStr);
    ref.read(activeWorkspaceIdProvider.notifier).state = idStr;

    try {
      await client.post<Map<String, dynamic>>(
        '/workspaces/switch',
        data: {'workspace_id': workspace.id},
      );
    } catch (_) {
      // Offline or network error handled gracefully; header remains cached locally
    }
  }

  Future<WorkspaceModel?> joinByCompanyCode(String companyCode) async {
    final client = ref.read(apiClientProvider);

    final response = await client.post<Map<String, dynamic>>(
      '/organizations/join-by-code',
      data: {'company_code': companyCode.trim().toUpperCase()},
    );

    final data = response.data?['data'] as Map<String, dynamic>?;
    final primaryWs = data?['primary_workspace'] as Map<String, dynamic>?;

    // Refresh workspace list
    final refreshedList = await _fetchWorkspaces();
    state = AsyncData(refreshedList);

    if (primaryWs != null && primaryWs['id'] != null) {
      final newWsId = primaryWs['id'].toString();
      await client.storageService.saveActiveWorkspaceId(newWsId);
      ref.read(activeWorkspaceIdProvider.notifier).state = newWsId;

      return refreshedList.firstWhere(
        (w) => w.id == primaryWs['id'],
        orElse: () => refreshedList.first,
      );
    }

    return null;
  }
}
