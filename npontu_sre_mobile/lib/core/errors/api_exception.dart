// lib/core/errors/api_exception.dart

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  const ApiException({required this.message, this.statusCode, this.errors});

  @override
  String toString() => 'ApiException: $message (status: $statusCode)';

  String get firstErrorMessage {
    if (errors != null && errors!.isNotEmpty) {
      final firstKey = errors!.keys.first;
      final val = errors![firstKey];
      if (val is List && val.isNotEmpty) {
        return val.first.toString();
      }
      return val.toString();
    }
    return message;
  }
}

class NetworkException extends ApiException {
  const NetworkException({
    super.message = 'No internet connection. Please check your network.',
  }) : super(statusCode: 0);
}

class UnauthorizedException extends ApiException {
  const UnauthorizedException({
    super.message = 'Session expired or unauthenticated. Please log in again.',
  }) : super(statusCode: 401);
}

class ForbiddenException extends ApiException {
  const ForbiddenException({
    super.message =
        'You do not have permission to perform this operational action.',
  }) : super(statusCode: 403);
}

class NotFoundException extends ApiException {
  const NotFoundException({
    super.message = 'The requested operational record was not found.',
  }) : super(statusCode: 404);
}

class ServerException extends ApiException {
  const ServerException({
    super.message = 'Internal server error. The SRE team has been alerted.',
  }) : super(statusCode: 500);
}
