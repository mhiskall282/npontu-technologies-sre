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
  final String? organizationName;
  final String? companyCode;
  final String? tier;
  final String? planName;
  final String? platformRole;
  final bool isPlatformAdmin;
  final String? currentWorkspaceName;

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
    this.organizationName,
    this.companyCode,
    this.tier,
    this.planName,
    this.platformRole,
    this.isPlatformAdmin = false,
    this.currentWorkspaceName,
  });

  bool get isAdmin => role == 'admin';
  bool get isLead => role == 'lead';
  bool get isAgent => role == 'agent';

  String get roleLabel {
    switch (role.toLowerCase()) {
      case 'admin':
        return 'System Administrator';
      case 'lead':
        return 'Shift Team Lead';
      case 'agent':
      default:
        return 'Support Engineer (SRE)';
    }
  }

  String get seniorityDescription {
    switch (grade) {
      case 'L5':
        return 'Principal Architect & Enterprise Lead. Highest engineering tier overseeing system architecture, reliability governance, and cross-team incidents.';
      case 'L4':
        return 'Team Lead & Shift Supervisor. Oversees live shifts, conducts two-way handovers, assigns checks, and supervises operations.';
      case 'L3':
        return 'Senior SRE Specialist. Manages complex infrastructure checks, P1/P2 incidents, and root cause investigations.';
      case 'L2':
        return 'Support Engineer (SRE). Handles core checklist runs, SLA compliance checks, and real-time incident war rooms.';
      case 'L1':
      default:
        return 'Associate Support Operator. Conducts routine operational checks, verifies telemetry, and escalates anomalies.';
    }
  }

  static const Map<String, String> privilegeCatalog = {
    'manage_activities': 'Manage Activities & Checks',
    'assign_tasks': 'Delegate & Reassign Tasks',
    'sign_handovers': 'Sign Shift Handovers',
    'accept_handovers': 'Accept & Sign-On Handovers',
    'escalate_incidents': 'Flag Incidents & Escalations',
    'export_reports': 'Reporting & Data Export',
    'manage_users': 'User Administration',
    'view_audit_logs': 'View Security Audit Trails',
    'create_channels': 'Create Chat Channels',
  };

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
    final org = json['organization'] as Map<String, dynamic>?;
    final currentWs = json['current_workspace'] as Map<String, dynamic>?;

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
      organizationName: org?['name'] as String?,
      companyCode: org?['company_code'] as String?,
      tier: org?['tier'] as String?,
      planName: org?['plan_name'] as String?,
      platformRole: json['platform_role'] as String?,
      isPlatformAdmin: json['is_platform_admin'] as bool? ?? false,
      currentWorkspaceName: currentWs?['name'] as String?,
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
      'platform_role': platformRole,
      'is_platform_admin': isPlatformAdmin,
      'organization': organizationName != null
          ? {
              'name': organizationName,
              'company_code': companyCode,
              'tier': tier,
              'plan_name': planName,
            }
          : null,
      'current_workspace': currentWorkspaceName != null
          ? {'name': currentWorkspaceName}
          : null,
    };
  }
}
