// lib/core/storage/secure_storage.dart

import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../constants/app_constants.dart';

/// Hybrid secure storage service with web and fallback resilience.
///
/// Uses [FlutterSecureStorage] on native mobile/desktop platforms with
/// an automatic 2-second timeout and [SharedPreferences] fallback to
/// prevent hanging during boot sequences on web or unsupported environments.
class SecureStorageService {
  final FlutterSecureStorage _storage;
  SharedPreferences? _fallbackPrefs;

  SecureStorageService({FlutterSecureStorage? storage})
    : _storage =
          storage ??
          const FlutterSecureStorage(
            aOptions: AndroidOptions(),
            iOptions: IOSOptions(
              accessibility: KeychainAccessibility.first_unlock,
            ),
            webOptions: WebOptions(
              dbName: 'npontu_sre_storage',
              publicKey: 'npontu_sre_public_key',
            ),
          );

  Future<SharedPreferences> _getPrefs() async {
    _fallbackPrefs ??= await SharedPreferences.getInstance();
    return _fallbackPrefs!;
  }

  Future<void> saveAuthToken(String token) async {
    if (kIsWeb) {
      final prefs = await _getPrefs();
      await prefs.setString(AppConstants.authTokenKey, token);
      return;
    }
    try {
      await _storage
          .write(key: AppConstants.authTokenKey, value: token)
          .timeout(const Duration(seconds: 2));
    } catch (_) {
      final prefs = await _getPrefs();
      await prefs.setString(AppConstants.authTokenKey, token);
    }
  }

  Future<String?> getAuthToken() async {
    if (kIsWeb) {
      final prefs = await _getPrefs();
      return prefs.getString(AppConstants.authTokenKey);
    }
    try {
      return await _storage
          .read(key: AppConstants.authTokenKey)
          .timeout(const Duration(seconds: 2));
    } catch (_) {
      try {
        final prefs = await _getPrefs();
        return prefs.getString(AppConstants.authTokenKey);
      } catch (_) {
        return null;
      }
    }
  }

  Future<void> deleteAuthToken() async {
    if (kIsWeb) {
      final prefs = await _getPrefs();
      await prefs.remove(AppConstants.authTokenKey);
      return;
    }
    try {
      await _storage
          .delete(key: AppConstants.authTokenKey)
          .timeout(const Duration(seconds: 2));
    } catch (_) {
      final prefs = await _getPrefs();
      await prefs.remove(AppConstants.authTokenKey);
    }
  }

  Future<void> saveActiveWorkspaceId(String workspaceId) async {
    if (kIsWeb) {
      final prefs = await _getPrefs();
      await prefs.setString(AppConstants.activeWorkspaceKey, workspaceId);
      return;
    }
    try {
      await _storage
          .write(key: AppConstants.activeWorkspaceKey, value: workspaceId)
          .timeout(const Duration(seconds: 2));
    } catch (_) {
      final prefs = await _getPrefs();
      await prefs.setString(AppConstants.activeWorkspaceKey, workspaceId);
    }
  }

  Future<String?> getActiveWorkspaceId() async {
    if (kIsWeb) {
      final prefs = await _getPrefs();
      return prefs.getString(AppConstants.activeWorkspaceKey);
    }
    try {
      return await _storage
          .read(key: AppConstants.activeWorkspaceKey)
          .timeout(const Duration(seconds: 2));
    } catch (_) {
      try {
        final prefs = await _getPrefs();
        return prefs.getString(AppConstants.activeWorkspaceKey);
      } catch (_) {
        return null;
      }
    }
  }

  Future<void> deleteActiveWorkspaceId() async {
    if (kIsWeb) {
      final prefs = await _getPrefs();
      await prefs.remove(AppConstants.activeWorkspaceKey);
      return;
    }
    try {
      await _storage
          .delete(key: AppConstants.activeWorkspaceKey)
          .timeout(const Duration(seconds: 2));
    } catch (_) {
      final prefs = await _getPrefs();
      await prefs.remove(AppConstants.activeWorkspaceKey);
    }
  }

  Future<void> saveUserData(Map<String, dynamic> userMap) async {
    final encoded = jsonEncode(userMap);
    if (kIsWeb) {
      final prefs = await _getPrefs();
      await prefs.setString(AppConstants.userDataKey, encoded);
      return;
    }
    try {
      await _storage
          .write(key: AppConstants.userDataKey, value: encoded)
          .timeout(const Duration(seconds: 2));
    } catch (_) {
      final prefs = await _getPrefs();
      await prefs.setString(AppConstants.userDataKey, encoded);
    }
  }

  Future<Map<String, dynamic>?> getUserData() async {
    String? str;
    if (kIsWeb) {
      final prefs = await _getPrefs();
      str = prefs.getString(AppConstants.userDataKey);
    } else {
      try {
        str = await _storage
            .read(key: AppConstants.userDataKey)
            .timeout(const Duration(seconds: 2));
      } catch (_) {
        final prefs = await _getPrefs();
        str = prefs.getString(AppConstants.userDataKey);
      }
    }
    if (str == null) return null;
    try {
      return jsonDecode(str) as Map<String, dynamic>;
    } catch (_) {
      return null;
    }
  }

  Future<void> clearAll() async {
    try {
      await _storage.deleteAll().timeout(const Duration(seconds: 2));
    } catch (_) {}
    try {
      final prefs = await _getPrefs();
      await prefs.remove(AppConstants.authTokenKey);
      await prefs.remove(AppConstants.userDataKey);
    } catch (_) {}
  }

  Future<void> clearAuth() => clearAll();
}
