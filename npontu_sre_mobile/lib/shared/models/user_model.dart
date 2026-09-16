// lib/shared/models/user_model.dart

class UserModel {
  final int id;
  final String name;
  final String email;
  final String role; // 'admin' | 'lead' | 'agent'
  final String grade; // 'L1' - 'L5'
  final String gradeLabel;
  final String? department;
  final String? designation;
  final String? phone;
  final List<String> privileges;
  final int unreadMessagesCount;

  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.grade,
    required this.gradeLabel,
    this.department,
    this.designation,
    this.phone,
    this.privileges = const [],
    this.unreadMessagesCount = 0,
  });

  bool get isAdmin => role == 'admin';
  bool get isLead => role == 'lead';
  bool get isAgent => role == 'agent';

  bool hasPrivilege(String privilege) {
    if (isAdmin) return true;
    return privileges.contains(privilege);
  }

  bool get canManageActivities => hasPrivilege('manage_activities');
  bool get canManageChecklists => isAdmin || isLead || canManageActivities;
  bool get canAssignTasks => hasPrivilege('assign_tasks');
  bool get canSignHandovers =>
      isAdmin || isLead || hasPrivilege('sign_handovers');
  bool get canAcceptHandovers => hasPrivilege('accept_handovers');
  bool get canEscalateIncidents => hasPrivilege('escalate_incidents');
  bool get canExportReports => hasPrivilege('export_reports');
  bool get canViewAuditLogs => hasPrivilege('view_audit_logs');
  bool get canCreateChannels => hasPrivilege('create_channels');

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int
          ? json['id'] as int
          : int.parse(json['id'].toString()),
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      role: json['role'] as String? ?? 'agent',
      grade: json['grade'] as String? ?? 'L1',
      gradeLabel:
          json['grade_label'] as String? ?? json['grade'] as String? ?? 'L1',
      department: json['department'] as String?,
      designation: json['designation'] as String?,
      phone: json['phone'] as String?,
      privileges:
          (json['privileges'] as List<dynamic>?)
              ?.map((e) => e.toString())
              .toList() ??
          [],
      unreadMessagesCount: json['unread_messages_count'] as int? ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'grade': grade,
      'grade_label': gradeLabel,
      'department': department,
      'designation': designation,
      'phone': phone,
      'privileges': privileges,
      'unread_messages_count': unreadMessagesCount,
    };
  }
}
