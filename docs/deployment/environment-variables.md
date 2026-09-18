# Environment Variables Reference Guide

This document catalogs every environment variable supported and utilized by the **Opsora (formerly Npontu) SRE Platform**, their purpose, requirements across environments, safe defaults, and security considerations.

---

## 1. Application & Core Identity

| Variable Name | Purpose | Required / Optional | Environment | Example Placeholder | Security Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `APP_NAME` | Display name of the SRE platform instance | Required | All | `"Opsora SRE"` | Publicly visible in email footers, page titles, and mobile clients. |
| `APP_ENV` | Runtime environment mode | Required | All | `production` / `staging` / `local` | In `production`, triggers strict asset caching and hides debug output. |
| `APP_KEY` | 32-character AES encryption key for sessions, cookies, and tokens | **Strictly Required** | All | `base64:4eU5u7...` | Generated via `php artisan key:generate`. **Never commit or expose.** |
| `APP_DEBUG` | Enables detailed stack traces and debugging views | Required | Production: `false` | `false` | **MUST BE `false` in production** to prevent credential or environment leakage. |
| `APP_URL` | Canonical root URL of the SRE web application | Required | All | `https://sre.opsora.io` | Used for generating absolute links in emails, webhooks, and Sanctum cookies. |
| `APP_LOCALE` | Default UI localization code | Optional | All | `en` | Defaults to English. |
| `APP_FALLBACK_LOCALE` | Fallback language code | Optional | All | `en` | Used if selected locale translation string is missing. |

---

## 2. Database Connectivity (MySQL 8.0+)

| Variable Name | Purpose | Required / Optional | Environment | Example Placeholder | Security Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `DB_CONNECTION` | Database driver | Required | All | `mysql` | Platform requires MySQL 8.0+ for JSON operations and locking. (SQLite in tests). |
| `DB_HOST` | Database server host or IP | Required | All | `127.0.0.1` or `db.internal.vpc` | Use private VPC endpoints in production clusters. |
| `DB_PORT` | Database server listening port | Required | All | `3306` | Standard MySQL port. |
| `DB_DATABASE` | Target database name | Required | All | `opsora_sre_prod` | Dedicated database instance with InnoDB storage engine. |
| `DB_USERNAME` | Database user account | Required | All | `opsora_app` | Restrict database user privileges to DML operations (`SELECT`, `INSERT`, `UPDATE`, `DELETE`). |
| `DB_PASSWORD` | Database user password | **Strictly Required** | All | `v3ry_Str0ng_P@ssw0rd!` | Store in cloud secrets manager (AWS Secrets Manager, Vault, etc.). |

---

## 3. Session & Authentication (Laravel Sanctum)

| Variable Name | Purpose | Required / Optional | Environment | Example Placeholder | Security Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `SESSION_DRIVER` | Storage backend for web sessions | Required | Production: `database` or `redis` | `database` | Do not use `file` or `cookie` in multi-server horizontal clusters. |
| `SESSION_LIFETIME` | Session duration in minutes | Required | All | `120` | Default 2 hours. Auto-invalidates inactive SRE operator sessions. |
| `SESSION_ENCRYPT` | Encrypt session payloads on disk/DB | Optional | Production: `true` | `true` | Recommended `true` for enterprise compliance. |
| `SANCTUM_STATEFUL_DOMAINS` | Domains permitted to exchange SPA session cookies | Required for SPA | Production | `sre.opsora.io,app.opsora.io` | Comma-delimited list of trusted subdomains. |

---

## 4. Cache, Queues & Background Workers

| Variable Name | Purpose | Required / Optional | Environment | Example Placeholder | Security Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `CACHE_STORE` | Cache driver for metrics & rate limiting | Required | All | `database` or `redis` | Redis recommended for high-throughput production clusters. |
| `QUEUE_CONNECTION` | Background job queue driver | Required | All | `database` or `redis` | Processed by `php artisan queue:work`. Never use `sync` in production. |
| `REDIS_HOST` | Redis cache & queue host | Required if Redis | Staging / Prod | `10.0.2.15` | Keep behind VPC firewall. |
| `REDIS_PASSWORD` | Redis authentication password | Required if Redis | Staging / Prod | `secr3t_auth_t0k3n` | Enforce AUTH on Redis instances. |
| `REDIS_PORT` | Redis server port | Optional | Staging / Prod | `6379` | Standard Redis port. |

---

## 5. Mail & Outbound Notifications

| Variable Name | Purpose | Required / Optional | Environment | Example Placeholder | Security Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `MAIL_MAILER` | Mail driver (`smtp`, `ses`, `postmark`, `log`) | Required | All | `smtp` (or `log` in dev) | Use transactional email providers for incident alerting. |
| `MAIL_HOST` | SMTP relay server | Required if SMTP | All | `smtp.postmarkapp.com` | Relayed over TLS port 587 or 465. |
| `MAIL_PORT` | SMTP port | Required if SMTP | All | `587` | Use 587 (STARTTLS). |
| `MAIL_USERNAME` | SMTP account username / API token | Required if SMTP | All | `postmark-api-token` | Store securely. |
| `MAIL_PASSWORD` | SMTP account password | Required if SMTP | All | `postmark-api-key` | Store securely. |
| `MAIL_ENCRYPTION` | Transport layer encryption | Required if SMTP | All | `tls` | Enforce TLS 1.2+. |
| `MAIL_FROM_ADDRESS` | Sender email address | Required | All | `alerts@opsora.io` | Ensure SPF and DKIM DNS records match this sender. |
| `MAIL_FROM_NAME` | Sender display name | Required | All | `"Opsora SRE Dispatch"` | Human-readable system name. |

---

## 6. Storage & Object Storage

| Variable Name | Purpose | Required / Optional | Environment | Example Placeholder | Security Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `FILESYSTEM_DISK` | Storage disk for report exports and attachments | Required | All | `local` or `s3` | Use `s3` in multi-node container environments. |
| `AWS_ACCESS_KEY_ID` | IAM access key for S3 bucket | Optional | If using S3 | `AKIAIOSFODNN7EXAMPLE` | Restrict IAM policy to target S3 bucket prefix. |
| `AWS_SECRET_ACCESS_KEY` | IAM secret access key | Optional | If using S3 | `wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY` | Never commit. Rotate periodically. |
| `AWS_DEFAULT_REGION` | AWS region hosting S3 storage | Optional | If using S3 | `af-south-1` or `eu-west-1` | Select region complying with data residency requirements. |
| `AWS_BUCKET` | S3 bucket name | Optional | If using S3 | `opsora-sre-attachments` | Enforce SSE-S3 or SSE-KMS bucket encryption. |

---

## 7. Logging & Observability

| Variable Name | Purpose | Required / Optional | Environment | Example Placeholder | Security Notes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `LOG_CHANNEL` | Log destination (`stack`, `single`, `daily`, `stderr`, `syslog`) | Required | All | `stack` (or `stderr` for Docker) | Cloud-native deployments stream to `stderr`. |
| `LOG_STACK` | Comma-separated channels in stack | Optional | All | `daily` | Rotates logs daily to avoid disk exhaustion. |
| `LOG_LEVEL` | Minimum log severity to capture | Required | All | `info` (or `debug` in dev) | Avoid `debug` in production to prevent high volume and accidental data logging. |

---

## 8. Mobile Client Configuration (`npontu_sre_mobile`)

The Flutter mobile application reads its backend configuration through Dart environment defines passed during build time or set in runtime configuration:

| Variable / Define | Purpose | Default | Example | Notes |
| :--- | :--- | :--- | :--- | :--- |
| `--dart-define=API_BASE_URL` | Root URL for REST API v1 endpoints | `http://10.0.2.2:8000/api/v1` | `https://sre.opsora.io/api/v1` | `10.0.2.2` maps to localhost inside Android emulator. |
| `--dart-define=APP_ENV` | Mobile app environment mode | `development` | `production` | Enables release assertions and analytics flags. |
| `--dart-define=ENABLE_BIOMETRICS` | Hardware biometric authentication toggle | `true` | `true` | Fallback PIN/passphrase enabled if hardware unavailable. |

---

## 9. Verification & Health Check

To verify your configuration in any deployed environment:

```bash
# Verify environment config and cache status
php artisan about

# Verify database connection and migrations
php artisan migrate:status

# Test queue worker connectivity
php artisan queue:work --once

# Test mail dispatch
php artisan tinker --execute="Mail::raw('Opsora health probe', fn(\$m) => \$m->to('probe@example.com')->subject('Probe'));"
```
