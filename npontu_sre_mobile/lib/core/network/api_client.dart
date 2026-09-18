// lib/core/network/api_client.dart

import 'package:dio/dio.dart';

import '../config/app_config.dart';
import '../errors/api_exception.dart';
import '../storage/secure_storage.dart';

class ApiClient {
  late final Dio dio;
  final SecureStorageService storageService;

  ApiClient({required this.storageService, Dio? customDio}) {
    dio =
        customDio ??
        Dio(
          BaseOptions(
            baseUrl: AppConfig.baseUrl,
            connectTimeout: AppConfig.connectTimeout,
            receiveTimeout: AppConfig.receiveTimeout,
            sendTimeout: AppConfig.sendTimeout,
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
          ),
        );

    _setupInterceptors();
  }

  void updateBaseUrl(String newUrl) {
    AppConfig.setBaseUrl(newUrl);
    dio.options.baseUrl = AppConfig.baseUrl;
  }

  void _setupInterceptors() {
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Attach Sanctum Bearer token
          final token = await storageService.getAuthToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          // Attach active multi-tenant workspace context
          final activeWorkspaceId = await storageService.getActiveWorkspaceId();
          if (activeWorkspaceId != null && activeWorkspaceId.isNotEmpty) {
            options.headers['X-Workspace-Id'] = activeWorkspaceId;
          }
          return handler.next(options);
        },
        onError: (DioException e, handler) {
          final mappedException = _mapDioException(e);
          return handler.reject(
            DioException(
              requestOptions: e.requestOptions,
              response: e.response,
              type: e.type,
              error: mappedException,
              message: mappedException.message,
            ),
          );
        },
      ),
    );
  }

  ApiException _mapDioException(DioException e) {
    if (e.type == DioExceptionType.connectionTimeout ||
        e.type == DioExceptionType.receiveTimeout ||
        e.type == DioExceptionType.sendTimeout ||
        e.type == DioExceptionType.connectionError) {
      return const NetworkException();
    }

    final response = e.response;
    if (response == null) {
      return ApiException(
        message: e.message ?? 'Unknown connection error occurred.',
      );
    }

    final statusCode = response.statusCode ?? 500;
    final data = response.data;
    String message = 'Unexpected error occurred.';
    Map<String, dynamic>? errors;

    if (data is Map<String, dynamic>) {
      message = data['message']?.toString() ?? message;
      if (data['errors'] is Map<String, dynamic>) {
        errors = data['errors'] as Map<String, dynamic>;
      }
    }

    switch (statusCode) {
      case 401:
        return UnauthorizedException(message: message);
      case 403:
        return ForbiddenException(message: message);
      case 404:
        return NotFoundException(message: message);
      case 422:
        return ApiException(message: message, statusCode: 422, errors: errors);
      case 500:
      case 502:
      case 503:
        return ServerException(message: message);
      default:
        return ApiException(
          message: message,
          statusCode: statusCode,
          errors: errors,
        );
    }
  }

  // Safe HTTP convenience wrappers
  Future<Response<T>> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await dio.get<T>(
        path,
        queryParameters: queryParameters,
        options: options,
      );
    } on DioException catch (e) {
      throw (e.error is ApiException)
          ? e.error as ApiException
          : _mapDioException(e);
    }
  }

  Future<Response<T>> post<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await dio.post<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } on DioException catch (e) {
      throw (e.error is ApiException)
          ? e.error as ApiException
          : _mapDioException(e);
    }
  }

  Future<Response<T>> put<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await dio.put<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } on DioException catch (e) {
      throw (e.error is ApiException)
          ? e.error as ApiException
          : _mapDioException(e);
    }
  }

  Future<Response<T>> delete<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await dio.delete<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } on DioException catch (e) {
      throw (e.error is ApiException)
          ? e.error as ApiException
          : _mapDioException(e);
    }
  }
}
