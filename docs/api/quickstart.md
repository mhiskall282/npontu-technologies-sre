# Opsora REST API — Developer Quickstart Guide

> **Status:** IMPLEMENTED  
> **API Version:** v1

Get started making requests to the Opsora REST API in under 2 minutes.

---

## 1. Authentication & Token Acquisition

Send your operator email and password to `/api/v1/auth/login`:

### cURL
```bash
curl -X POST https://npontu-support-tracker.onrender.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "hello@johnokyere.xyz",
    "password": "password",
    "device_name": "API Quickstart Client"
  }'
```

Response:
```json
{
  "token": "1|sre_token_xyz987...",
  "user": {
    "id": 1,
    "name": "John Okyere",
    "role": "admin"
  }
}
```

---

## 2. Listing Accessible Workspaces

```bash
curl -X GET https://npontu-support-tracker.onrender.com/api/v1/workspaces \
  -H "Authorization: Bearer 1|sre_token_xyz987..." \
  -H "Accept: application/json"
```

---

## 3. Querying Daily Activities in Workspace Context

Pass the desired workspace ID in `X-Workspace-Id`:

```bash
curl -X GET https://npontu-support-tracker.onrender.com/api/v1/activities \
  -H "Authorization: Bearer 1|sre_token_xyz987..." \
  -H "X-Workspace-Id: 1" \
  -H "Accept: application/json"
```

---

## 4. Dart (Flutter) Example

```dart
import 'package:dio/dio.dart';

final dio = Dio(BaseOptions(
  baseUrl: 'https://npontu-support-tracker.onrender.com/api/v1',
  headers: {
    'Authorization': 'Bearer $token',
    'X-Workspace-Id': '$activeWorkspaceId',
    'Accept': 'application/json',
  },
));

final response = await dio.get('/activities');
print(response.data);
```
