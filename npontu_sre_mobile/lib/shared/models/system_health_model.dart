// lib/shared/models/system_health_model.dart

class SubsystemHealth {
  final String name;
  final String category;
  final String driver;
  final String latency;
  final String status;
  final String detail;
  final String icon;

  const SubsystemHealth({
    required this.name,
    required this.category,
    required this.driver,
    required this.latency,
    required this.status,
    required this.detail,
    required this.icon,
  });

  bool get isOperational => status == 'operational';

  factory SubsystemHealth.fromJson(Map<String, dynamic> json) {
    return SubsystemHealth(
      name: json['name'] as String? ?? '',
      category: json['category'] as String? ?? '',
      driver: json['driver'] as String? ?? '',
      latency: json['latency'] as String? ?? '',
      status: json['status'] as String? ?? 'operational',
      detail: json['detail'] as String? ?? '',
      icon: json['icon'] as String? ?? 'cpu',
    );
  }
}

class SystemHealthModel {
  final String status;
  final double dbLatencyMs;
  final String uptimeSla;
  final String? environment;
  final String timestamp;
  final List<SubsystemHealth> subsystems;

  const SystemHealthModel({
    required this.status,
    required this.dbLatencyMs,
    required this.uptimeSla,
    this.environment,
    this.timestamp = '',
    this.subsystems = const [],
  });

  bool get isOperational =>
      status == 'ok' || status == 'operational' || status == 'healthy';

  String get databaseStatus =>
      subsystems
          .cast<SubsystemHealth?>()
          .firstWhere(
            (s) =>
                s?.name.toLowerCase().contains('database') == true ||
                s?.name.toLowerCase().contains('mysql') == true,
            orElse: () => null,
          )
          ?.status ??
      'connected';

  String get redisStatus =>
      subsystems
          .cast<SubsystemHealth?>()
          .firstWhere(
            (s) =>
                s?.name.toLowerCase().contains('redis') == true ||
                s?.name.toLowerCase().contains('cache') == true,
            orElse: () => null,
          )
          ?.status ??
      'connected';

  String get queueStatus =>
      subsystems
          .cast<SubsystemHealth?>()
          .firstWhere(
            (s) => s?.name.toLowerCase().contains('queue') == true,
            orElse: () => null,
          )
          ?.status ??
      'running';

  String get storageStatus =>
      subsystems
          .cast<SubsystemHealth?>()
          .firstWhere(
            (s) =>
                s?.name.toLowerCase().contains('storage') == true ||
                s?.name.toLowerCase().contains('disk') == true,
            orElse: () => null,
          )
          ?.status ??
      'healthy';

  factory SystemHealthModel.fromJson(Map<String, dynamic> json) {
    final subList =
        (json['subsystems'] as List<dynamic>?)
            ?.map((e) => SubsystemHealth.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];

    final dbStatus = json['database'] is Map
        ? (json['database']['status'] as String? ?? 'connected')
        : null;
    final rStatus = json['redis'] is Map
        ? (json['redis']['status'] as String? ?? 'connected')
        : null;
    final qStatus = json['queue'] is Map
        ? (json['queue']['status'] as String? ?? 'running')
        : null;
    final sStatus = json['storage'] is Map
        ? (json['storage']['status'] as String? ?? 'healthy')
        : null;

    if (subList.isEmpty) {
      if (dbStatus != null) {
        subList.add(
          SubsystemHealth(
            name: 'Database',
            category: 'Storage',
            driver: 'MySQL',
            latency: '1.2ms',
            status: dbStatus,
            detail: 'Operational',
            icon: 'database',
          ),
        );
      }
      if (rStatus != null) {
        subList.add(
          SubsystemHealth(
            name: 'Redis',
            category: 'Cache',
            driver: 'Redis',
            latency: '0.4ms',
            status: rStatus,
            detail: 'Operational',
            icon: 'flash',
          ),
        );
      }
      if (qStatus != null) {
        subList.add(
          SubsystemHealth(
            name: 'Queues',
            category: 'Async',
            driver: 'Redis',
            latency: '0ms',
            status: qStatus,
            detail: 'Running',
            icon: 'queue',
          ),
        );
      }
      if (sStatus != null) {
        subList.add(
          SubsystemHealth(
            name: 'Storage',
            category: 'Filesystem',
            driver: 'Local',
            latency: '0ms',
            status: sStatus,
            detail: 'Writable',
            icon: 'disk',
          ),
        );
      }
    }

    return SystemHealthModel(
      status: json['status'] as String? ?? 'healthy',
      dbLatencyMs: (json['db_latency_ms'] is num)
          ? (json['db_latency_ms'] as num).toDouble()
          : 0.0,
      uptimeSla: json['uptime_sla'] as String? ?? '99.98%',
      environment: json['environment'] as String?,
      timestamp:
          json['timestamp'] as String? ?? DateTime.now().toIso8601String(),
      subsystems: subList,
    );
  }
}
