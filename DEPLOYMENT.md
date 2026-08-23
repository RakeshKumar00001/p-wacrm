# Production Deployment Guide: WhatsApp CRM on aaPanel (OpenLiteSpeed) + Supabase

This guide provides step-by-step instructions for deploying your **WhatsApp CRM (Laravel + Livewire)** application live using **aaPanel with OpenLiteSpeed (OLS)** server and **Supabase PostgreSQL Database**.

---

## Phase 1: Supabase Database Setup & Credentials

### 1. Retrieve Connection Parameters from Supabase
1. Log in to your [Supabase Dashboard](https://supabase.com/dashboard).
2. Select your project (or create a new project).
3. Go to **Project Settings** -> **Database**.
4. Scroll to **Connection Parameters** or **Connection Pooling**:
   - **Host**: `db.xxxxxxxxxxxxxxxxxxxx.supabase.co` (or pooler address like `aws-0-xxxx.pooler.supabase.com`)
   - **Port**: `5432` (Direct connection) or `6543` (Transaction Pooler)
   - **Database Name**: `postgres`
   - **User**: `postgres` (or `postgres.projectref` if using pooler)
   - **Password**: *[Your Database Password set during project creation]*
   - **SSL**: Required (`DB_SSLMODE=require`)

---

## Phase 2: aaPanel & OpenLiteSpeed Server Configuration

### 1. PHP Version & Required Extensions
1. In aaPanel, go to **App Store** -> **PHP 8.2** (or PHP 8.3).
2. Click **Setting** for PHP:
   - Go to **Install Extensions** and install:
     - `pgsql` / `pdo_pgsql` *(Required for Supabase PostgreSQL)*
     - `fileinfo`
     - `redis` *(Optional: if using Redis for queues/cache)*
     - `curl`, `openssl`, `mbstring`, `exif`
   - Go to **Disabled Functions**:
     - Remove `proc_open`, `symlink`, `putenv`, and `pcntl_signal` from the list so Laravel artisan commands, queues, and symlinks work smoothly.
3. Restart PHP service in aaPanel.

---

### 2. Create Web Site in aaPanel
1. Go to **Website** -> **Add Site**.
2. Fill in the parameters:
   - **Domain**: `your-domain.com` (and `www.your-domain.com`)
   - **PHP Version**: Select `PHP-8.2` (or `PHP-8.3`)
   - **Database**: Select *Do not create* (since we use Supabase)
3. Click **Submit**.

---

### 3. Deploy Code & Directory Permissions
1. Upload your project code to `/www/wwwroot/your-domain.com`.
2. Open terminal in aaPanel or connect via SSH and navigate to the site directory:
   ```bash
   cd /www/wwwroot/your-domain.com
   ```
3. Set the correct file permissions and ownership:
   ```bash
   chown -R www:www /www/wwwroot/your-domain.com
   chmod -R 775 /www/wwwroot/your-domain.com/storage
   chmod -R 775 /www/wwwroot/your-domain.com/bootstrap/cache
   ```

---

### 4. Configure Site Document Root to `/public`
In Laravel, all publicly accessible assets and entry points live in the `public/` directory:
1. In aaPanel, go to **Website** -> Click on `your-domain.com` -> **Site directory**.
2. Change **Running directory** to `/public`.
3. Click **Save**.

---

### 5. OpenLiteSpeed Rewrite Rules & SSL Certificate

#### A. Rewrite Rules
OpenLiteSpeed supports Apache `.htaccess` rules automatically.
The repository now includes `public/.htaccess`.
In aaPanel -> Website Settings -> **URL Rewrite**, select **laravel** template (or leave default if reading `.htaccess`), then click **Save**.

#### B. Free SSL (Let's Encrypt)
1. Go to Website Settings -> **SSL** tab.
2. Select **Let's Encrypt**.
3. Select your domain names and click **Apply**.
4. Turn on **Force HTTPS**.

---

## Phase 3: Environment & Database Migration

### 1. Configure `.env` on Server
Create or edit `/www/wwwroot/your-domain.com/.env`:

```env
APP_NAME="WhatsApp CRM"
APP_ENV=production
APP_KEY=base64:w3V/e4m9y8q1K2L3M4N5O6P7Q8R9S0T1U2V3W4X5Y6Z=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://your-domain.com

# Database Connection to Supabase PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password
DB_SSLMODE=require

# Queue & Cache Setup
SESSION_DRIVER=file
CACHE_STORE=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=database

# Meta WhatsApp Webhook Verification Token
META_VERIFY_TOKEN=wacrm_secret_verify_token_2026

# Optional API Keys
OPENAI_API_KEY=
META_PIXEL_ID=
META_CAPI_TOKEN=
```

---

### 2. Run Database Migrations to Supabase
Run the migrations to create all required tables in Supabase:

```bash
php artisan migrate --force
```

---

### 3. Create Storage Symlink & Enable Caching

```bash
# Link public storage directory
php artisan storage:link

# Cache config, routes, views, and events
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Phase 4: Background Queue Worker & Scheduler in aaPanel

### 1. Setup Queue Worker with Supervisor
1. In aaPanel, go to **App Store** -> Search for **SupervisorManager** -> Install it.
2. Open **SupervisorManager** -> Click **Add Daemon**:
   - **Name**: `wacrm-worker`
   - **User**: `www`
   - **Run Directory**: `/www/wwwroot/your-domain.com`
   - **Start Command**: `php artisan queue:work --tries=3 --timeout=90`
   - **Processes**: `1`
3. Click **Confirm** and verify status is running.

---

### 2. Setup Laravel Cron Scheduler
1. In aaPanel, go to **Cron** (left sidebar).
2. Add a new Cron job:
   - **Type**: `Shell Script`
   - **Name**: `WACRM Scheduler`
   - **Execution Cycle**: `N Minutes` -> `1 Minute`
   - **Script Content**:
     ```bash
     cd /www/wwwroot/your-domain.com && php artisan schedule:run >> /dev/null 2>&1
     ```
3. Click **Add Task**.
