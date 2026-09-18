// test/features/workspaces/workspace_model_test.dart

import 'package:flutter_test/flutter_test.dart';
import 'package:npontu_sre_mobile/features/workspaces/workspace_model.dart';

void main() {
  group('WorkspaceModel Tests', () {
    test('parses organization workspace json correctly', () {
      final json = {
        'id': 1,
        'uuid': 'd290f1ee-6c54-4b01-90e6-d701748f0851',
        'name': 'Fintech Core SRE',
        'slug': 'fintech-core-sre',
        'subdomain': 'fintech',
        'custom_domain': 'sre.fintech.example',
        'is_personal': false,
        'status': 'active',
        'my_role': 'lead',
        'organization': {
          'id': 10,
          'name': 'Fintech Global',
          'slug': 'fintech-global',
          'company_code': 'FNT-001',
        },
      };

      final ws = WorkspaceModel.fromJson(json);

      expect(ws.id, 1);
      expect(ws.uuid, 'd290f1ee-6c54-4b01-90e6-d701748f0851');
      expect(ws.name, 'Fintech Core SRE');
      expect(ws.slug, 'fintech-core-sre');
      expect(ws.subdomain, 'fintech');
      expect(ws.isPersonal, false);
      expect(ws.status, 'active');
      expect(ws.myRole, 'lead');
      expect(ws.organizationName, 'Fintech Global');
      expect(ws.companyCode, 'FNT-001');
    });

    test('parses personal workspace json correctly', () {
      final json = {
        'id': 99,
        'uuid': '8888-9999-aaaa-bbbb',
        'name': "John's Personal Sandbox",
        'slug': 'john-personal',
        'is_personal': true,
        'status': 'active',
        'my_role': 'admin',
        'organization': null,
      };

      final ws = WorkspaceModel.fromJson(json);

      expect(ws.id, 99);
      expect(ws.name, "John's Personal Sandbox");
      expect(ws.isPersonal, true);
      expect(ws.organizationName, isNull);
      expect(ws.myRole, 'admin');
    });

    test('serializes to json correctly', () {
      const ws = WorkspaceModel(
        id: 5,
        uuid: 'test-uuid-123',
        name: 'Alpha Ops',
        slug: 'alpha-ops',
        isPersonal: false,
        status: 'active',
        myRole: 'agent',
        organizationName: 'Alpha Corp',
        companyCode: 'ALP-01',
      );

      final json = ws.toJson();

      expect(json['id'], 5);
      expect(json['name'], 'Alpha Ops');
      expect(json['is_personal'], false);
      expect(json['organization']['name'], 'Alpha Corp');
      expect(json['organization']['company_code'], 'ALP-01');
    });
  });
}
