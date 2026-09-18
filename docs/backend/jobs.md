# Opsora Backend — Jobs, Queues & Scheduled Tasks

> **Status:** IMPLEMENTED  
> **Queue Driver:** Database (`jobs`, `failed_jobs`)

---

## 1. Scheduled Tasks (`routes/console.php`)
- **Daily EOD Report (23:59 UTC)**: `reports:send-automated --period=daily` aggregates daily checklist resolutions and signed shift briefings.
- **Weekly Digest (Sunday 23:59 UTC)**: `reports:send-automated --period=weekly` dispatches 7-day operational trends to subscribed team leads.
- **Monthly SLA Summary (28th 23:59 UTC)**: `reports:send-automated --period=monthly` aggregates uptime achievement and audit log volume.

---

## 2. Notification Pipeline & Queues
- **Email Notifications**: Powered by `App\Mail\AutomatedActivityReportMail` and `App\Mail\SecurityAlertMail`.
- **Database Notifications**: Stored in `operational_notifications` with `workspace_id`, read status, and action links.
- **Inbound Email Webhook**: `POST /api/webhooks/inbound-email` extracts reply tokens and writes direct messages into operational chat threads.
