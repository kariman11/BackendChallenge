# Backend Challenge (Laravel 11 + Docker)

## 🚀 Overview

This project implements the complete **Orthoplex Backend Development Challenge**, built with:

- **Laravel 11**
- **MySQL 8**
- **Redis**
- **Mailpit**
- **Docker Compose**
- **JWT Authentication**
- **2FA (TOTP)**
- **Magic Link Login**
- **RBAC with Roles & Permissions**
- **Organization Multi‑Tenancy**
- **Login Analytics**
- **Webhooks (Signed, Queued, Retry)**
- **GDPR Export + Delete Workflow**
- **Swagger/OpenAPI Documentation**

This README explains how to set up, run, and understand the structure of the project.

---

# 📦 1. Project Architecture

```
BackendChallenge/
│
├── app/
│   ├── Http/Controllers/Api/
│   ├── Models/
│   ├── Jobs/
│   ├── Services/
│   ├── Swagger/   <-- OpenAPI PHP Attribute Definitions
│   └── ...
│
├── docker/
│   ├── docker-compose.yml
│   ├── mysql/
│   ├── php/
│   └── nginx/
│
├── scripts/
│   └── bootstrap.sh   <-- One-command environment setup
│
├── routes/api.php
├── routes/swagger.php
├── config/l5-swagger.php
└── README.md
```

---

# 🐳 2. Docker Environment

This project includes a **complete Docker environment**:

- **PHP 8.3**
- **Nginx**
- **MySQL 8** (port 3307)
- **Redis**
- **Mailpit** (SMTP testing)
- **phpMyAdmin**

---

# ⚡ 3. One‑Command Setup

Run:

```bash
./scripts/bootstrap.sh
```

This script:

1. Starts Docker containers
2. Installs Composer dependencies
3. Generates app key
4. Runs migrations
5. Shows access URLs

After running:

| Service | URL |
|--------|-----|
| Laravel API | http://localhost:8001 |
| Swagger Docs | http://localhost:8001/docs |
| Mailpit | http://localhost:8025 |
| phpMyAdmin | http://localhost:8082 |

---

# 🔐 4. Authentication Features

### ✔ JWT Auth
### ✔ Email Verification (Required Before Login)
### ✔ Login Throttling + Lockout
### ✔ 2FA (TOTP)
### ✔ Magic Link Login
### ✔ Idempotency Keys

Magic links and email verification use Mailpit.

---

# 🧩 5. RBAC (Roles & Permissions)

Roles:

- **owner**
- **admin**
- **member**
- **auditor**

Permissions include:

- `users.invite`
- `users.read`
- `users.update`
- `users.delete`
- `analytics.read`

Middleware: `org.permission:*`

---

# 🏢 6. Multi-Tenancy: Organizations

- A user can belong to multiple organizations
- Each membership has a role
- Owners can invite users via email
- Invitations include accept tokens

Example route:

```
POST /api/orgs/{org}/add-member
```

Webhook fired:

```
organization.member_invited
```

---

# 📊 7. Login Analytics

Two tables:

- **login_events** (raw events)
- **login_daily** (aggregated)

On login:

1. Update user last_login_at + login_count
2. Queue RecordLoginEvent job
3. Fire webhook (`user.login`)

Nightly cron:

```
php artisan analytics:rollup-logins
```

Endpoints:

```
GET /api/users/top-logins
GET /api/users/inactive
```

---

# 🔄 8. Webhooks (Outbound)

Queued delivery with retry + HMAC:

Events:

- `user.login`
- `user.verified`
- `organization.created`
- `organization.member_invited`
- GDPR events

Implementation is in:

```
App\Services\WebhookService
App\Jobs\SendWebhook
```

---

# 🛡 9. GDPR Features

### ✔ User Export (ZIP of JSON files)
Generated asynchronously.  
Download available once via token.

### ✔ GDPR Delete Request
Owner/admin must approve:

```
POST /api/users/gdpr/{id}/approve
POST /api/users/gdpr/{id}/reject
```

---

# 📘 10. Swagger / OpenAPI Documentation

Generated via PHP attributes:

```
app/Swagger/*
```

Generate:

```
php artisan l5-swagger:generate
```

Open Documentation:

```
http://localhost:8001/docs
```

---

# 🧪 11. Testing the API

Import into Postman:

1. Register user
2. Verify email (Mailpit)
3. Login
4. Test 2FA
5. Create organization
6. Invite member
7. Trigger webhooks
8. Trigger analytics
9. Use magic link login

---

# 📝 12. Environment Variables

Update `.env`:

```
APP_ENV=local
APP_KEY=
APP_URL=http://localhost:8001

DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=orthoplex
DB_USERNAME=laravel
DB_PASSWORD=secret

MAIL_HOST=mailpit
MAIL_PORT=1025
```

---
