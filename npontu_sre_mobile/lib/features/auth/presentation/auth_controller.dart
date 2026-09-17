// lib/features/auth/presentation/auth_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/errors/api_exception.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_client_provider.dart';
import '../../../core/storage/secure_storage.dart';
import '../../../shared/models/user_model.dart';

class AuthState {
  final UserModel? user;
  final bool isLoading;
  final String? errorMessage;
  final bool isAuthenticated;

  const AuthState({
    this.user,
    this.isLoading = false,
    this.errorMessage,
    this.isAuthenticated = false,
  });

  AuthState copyWith({
    UserModel? user,
    bool? isLoading,
    String? errorMessage,
    bool? isAuthenticated,
  }) {
    return AuthState(
      user: user ?? this.user,
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
    );
  }
}

class AuthController extends StateNotifier<AuthState> {
  final ApiClient _apiClient;
  final SecureStorageService _storage;

  bool _isChecking = false;

  AuthController({
    required ApiClient apiClient,
    required SecureStorageService storage,
  }) : _apiClient = apiClient,
       _storage = storage,
       super(const AuthState(isLoading: false));

  Future<void> checkAuthStatus() async {
    if (_isChecking) return;
    _isChecking = true;
    try {
      final token = await _storage.getAuthToken();
      if (token == null || token.isEmpty) {
        state = const AuthState(isAuthenticated: false, isLoading: false);
        return;
      }

      // Try fetching current profile from /api/v1/me with a reasonable timeout
      final response = await _apiClient
          .get('/me')
          .timeout(const Duration(seconds: 10));
      final data = response.data['data'] as Map<String, dynamic>;
      final user = UserModel.fromJson(data);

      await _storage.saveUserData(user.toJson());

      state = AuthState(user: user, isAuthenticated: true, isLoading: false);
    } on UnauthorizedException {
      // Token is invalid/expired — clear stored credentials and prompt login
      await _storage.clearAuth();
      state = const AuthState(isAuthenticated: false, isLoading: false);
    } catch (_) {
      // If network offline or unreachable, check if cached user data exists
      final cachedUser = await _storage.getUserData();
      if (cachedUser != null) {
        state = AuthState(
          user: UserModel.fromJson(cachedUser),
          isAuthenticated: true,
          isLoading: false,
        );
      } else {
        state = const AuthState(isAuthenticated: false, isLoading: false);
      }
    } finally {
      _isChecking = false;
    }
  }

  Future<bool> login(
    String email,
    String password, {
    String? deviceName,
  }) async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final response = await _apiClient.post(
        '/auth/login',
        data: {
          'email': email.trim(),
          'password': password,
          'device_name': deviceName ?? 'Npontu Mobile SRE',
        },
      );

      final responseData = response.data['data'] as Map<String, dynamic>;
      final token = responseData['token'] as String;
      final userMap = responseData['user'] as Map<String, dynamic>;
      final user = UserModel.fromJson(userMap);

      await _storage.saveAuthToken(token);
      await _storage.saveUserData(user.toJson());

      state = AuthState(user: user, isAuthenticated: true, isLoading: false);
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.firstErrorMessage,
      );
      return false;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Authentication failed. Please check your credentials.',
      );
      return false;
    }
  }

  Future<bool> updateProfile({
    String? name,
    String? department,
    String? designation,
    String? phone,
  }) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final payload = <String, dynamic>{};
      if (name != null) payload['name'] = name;
      if (department != null) payload['department'] = department;
      if (designation != null) payload['designation'] = designation;
      if (phone != null) payload['phone'] = phone;

      final response = await _apiClient.put('/me', data: payload);
      final data = response.data['data'] as Map<String, dynamic>;
      final user = UserModel.fromJson(data);
      await _storage.saveUserData(user.toJson());
      state = state.copyWith(user: user, isLoading: false);
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: e.firstErrorMessage,
      );
      return false;
    } catch (_) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Failed to update profile information.',
      );
      return false;
    }
  }

  Future<void> logout() async {
    state = state.copyWith(isLoading: true);
    try {
      await _apiClient.post('/auth/logout');
    } catch (_) {
      // Even if network fails, revoke local credentials
    } finally {
      await _storage.clearAll();
      state = const AuthState(isAuthenticated: false, isLoading: false);
    }
  }
}

final authControllerProvider = StateNotifierProvider<AuthController, AuthState>(
  (ref) {
    final apiClient = ref.watch(apiClientProvider);
    final storage = ref.watch(secureStorageProvider);
    return AuthController(apiClient: apiClient, storage: storage);
  },
);
