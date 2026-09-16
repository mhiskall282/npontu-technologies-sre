// test/models_test.dart

import 'package:flutter_test/flutter_test.dart';
import 'package:npontu_sre_mobile/shared/models/activity_model.dart';
import 'package:npontu_sre_mobile/shared/models/audit_log_model.dart';
import 'package:npontu_sre_mobile/shared/models/conversation_model.dart';
import 'package:npontu_sre_mobile/shared/models/shift_handover_model.dart';
import 'package:npontu_sre_mobile/shared/models/system_health_model.dart';
import 'package:npontu_sre_mobile/shared/models/user_model.dart';

void main() {
  group('UserModel Deserialization', () {
    test('parses json correctly and exposes permissions', () {
      final json = {
        'id': 1,
        'name': 'Kwame Mensah',
        'email': 'kwame@npontu.com',
        'role': 'lead',
        'grade': 'l3_senior',
        'grade_label': 'L3 Senior Engineer',
        'is_active': true,
        'unread_messages_count': 3,
        'created_at': '2026-09-16T12:00:00Z',
      };

      final user = UserModel.fromJson(json);

      expect(user.id, 1);
      expect(user.name, 'Kwame Mensah');
      expect(user.email, 'kwame@npontu.com');
      expect(user.role, 'lead');
      expect(user.grade, 'l3_senior');
      expect(user.gradeLabel, 'L3 Senior Engineer');
      expect(user.isAdmin, false);
      expect(user.isLead, true);
      expect(user.canManageChecklists, true);
      expect(user.canSignHandovers, true);
      expect(user.unreadMessagesCount, 3);
    });
  });

  group('ActivityModel Deserialization', () {
    test('parses activity with latest event and status', () {
      final json = {
        'id': 42,
        'title': 'Verify Redis cluster synchronization',
        'description': 'Check latency and memory usage on node-0',
        'shift': 'morning',
        'priority': 'high',
        'recurrence': 'daily',
        'sla_time': '09:00:00',
        'is_pinned': true,
        'is_active': true,
        'current_status': 'done',
        'is_done': true,
        'latest_log': {
          'id': 100,
          'activity_id': 42,
          'user_id': 1,
          'logged_date': '2026-09-16',
          'status': 'done',
          'remark': 'Latency < 2ms',
          'incident_ticket': null,
          'is_escalated': false,
          'created_at': '2026-09-16T09:05:00Z',
          'actor': {
            'id': 1,
            'name': 'Kwame Mensah',
            'email': 'kwame@npontu.com',
            'role': 'lead',
          },
        },
        'logs': [],
        'assignee': {
          'id': 1,
          'name': 'Kwame Mensah',
          'email': 'kwame@npontu.com',
          'role': 'lead',
        },
        'created_at': '2026-09-16T08:00:00Z',
      };

      final activity = ActivityModel.fromJson(json);

      expect(activity.id, 42);
      expect(activity.title, 'Verify Redis cluster synchronization');
      expect(activity.shift, 'morning');
      expect(activity.priority, 'high');
      expect(activity.isPinned, true);
      expect(activity.isDone, true);
      expect(activity.currentStatus, 'done');
      expect(activity.latestLog?.remark, 'Latency < 2ms');
      expect(activity.assignee?.name, 'Kwame Mensah');
    });
  });

  group('ShiftHandoverModel Deserialization', () {
    test('parses handover with two-way operator relations', () {
      final json = {
        'id': 10,
        'shift': 'morning',
        'handover_date': '2026-09-16',
        'outgoing_user_id': 1,
        'incoming_user_id': 2,
        'status': 'initiated',
        'summary': 'Morning shift clear, routine maintenance performed.',
        'outstanding_items': 'INC-102 monitored in staging.',
        'acknowledged_at': null,
        'outgoing_user': {
          'id': 1,
          'name': 'Kwame Mensah',
          'email': 'kwame@npontu.com',
          'role': 'lead',
        },
        'incoming_user': {
          'id': 2,
          'name': 'Abena Osei',
          'email': 'abena@npontu.com',
          'role': 'engineer',
        },
        'created_at': '2026-09-16T14:00:00Z',
      };

      final handover = ShiftHandoverModel.fromJson(json);

      expect(handover.id, 10);
      expect(handover.shift, 'morning');
      expect(handover.status, 'initiated');
      expect(handover.isAcknowledged, false);
      expect(handover.outgoingUser?.name, 'Kwame Mensah');
      expect(handover.incomingUser?.name, 'Abena Osei');
    });
  });

  group('ConversationModel & MessageModel Deserialization', () {
    test('parses war room and message attachments', () {
      final json = {
        'id': 5,
        'type': 'war_room',
        'title': 'INC-901 Payment Gateway Outage',
        'incident_id': 901,
        'created_by': 1,
        'unread_count': 2,
        'last_message_at': '2026-09-16T14:20:00Z',
        'participants': [],
        'created_at': '2026-09-16T14:00:00Z',
      };

      final conv = ConversationModel.fromJson(json);

      expect(conv.id, 5);
      expect(conv.type, 'war_room');
      expect(conv.title, 'INC-901 Payment Gateway Outage');
      expect(conv.unreadCount, 2);

      final msgJson = {
        'id': 101,
        'conversation_id': 5,
        'sender_id': 1,
        'body': 'Logs uploaded from app container.',
        'attachment_name': 'error.log',
        'attachment_mime': 'text/plain',
        'attachment_size': 1024,
        'attachment_blob': 'bGFyYXZlbCBsb2dz',
        'created_at': '2026-09-16T14:22:00Z',
      };

      final msg = MessageModel.fromJson(msgJson);
      expect(msg.id, 101);
      expect(msg.body, 'Logs uploaded from app container.');
      expect(msg.attachmentName, 'error.log');
    });
  });

  group('SystemHealthModel & AuditLogModel Deserialization', () {
    test('parses health diagnostics payload', () {
      final json = {
        'status': 'healthy',
        'timestamp': '2026-09-16T14:25:00Z',
        'database': {'status': 'connected', 'latency_ms': 1.2},
        'redis': {'status': 'connected'},
        'queue': {'status': 'running', 'failed_jobs': 0},
        'storage': {'status': 'writable'},
      };

      final health = SystemHealthModel.fromJson(json);

      expect(health.status, 'healthy');
      expect(health.databaseStatus, 'connected');
      expect(health.redisStatus, 'connected');
    });

    test('parses audit log with old and new values', () {
      final json = {
        'id': 500,
        'actor_id': 1,
        'actor_name': 'Kwame Mensah',
        'subject_type': 'App\\Models\\Activity',
        'subject_id': 42,
        'event': 'status_changed',
        'old_values': {'status': 'pending'},
        'new_values': {'status': 'done', 'remark': 'All clear'},
        'ip_address': '127.0.0.1',
        'created_at': '2026-09-16T14:26:00Z',
      };

      final audit = AuditLogModel.fromJson(json);

      expect(audit.id, 500);
      expect(audit.actorName, 'Kwame Mensah');
      expect(audit.event, 'status_changed');
      expect(audit.oldValues?['status'], 'pending');
      expect(audit.newValues?['status'], 'done');
    });
  });
}
