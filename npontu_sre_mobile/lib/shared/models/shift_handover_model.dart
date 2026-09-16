// lib/shared/models/shift_handover_model.dart

import 'user_model.dart';

class ShiftHandoverModel {
  final int id;
  final String date;
  final String shift; // 'morning' | 'afternoon' | 'night'
  final String shiftLabel;
  final int outgoingLeadId;
  final UserModel? outgoingLead;
  final int? incomingLeadId;
  final UserModel? incomingLead;
  final String summary;
  final String? incidents;
  final int pendingTasksCount;
  final int completedTasksCount;
  final DateTime? signedAt;
  final DateTime? acceptedAt;
  final int? acceptedById;
  final UserModel? acceptedBy;
  final String? acceptanceRemarks;
  final bool isAccepted;
  final DateTime? createdAt;

  const ShiftHandoverModel({
    required this.id,
    required this.date,
    required this.shift,
    required this.shiftLabel,
    required this.outgoingLeadId,
    this.outgoingLead,
    this.incomingLeadId,
    this.incomingLead,
    required this.summary,
    this.incidents,
    this.pendingTasksCount = 0,
    this.completedTasksCount = 0,
    this.signedAt,
    this.acceptedAt,
    this.acceptedById,
    this.acceptedBy,
    this.acceptanceRemarks,
    this.isAccepted = false,
    this.createdAt,
  });

  String get handoverDate => date;
  String get status => isAccepted ? 'acknowledged' : 'initiated';
  bool get isAcknowledged => isAccepted;
  UserModel? get outgoingUser => outgoingLead;
  int get outgoingUserId => outgoingLeadId;
  UserModel? get incomingUser => incomingLead;
  int? get incomingUserId => incomingLeadId;
  String? get outstandingItems => incidents;

  factory ShiftHandoverModel.fromJson(Map<String, dynamic> json) {
    final rawOutgoing = json['outgoing_user'] ?? json['outgoing_lead'];
    final rawIncoming = json['incoming_user'] ?? json['incoming_lead'];
    final rawOutgoingId =
        json['outgoing_user_id'] ?? json['outgoing_lead_id'] ?? 0;
    final rawIncomingId = json['incoming_user_id'] ?? json['incoming_lead_id'];

    return ShiftHandoverModel(
      id: json['id'] is int
          ? json['id'] as int
          : int.parse(json['id'].toString()),
      date: json['date'] as String? ?? json['handover_date'] as String? ?? '',
      shift: json['shift'] as String? ?? 'morning',
      shiftLabel: json['shift_label'] as String? ?? '${json['shift']} Shift',
      outgoingLeadId: rawOutgoingId is int
          ? rawOutgoingId
          : int.parse(rawOutgoingId.toString()),
      outgoingLead: rawOutgoing is Map<String, dynamic>
          ? UserModel.fromJson(rawOutgoing)
          : null,
      incomingLeadId: rawIncomingId != null
          ? (rawIncomingId is int
                ? rawIncomingId
                : int.parse(rawIncomingId.toString()))
          : null,
      incomingLead: rawIncoming is Map<String, dynamic>
          ? UserModel.fromJson(rawIncoming)
          : null,
      summary: json['summary'] as String? ?? '',
      incidents:
          json['incidents'] as String? ?? json['outstanding_items'] as String?,
      pendingTasksCount: json['pending_tasks_count'] as int? ?? 0,
      completedTasksCount: json['completed_tasks_count'] as int? ?? 0,
      signedAt: json['signed_at'] != null
          ? DateTime.tryParse(json['signed_at'].toString())
          : null,
      acceptedAt: json['accepted_at'] != null
          ? DateTime.tryParse(json['accepted_at'].toString())
          : null,
      acceptedById: json['accepted_by_id'] as int?,
      acceptedBy: json['accepted_by'] is Map<String, dynamic>
          ? UserModel.fromJson(json['accepted_by'] as Map<String, dynamic>)
          : null,
      acceptanceRemarks: json['acceptance_remarks'] as String?,
      isAccepted:
          json['is_accepted'] == true ||
          json['status'] == 'acknowledged' ||
          json['accepted_at'] != null,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }
}
