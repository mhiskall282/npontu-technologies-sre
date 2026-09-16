// test/live_render_integration_test.dart
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:npontu_sre_mobile/shared/models/system_health_model.dart';

void main() {
  group('Live Render Production Connectivity Tests', () {
    late Dio directDio;

    setUp(() {
      directDio = Dio(
        BaseOptions(
          connectTimeout: const Duration(seconds: 15),
          receiveTimeout: const Duration(seconds: 15),
          headers: {'Accept': 'application/json'},
        ),
      );
    });

    test('verifies live connectivity to Render production health probe', () async {
      final response = await directDio.get<Map<String, dynamic>>(
        'https://npontu-support-tracker.onrender.com/health',
      );

      expect(response.statusCode, 200);
      final data = response.data;
      expect(data, isNotNull);
      expect(data!['status'], 'ok');
      expect(data['db'], 'ok');
      expect(data['environment'], 'production');
      expect(data['uptime_sla'], '99.98%');

      // Verify deserialization into mobile model
      final health = SystemHealthModel.fromJson(data);
      expect(health.status, 'ok');
      expect(health.isOperational, isTrue);
      expect(health.uptimeSla, '99.98%');
    });
  });
}
