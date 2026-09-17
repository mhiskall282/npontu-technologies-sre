// lib/features/notifications/notification_model.dart

import 'package:flutter/foundation.dart';

/// Mirrors the `operational_notifications` table in the Laravel backend.
@immutable
class NotificationModel {
  const NotificationModel({
    required this.id,
    required this.title,
    required this.body,
    required this.type,
    required this.isRead,
    required this.createdAt,
    this.readAt,
    this.relatedType,
    this.relatedId,
    this.data,
  });

  final int id;
  final String title;
  final String body;

  /// Notification type: 'incident', 'escalation', 'handover', 'assignment',
  /// 'system', 'info', 'warning'.
  final String type;

  final bool isRead;
  final DateTime createdAt;
  final DateTime? readAt;

  /// Polymorphic subject — e.g. 'Activity', 'ShiftHandover'.
  final String? relatedType;
  final int? relatedId;

  /// Arbitrary JSON payload from the server.
  final Map<String, dynamic>? data;

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'] as int,
      title: json['title'] as String,
      body: (json['body'] ?? json['message'] ?? '') as String,
      type: json['type'] as String? ?? 'info',
      isRead: json['is_read'] as bool? ?? false,
      createdAt: DateTime.parse(json['created_at'] as String),
      readAt: json['read_at'] != null
          ? DateTime.parse(json['read_at'] as String)
          : null,
      relatedType: json['related_type'] as String?,
      relatedId: json['related_id'] as int?,
      data: (json['data'] ?? json['metadata']) as Map<String, dynamic>?,
    );
  }

  NotificationModel copyWith({bool? isRead, DateTime? readAt}) {
    return NotificationModel(
      id: id,
      title: title,
      body: body,
      type: type,
      isRead: isRead ?? this.isRead,
      createdAt: createdAt,
      readAt: readAt ?? this.readAt,
      relatedType: relatedType,
      relatedId: relatedId,
      data: data,
    );
  }
}
