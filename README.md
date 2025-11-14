
# Backend Challenge
### Laravel 11 • Docker • JWT • 2FA • Magic Link • RBAC • Webhooks • GDPR • Swagger

---

<p align="center">
  <img src="https://dummyimage.com/1200x250/000/ffffff&text=Backend+Challenge" />
</p>

---

## ✨ Overview

This repository contains the full backend implementation for the **Orthoplex Backend Challenge**, built with:

- Laravel **11**
- MySQL **8**, Redis, Nginx
- Docker + Docker Compose
- JWT Authentication
- 2FA (TOTP)
- Magic Link Login
- Multi‑Tenancy (Organizations)
- Role‑Based Access Control (RBAC)
- Webhooks (signed + queued)
- Login Analytics (rollup + caching)
- GDPR Export & Delete Workflow
- Swagger/OpenAPI Documentation (PHP Attributes)
- Idempotency Middleware
- Queue Workers

The project is fully containerized and ready to run with **one bootstrap command**.

---

## 🏗 Project Structure

```
BackendChallenge/
│
├── app/
│   ├── Http/Controllers/Api/
│   ├── Models/
│   ├── Jobs/
│   ├── Services/
│   ├── Swagger/          # → OpenAPI Attribute Classes
│   └── ...
│
├── docker/
│   ├── nginx/
│   ├── php/
│   ├── mysql/
│   └── docker-compose.yml
│
├── scripts/
│   └── bootstrap.sh      # → One-command setup
│
├── routes/
│   ├── api.php
│   └── swagger.php
│
├── config/l5-swagger.php
└── README.md
```

---

## 🐳 Docker Environment

This project includes:

| Service | Description | URL |
|--------|-------------|-----|
| **API** | Laravel 11 | http://localhost:8001 |
| **Docs** | Swagger UI | http://localhost:8001/docs |
| **Mailpit** | Email testing | http://localhost:8025 |
| **phpMyAdmin** | DB UI | http://localhost:8082 |
| **MySQL** | 3307 (local) | docker internal: `mysql:3306` |
| **Redis** | Queue/Cache | Docker internal |

---

## ⚡ One‑Command Setup

Run:

```bash
./scripts/bootstrap.sh
```

This will:

1. Build & start Docker containers
2. Install composer dependencies
3. Run migrations
4. Generate app key
5. Show URLs

---

## 🔐 Authentication Features

✔ Login with JWT  
✔ Email verification (required to login)  
✔ 2FA (Google Authenticator TOTP)  
✔ Magic-link login via email  
✔ Login throttling  
✔ Idempotency-Key middleware

---

## 🏢 Organizations (Multi‑Tenant)

- Users can belong to multiple organizations
- Each membership has a **role**
- Enforced using `org.permission:*` middleware
- Webhooks sent on org events

---

## 🧩 RBAC (Roles & Permissions)

Included roles:

- **owner**
- **admin**
- **member**
- **auditor**

Permissions include:

- `users.invite`
- `users.read`
- `users.delete`
- `users.update`
- `analytics.read`

---

## 📊 Login Analytics

Two tables:

- `login_events`
- `login_daily` (rolled up)

Command:

```bash
php artisan analytics:rollup-logins
```

Endpoints:

```
GET /api/users/top-logins
GET /api/users/inactive
```

---

## 🔄 Webhooks

Webhook events fired:

- `user.login`
- `organization.created`
- `organization.member_invited`
- `gdpr.export.ready`
- `gdpr.delete.approved`

Webhooks are:

- Signed via HMAC SHA‑256
- Queued
- Retried automatically

---

## 🛡 GDPR Features

### User Export
- Asynchronously packaged ZIP
- One‑time download token

### Delete Request Workflow
- Member submits request
- Admin/Owner approves/rejects
- Delete job queued

---

## 📘 Swagger / OpenAPI Documentation

Generated using **PHP Attributes** (OpenAPI 3.1).

Generate docs:

```bash
php artisan l5-swagger:generate
```

URL:

```
http://localhost:8001/docs
```

---

## 🧪 Postman Collection

The repository includes a full **Postman collection** covering:

- Registration
- Login
- 2FA setup/enable/disable
- Magic link login
- Org creation/invite/accept
- Analytics endpoints
- GDPR features
- User exports

---

## 🔧 Environment Variables

```
APP_URL=http://localhost:8001

DB_HOST=mysql
DB_PORT=3306
DB_USERNAME=laravel
DB_PASSWORD=secret

MAIL_HOST=mailpit
MAIL_PORT=1025
```

---

## 👤 Author

**Kariman Nasr**  
Full Stack Engineer  
📌 Based in Cairo, Egypt  
💼 Specialized in Laravel, React, Multi‑Tenant SaaS, Complex ERP Modules

---

## ⭐ If this project helped you
Feel free to star ⭐ the repo — it means a lot!

---
