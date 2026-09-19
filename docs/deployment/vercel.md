# Deploying Opsora SRE on Vercel (Serverless PHP)

> **Platform**: Vercel Serverless Platform  
> **Runtime**: `vercel-php@0.7.3` Serverless Runtime  
> **Configuration**: [`vercel.json`](file:///c:/Users/user/Desktop/npontu-technologies-sre/vercel.json)  
> **Serverless Bridge**: [`api/index.php`](file:///c:/Users/user/Desktop/npontu-technologies-sre/api/index.php)

---

## 1. Serverless Architecture on Vercel

Vercel is an edge serverless cloud. Running Laravel on Vercel requires adapting to a **stateless, read-only filesystem**:

```mermaid
flowchart LR
    ClientRequests(("User Browser / Mobile")) --> VercelEdge["Vercel Edge Network / CDN"]
    
    subgraph StaticAssets["Edge CDN Cache"]
        ViteBuild["/public/build/* (CSS, JS)"]
        Images["/public/images/* (SVGs, Icons)"]
    end
    
    subgraph ServerlessLambda["Vercel Serverless Function (PHP 8.2)"]
        Bridge["api/index.php<br/>(/tmp Ephemeral Storage Prep)"]
        LaravelApp["Laravel Front Controller<br/>(public/index.php)"]
    end
    
    subgraph ExternalData["Cloud Managed Database"]
        Postgres[("Neon / Supabase / AWS RDS<br/>(PostgreSQL or MySQL)")]
    end

    VercelEdge -- "Static Assets" --> StaticAssets
    VercelEdge -- "Dynamic Routes" --> Bridge
    Bridge --> LaravelApp
    LaravelApp --> Postgres
```

### Storage Adaptations:
1. **Ephemeral Filesystem (`/tmp`)**: Vercel functions cannot write to `storage/` in the project root. The [`api/index.php`](file:///c:/Users/user/Desktop/npontu-technologies-sre/api/index.php) bridge dynamically redirects `VIEW_COMPILED_PATH` and cache stores to `/tmp`.
2. **Session Driver**: Must be configured to `cookie` or `database` so user authentication persists across stateless serverless invocations.
3. **Database**: Requires an external cloud database accessible over the internet (e.g., [Neon](https://neon.tech), [Supabase](https://supabase.com), [PlanetScale](https://planetscale.com), or AWS RDS).

---

## 2. Fast-Track Deployment Steps

### Option A: Deploy via GitHub Integration (Recommended)
1. Push your latest code to GitHub:
   ```bash
   git push origin feat/opsora-saas
   ```
2. Log in to [Vercel Dashboard](https://vercel.com).
3. Click **Add New... &rarr; Project**.
4. Import your GitHub repository (`npontu-technologies-sre` or `opsora-sre`).
5. In **Framework Preset**, leave as **Other**.
6. Expand **Environment Variables** and configure the required keys (see Section 3 below).
7. Click **Deploy**.

---

### Option B: Deploy via Vercel CLI
If you have `vercel` CLI installed locally:
```bash
# 1. Login to Vercel
vercel login

# 2. Deploy preview
vercel

# 3. Deploy to production
vercel --prod
```

---

## 3. Required Vercel Environment Variables

Set these in **Project Settings &rarr; Environment Variables** on Vercel:

| Variable | Recommended Value | Notes |
|---|---|---|
| `APP_NAME` | `Opsora SRE` | Application brand |
| `APP_ENV` | `production` | Production environment |
| `APP_DEBUG` | `false` | Security enforcement |
| `APP_KEY` | `base64:...` | Generate locally with `php artisan key:generate --show` |
| `APP_URL` | `https://your-project.vercel.app` | Vercel production domain |
| `DB_CONNECTION` | `pgsql` (or `mysql`) | Database driver |
| `DATABASE_URL` | `postgres://user:pass@host:5432/db?sslmode=require` | Connection string to Neon / Supabase / PlanetScale |
| `SESSION_DRIVER` | `cookie` | Required for stateless serverless functions |
| `CACHE_STORE` | `array` (or `database`) | Avoid local file cache |
| `LOG_CHANNEL` | `stderr` | Sends application logs to Vercel Function Logs |

---

## 4. Running Migrations for Vercel

Because Vercel serverless functions do not provide an interactive persistent SSH shell, run database migrations against your cloud database prior to traffic cutover using one of these methods:

### Method 1: Local Machine targeting Remote DB
```bash
# Point your local environment to the remote production database
DATABASE_URL="postgres://user:pass@host:5432/db?sslmode=require" php artisan migrate --force
DATABASE_URL="postgres://user:pass@host:5432/db?sslmode=require" php artisan db:seed --force
```

### Method 2: GitHub Actions CI/CD Pipeline
Use a GitHub Actions workflow that executes `php artisan migrate --force` upon merging to production.

---

## 5. Serverless Considerations & Best Practices

- **File Uploads**: Serverless lambdas cannot store persistent uploaded files on disk. Use S3, Cloudinary, or Supabase Storage by setting `FILESYSTEM_DISK=s3`.
- **Scheduled Tasks (Cron)**: Use [Vercel Cron Jobs](https://vercel.com/docs/cron-jobs) or a GitHub Actions scheduled workflow to trigger scheduled SRE reports.
- **Cold Starts**: Vercel keeps functions warm automatically on active production domains.
