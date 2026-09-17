// lib/core/services/cache_service.dart

import 'dart:convert';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Local offline cache service using SharedPreferences for fast startup and offline fallback.
class CacheService {
  static const String _syncTimeKey = 'sre_last_sync_timestamp';
  static const String keyDashboard = 'sre_cache_dashboard';
  static const String keyActivities = 'sre_cache_activities';
  static const String keyHandovers = 'sre_cache_handovers';
  static const String keyTeam = 'sre_cache_team';
  static const String keyProfile = 'sre_cache_profile';
  static const String keyHasSeenOnboarding = 'sre_has_seen_onboarding';

  SharedPreferences? _prefs;

  Future<void> initialise() async {
    _prefs ??= await SharedPreferences.getInstance();
  }

  bool hasSeenOnboarding() {
    return _prefs?.getBool(keyHasSeenOnboarding) ?? false;
  }

  Future<void> setHasSeenOnboarding(bool value) async {
    await _requirePrefs.setBool(keyHasSeenOnboarding, value);
  }

  SharedPreferences get _requirePrefs {
    if (_prefs == null) {
      throw StateError(
        'CacheService not initialised. Call initialise() first.',
      );
    }
    return _prefs!;
  }

  Future<void> saveMap(String key, Map<String, dynamic> data) async {
    final jsonStr = jsonEncode(data);
    await _requirePrefs.setString(key, jsonStr);
    await _updateSyncTime();
  }

  Map<String, dynamic>? getMap(String key) {
    final str = _prefs?.getString(key);
    if (str == null || str.isEmpty) return null;
    try {
      return jsonDecode(str) as Map<String, dynamic>?;
    } catch (_) {
      return null;
    }
  }

  Future<void> saveList(String key, List<dynamic> data) async {
    final jsonStr = jsonEncode(data);
    await _requirePrefs.setString(key, jsonStr);
    await _updateSyncTime();
  }

  List<dynamic>? getList(String key) {
    final str = _prefs?.getString(key);
    if (str == null || str.isEmpty) return null;
    try {
      return jsonDecode(str) as List<dynamic>?;
    } catch (_) {
      return null;
    }
  }

  Future<void> remove(String key) async {
    await _prefs?.remove(key);
  }

  Future<void> clearAllCache() async {
    final keys =
        _prefs?.getKeys().where((k) => k.startsWith('sre_cache_')).toList() ??
        [];
    for (final k in keys) {
      await _prefs?.remove(k);
    }
    await _prefs?.remove(_syncTimeKey);
  }

  int getCachedItemCount() {
    return _prefs?.getKeys().where((k) => k.startsWith('sre_cache_')).length ??
        0;
  }

  String? getLastSyncTime() {
    return _prefs?.getString(_syncTimeKey);
  }

  Future<void> _updateSyncTime() async {
    await _requirePrefs.setString(
      _syncTimeKey,
      DateTime.now().toIso8601String(),
    );
  }
}

final cacheServiceProvider = Provider<CacheService>((ref) {
  return CacheService();
});
