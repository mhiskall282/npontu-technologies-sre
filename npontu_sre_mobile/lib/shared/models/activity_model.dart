// lib/shared/models/activity_model.dart

import 'user_model.dart';

class ActivityLogModel {
  final int id;
  final int activityId;
  final String date;
  final String status; // 'pending' | 'done'
  final String? remark;
  final String? incidentTicket;
  final bool isEscalated;
  final int? updatedBy;
  final String actorName;
  final String? actorRole;
  final String? actorDesignation;
  final UserModel? updater;
  final DateTime? createdAt;
  String get loggedDate => date;
  UserModel? get actor => updater;

  const ActivityLogModel({
    required this.id,
    required this.activityId,
    required this.date,
    required this.status,
    this.remark,
    this.incidentTicket,
    this.isEscalated = false,
    this.updatedBy,
    required this.actorName,
    this.actorRole,
    this.actorDesignation,
    this.updater,
    this.createdAt,
  });

  bool get isDone => status == 'done';
  bool get isPending => status == 'pending';

  factory ActivityLogModel.fromJson(Map<String, dynamic> json) {
    return ActivityLogModel(
      id: json['id'] is int
          ? json['id'] as int
          : int.parse(json['id'].toString()),
      activityId: json['activity_id'] is int
          ? json['activity_id'] as int
          : int.parse(json['activity_id'].toString()),
      date: json['date'] as String? ?? json['logged_date'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      remark: json['remark'] as String?,
      incidentTicket: json['incident_ticket'] as String?,
      isEscalated: json['is_escalated'] == true || json['is_escalated'] == 1,
      updatedBy: json['updated_by'] as int?,
      actorName:
          json['actor_name'] as String? ??
          (json['actor'] is Map<String, dynamic>
              ? json['actor']['name'] as String?
              : null) ??
          'System',
      actorRole:
          json['actor_role'] as String? ??
          (json['actor'] is Map<String, dynamic>
              ? json['actor']['role'] as String?
              : null),
      actorDesignation: json['actor_designation'] as String?,
      updater: json['updater'] is Map<String, dynamic>
          ? UserModel.fromJson(json['updater'] as Map<String, dynamic>)
          : (json['actor'] is Map<String, dynamic>
                ? UserModel.fromJson(json['actor'] as Map<String, dynamic>)
                : null),
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }
}

class ActivityModel {
  final int id;
  final String title;
  final String? description;
  final String? category;
  final String shift;
  final String recurrence;
  final String priority; // 'critical' | 'high' | 'medium' | 'low'
  final String? slaTime;
  final bool isPinned;
  final bool isActive;
  final int? createdBy;
  final UserModel? creator;
  final int? assignedTo;
  final UserModel? assignee;
  final String currentStatus; // 'pending' | 'done'
  final ActivityLogModel? latestLog;
  final List<ActivityLogModel> logs;

  const ActivityModel({
    required this.id,
    required this.title,
    this.description,
    this.category,
    this.shift = 'morning',
    this.recurrence = 'daily',
    this.priority = 'medium',
    this.slaTime,
    this.isPinned = false,
    this.isActive = true,
    this.createdBy,
    this.creator,
    this.assignedTo,
    this.assignee,
    this.currentStatus = 'pending',
    this.latestLog,
    this.logs = const [],
  });

  bool get isDone => currentStatus == 'done';
  bool get isPending => currentStatus == 'pending';
  bool get isCritical => priority == 'critical';
  bool get isHigh => priority == 'high';

  String get priorityTierLabel {
    switch (priority) {
      case 'critical':
        return 'P1 • CRITICAL';
      case 'high':
        return 'P2 • HIGH';
      case 'medium':
        return 'P3 • MEDIUM';
      case 'low':
        return 'P4 • LOW';
      default:
        return priority.toUpperCase();
    }
  }

  factory ActivityModel.fromJson(Map<String, dynamic> json) {
    return ActivityModel(
      id: json['id'] is int
          ? json['id'] as int
          : int.parse(json['id'].toString()),
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      category: json['category'] as String?,
      shift: json['shift'] as String? ?? 'morning',
      recurrence: json['recurrence'] as String? ?? 'daily',
      priority: json['priority'] as String? ?? 'medium',
      slaTime: json['sla_time'] as String?,
      isPinned: json['is_pinned'] == true || json['is_pinned'] == 1,
      isActive: json['is_active'] != false,
      createdBy: json['created_by'] as int?,
      creator: json['creator'] is Map<String, dynamic>
          ? UserModel.fromJson(json['creator'] as Map<String, dynamic>)
          : null,
      assignedTo: json['assigned_to'] as int?,
      assignee: json['assignee'] is Map<String, dynamic>
          ? UserModel.fromJson(json['assignee'] as Map<String, dynamic>)
          : null,
      currentStatus: json['current_status'] as String? ?? 'pending',
      latestLog: json['latest_log'] is Map<String, dynamic>
          ? ActivityLogModel.fromJson(
              json['latest_log'] as Map<String, dynamic>,
            )
          : null,
      logs:
          (json['logs'] as List<dynamic>?)
              ?.map((e) => ActivityLogModel.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}
