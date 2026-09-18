# Opsora Development — Environment Variables Dictionary

> **Status:** AUDITED  
> **Last Verified:** 2026-09-18

This table documents every environment variable utilized across the Opsora backend.

| Variable | Purpose | Default / Example | Required | Environment |
|---|---|---|---|---|
| `APP_NAME` | Customer-facing product brand | `Opsora` | Yes | All |
| `APP_ENV` | Application environment | `production` / `local` | Yes | All |
| `APP_KEY` | 32-character AES encryption key | `base64:...` | Yes | All |
| `APP_DEBUG` | Detailed debug error output | `false` | Yes | Local only |
| `APP_URL` | Canonical platform URL | `https://opsora.production` | Yes | All |
| `DB_CONNECTION`| Primary SQL database driver | `mysql` | Yes | All |
| `DB_HOST` | Database host address | `127.0.0.1` | Yes | All |
| `DB_PORT` | Database port number | `3306` | Yes | All |
| `DB_DATABASE` | Database name | `npontu_tracker` | Yes | All |
| `SESSION_DRIVER`| Web session storage driver | `database` / `file` | Yes | All |
| `QUEUE_CONNECTION`| Async queue driver | `database` | Yes | All |
| `MAIL_MAILER` | Outbound email driver | `smtp` / `log` | Yes | All |
