// lib/features/settings/settings_controller.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

// ─── State ─────────────────────────────────────────────────────────────────

class SettingsState {
  const SettingsState({
    this.themeMode = ThemeMode.system,
    this.notificationsEnabled = true,
    this.notifyIncidents = true,
    this.notifyHandovers = true,
    this.notifyAssignments = true,
    this.biometricEnabled = false,
    this.apiBaseUrl = '',
    this.isLoading = false,
  });

  final ThemeMode themeMode;
  final bool notificationsEnabled;
  final bool notifyIncidents;
  final bool notifyHandovers;
  final bool notifyAssignments;
  final bool biometricEnabled;
  final String apiBaseUrl;
  final bool isLoading;

  SettingsState copyWith({
    ThemeMode? themeMode,
    bool? notificationsEnabled,
    bool? notifyIncidents,
    bool? notifyHandovers,
    bool? notifyAssignments,
    bool? biometricEnabled,
    String? apiBaseUrl,
    bool? isLoading,
  }) {
    return SettingsState(
      themeMode: themeMode ?? this.themeMode,
      notificationsEnabled: notificationsEnabled ?? this.notificationsEnabled,
      notifyIncidents: notifyIncidents ?? this.notifyIncidents,
      notifyHandovers: notifyHandovers ?? this.notifyHandovers,
      notifyAssignments: notifyAssignments ?? this.notifyAssignments,
      biometricEnabled: biometricEnabled ?? this.biometricEnabled,
      apiBaseUrl: apiBaseUrl ?? this.apiBaseUrl,
      isLoading: isLoading ?? this.isLoading,
    );
  }
}

// ─── Controller ────────────────────────────────────────────────────────────

class SettingsController extends Notifier<SettingsState> {
  static const _kTheme = 'settings_theme';
  static const _kNotifs = 'settings_notifications';
  static const _kNotifIncidents = 'settings_notify_incidents';
  static const _kNotifHandovers = 'settings_notify_handovers';
  static const _kNotifAssignments = 'settings_notify_assignments';
  static const _kBiometric = 'settings_biometric';
  static const _kApiUrl = 'settings_api_url';

  @override
  SettingsState build() {
    _loadFromPrefs();
    return const SettingsState();
  }

  Future<void> _loadFromPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    final themeIndex = prefs.getInt(_kTheme) ?? ThemeMode.system.index;

    state = state.copyWith(
      themeMode: ThemeMode.values[themeIndex],
      notificationsEnabled: prefs.getBool(_kNotifs) ?? true,
      notifyIncidents: prefs.getBool(_kNotifIncidents) ?? true,
      notifyHandovers: prefs.getBool(_kNotifHandovers) ?? true,
      notifyAssignments: prefs.getBool(_kNotifAssignments) ?? true,
      biometricEnabled: prefs.getBool(_kBiometric) ?? false,
      apiBaseUrl: prefs.getString(_kApiUrl) ?? '',
    );
  }

  // ─── Setters ────────────────────────────────────────────────────────────

  Future<void> setThemeMode(ThemeMode mode) async {
    state = state.copyWith(themeMode: mode);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_kTheme, mode.index);
  }

  Future<void> setNotificationsEnabled(bool value) async {
    state = state.copyWith(notificationsEnabled: value);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_kNotifs, value);
  }

  Future<void> setNotifyIncidents(bool value) async {
    state = state.copyWith(notifyIncidents: value);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_kNotifIncidents, value);
  }

  Future<void> setNotifyHandovers(bool value) async {
    state = state.copyWith(notifyHandovers: value);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_kNotifHandovers, value);
  }

  Future<void> setNotifyAssignments(bool value) async {
    state = state.copyWith(notifyAssignments: value);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_kNotifAssignments, value);
  }

  Future<void> setBiometricEnabled(bool value) async {
    state = state.copyWith(biometricEnabled: value);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_kBiometric, value);
  }

  Future<void> setApiBaseUrl(String url) async {
    state = state.copyWith(apiBaseUrl: url);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kApiUrl, url);
  }
}

final settingsControllerProvider =
    NotifierProvider<SettingsController, SettingsState>(SettingsController.new);

/// Derived provider so [MaterialApp.router] can react to theme changes.
final appThemeModeProvider = Provider<ThemeMode>((ref) {
  return ref.watch(settingsControllerProvider).themeMode;
});
