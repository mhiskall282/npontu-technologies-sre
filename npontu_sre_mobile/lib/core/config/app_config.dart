// lib/core/config/app_config.dart

class AppConfig {
  // Live Render production backend URL
  static const String defaultProductionUrl =
      'https://npontu-support-tracker.onrender.com/api/v1';

  // Local development fallbacks
  static const String defaultLocalUrl = 'http://127.0.0.1:8000/api/v1';
  static const String defaultAndroidEmulatorUrl = 'http://10.0.2.2:8000/api/v1';

  // Environment-provided or default live Render production URL
  static const String configuredUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: defaultProductionUrl,
  );

  static String baseUrl = configuredUrl;

  static const Duration connectTimeout = Duration(seconds: 35);
  static const Duration receiveTimeout = Duration(seconds: 35);
  static const Duration sendTimeout = Duration(seconds: 30);

  static void setBaseUrl(String url) {
    if (url.isNotEmpty) {
      baseUrl = url.endsWith('/')
          ? '${url}api/v1'
          : (url.contains('/api/v1') ? url : '$url/api/v1');
    }
  }
}
