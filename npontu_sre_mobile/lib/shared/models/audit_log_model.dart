// lib/shared/models/audit_log_model.dart

class AuditLogModel {
  final int id;
  final int? actorId;
  final String actorName;
  final String? actorRole;
  final String? actorIp;
  final String subjectType;
  final int subjectId;
  final String event;
  final Map<String, dynamic>? oldValues;
  final Map<String, dynamic>? newValues;
  final DateTime? createdAt;

  const AuditLogModel({
    required this.id,
    this.actorId,
    required this.actorName,
    this.actorRole,
    this.actorIp,
    required this.subjectType,
    required this.subjectId,
    required this.event,
    this.oldValues,
    this.newValues,
    this.createdAt,
  });

  String? get ipAddress => actorIp;

  factory AuditLogModel.fromJson(Map<String, dynamic> json) {
    return AuditLogModel(
      id: json['id'] is int
          ? json['id'] as int
          : int.parse(json['id'].toString()),
      actorId: json['actor_id'] as int?,
      actorName: json['actor_name'] as String? ?? 'System',
      actorRole: json['actor_role'] as String?,
      actorIp: json['actor_ip'] as String? ?? json['ip_address'] as String?,
      subjectType: json['subject_type'] as String? ?? '',
      subjectId: json['subject_id'] is int
          ? json['subject_id'] as int
          : int.parse(json['subject_id'].toString()),
      event: json['event'] as String? ?? '',
      oldValues: json['old_values'] as Map<String, dynamic>?,
      newValues: json['new_values'] as Map<String, dynamic>?,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }
}
