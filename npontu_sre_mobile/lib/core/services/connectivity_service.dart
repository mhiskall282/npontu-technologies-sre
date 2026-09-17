// lib/core/services/connectivity_service.dart

import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../network/api_client_provider.dart';

class ConnectivityState {
  final bool isOnline;
  final bool isSyncing;
  final DateTime? lastChecked;

  const ConnectivityState({
    this.isOnline = true,
    this.isSyncing = false,
    this.lastChecked,
  });

  ConnectivityState copyWith({
    bool? isOnline,
    bool? isSyncing,
    DateTime? lastChecked,
  }) {
    return ConnectivityState(
      isOnline: isOnline ?? this.isOnline,
      isSyncing: isSyncing ?? this.isSyncing,
      lastChecked: lastChecked ?? this.lastChecked,
    );
  }
}

class ConnectivityService extends StateNotifier<ConnectivityState> {
  final Ref _ref;
  Timer? _probeTimer;

  ConnectivityService(this._ref) : super(const ConnectivityState()) {
    // Check health probe periodically every 30 seconds
    _probeTimer = Timer.periodic(
      const Duration(seconds: 30),
      (_) => checkConnectivity(),
    );
  }

  void markOffline() {
    if (state.isOnline) {
      state = state.copyWith(isOnline: false, lastChecked: DateTime.now());
    }
  }

  void markOnline() {
    if (!state.isOnline) {
      state = state.copyWith(isOnline: true, lastChecked: DateTime.now());
    }
  }

  Future<bool> checkConnectivity() async {
    try {
      final client = _ref.read(apiClientProvider);
      final response = await client.get('/health');
      final isUp = response.statusCode == 200;
      state = state.copyWith(isOnline: isUp, lastChecked: DateTime.now());
      return isUp;
    } catch (_) {
      state = state.copyWith(isOnline: false, lastChecked: DateTime.now());
      return false;
    }
  }

  @override
  void dispose() {
    _probeTimer?.cancel();
    super.dispose();
  }
}

final connectivityServiceProvider =
    StateNotifierProvider<ConnectivityService, ConnectivityState>((ref) {
      return ConnectivityService(ref);
    });
