// lib/features/workspaces/workspace_model.dart

class WorkspaceModel {
  final int id;
  final String uuid;
  final String name;
  final String slug;
  final String? subdomain;
  final String? customDomain;
  final bool isPersonal;
  final String status;
  final String myRole;
  final String? organizationName;
  final String? companyCode;
  final String? tier;
  final String? planName;

  const WorkspaceModel({
    required this.id,
    required this.uuid,
    required this.name,
    required this.slug,
    this.subdomain,
    this.customDomain,
    required this.isPersonal,
    required this.status,
    required this.myRole,
    this.organizationName,
    this.companyCode,
    this.tier,
    this.planName,
  });

  factory WorkspaceModel.fromJson(Map<String, dynamic> json) {
    final org = json['organization'] as Map<String, dynamic>?;

    return WorkspaceModel(
      id: json['id'] as int? ?? 0,
      uuid: json['uuid'] as String? ?? '',
      name: json['name'] as String? ?? 'Unnamed Workspace',
      slug: json['slug'] as String? ?? '',
      subdomain: json['subdomain'] as String?,
      customDomain: json['custom_domain'] as String?,
      isPersonal: json['is_personal'] as bool? ?? false,
      status: json['status'] as String? ?? 'active',
      myRole: json['my_role'] as String? ?? 'agent',
      organizationName: org?['name'] as String?,
      companyCode: org?['company_code'] as String?,
      tier: org?['tier'] as String?,
      planName: org?['plan_name'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'uuid': uuid,
      'name': name,
      'slug': slug,
      'subdomain': subdomain,
      'custom_domain': customDomain,
      'is_personal': isPersonal,
      'status': status,
      'my_role': myRole,
      'organization': organizationName != null
          ? {'name': organizationName, 'company_code': companyCode}
          : null,
    };
  }
}
