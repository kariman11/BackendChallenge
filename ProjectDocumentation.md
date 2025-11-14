================================================================================
                     PROJECT – TECHNICAL DOCUMENTATION
================================================================================

OVERVIEW
========
This project is the full implementation of the ** Backend Challenge**, 
built using **Laravel 11**, **Docker**, **MySQL**, **Redis**, **Mailpit**, 
**JWT Authentication**, **TOTP 2FA**, **Magic Links**, **Multi-Organization**, 
**GDPR Export & Delete**, **Analytics**, and **Swagger/OpenAPI**.

The system provides a secure, multi-tenant, analytics-driven backend suitable
for SaaS products.

--------------------------------------------------------------------------------

TECH STACK
==========
- **Laravel 11**
- **PHP 8.3**
- **MySQL 8** (port 3307)
- **Redis** (queues, cache)
- **Nginx**
- **Mailpit** (email testing)
- **JWT Authentication**
- **Google2FA (TOTP)**
- **Swagger/OpenAPI (l5-swagger)**
- **Docker Compose**
- **Postman (API testing)**

--------------------------------------------------------------------------------

ARCHITECTURE
============
```
app/
 ├── Http/Controllers/Api/
 ├── Models/
 ├── Jobs/
 ├── Services/
 ├── Swagger/           ← OpenAPI PHP attribute definitions
 └── ...
docker/
scripts/bootstrap.sh     ← One-command setup
routes/api.php
routes/swagger.php
config/l5-swagger.php
```

--------------------------------------------------------------------------------

DOCKER ENVIRONMENT
===================
Run everything with:

```
./scripts/bootstrap.sh
```

This will:

1. Build & start Docker containers  
2. Install dependencies  
3. Generate APP_KEY  
4. Run migrations  
5. Start queue worker  

Services:
| Service | URL |
|--------|------|
| API | http://localhost:8001 |
| Swagger UI | http://localhost:8001/docs |
| Mailpit | http://localhost:8025 |
| phpMyAdmin | http://localhost:8082 |

--------------------------------------------------------------------------------

AUTHENTICATION FEATURES
=======================

### ✔ JWT Authentication
- Stateless API auth  
- `/api/login`, `/api/logout`, `/api/user`

### ✔ Email Verification
- Signed URL verification  
- Unverified users cannot log in  

### ✔ Two-Factor Authentication (TOTP)
Endpoints:
- `/api/2fa/setup`
- `/api/2fa/enable`
- `/api/2fa/disable`

Generates QR code + secret + backup codes.

### ✔ Magic Link Authentication
- `/api/magic` — request link  
- `/api/magic/consume/{token}` — authenticate  
- Tokens are single-use, expire in 15 minutes  

### ✔ Idempotency Keys
Added to critical operations (register, login).

--------------------------------------------------------------------------------

ORGANIZATION SYSTEM
====================

### ✔ Multi-Tenancy (User → Organizations)
- Users can belong to multiple orgs
- Each org membership has a **role**
- Permissions enforced via middleware `org.permission:*`

### ✔ Roles implemented
- owner  
- admin  
- member  
- auditor  

### ✔ Invitation Flow
- Create organization  
- Invite users via email (`add-member`)  
- Accept using token:  
  `/api/orgs/{org}/invites/accept/{token}`

--------------------------------------------------------------------------------

LOGIN ANALYTICS
================
Two tables:
- **login_events** – raw login event log  
- **login_daily** – aggregated rollup  

On login:
1. Update login_count + last_login_at  
2. Queue analytics job  
3. Fire webhook  

Endpoints:
- `/api/users/top-logins`
- `/api/users/inactive`

--------------------------------------------------------------------------------

GDPR COMPLIANCE
================

### ✔ Data Export
- `/api/users/{id}/export`  
Queued job creates ZIP containing user data.  
Download with one-time token.

### ✔ GDPR Delete Requests
- User requests deletion  
- Admin approves or rejects  

Endpoints:
```
POST /api/users/gdpr/request-delete
POST /api/users/gdpr/{id}/approve
POST /api/users/gdpr/{id}/reject
```

--------------------------------------------------------------------------------

WEBHOOK SYSTEM
===============
All major events trigger outgoing webhooks:

Events include:
- `user.login`
- `user.verified`
- `organization.created`
- `organization.member_invited`
- `gdpr.export.ready`
- `gdpr.delete.requested`

Webhooks are:
- Signed with HMAC  
- Delivered through queued job  
- Retried on failure  

--------------------------------------------------------------------------------

SWAGGER / OPENAPI DOCUMENTATION
================================
OpenAPI is defined using **PHP 8 attributes** under `app/Swagger/`.

Generate documentation:

```
php artisan l5-swagger:generate
```

View API docs:

```
http://localhost:8001/docs
```

--------------------------------------------------------------------------------

MAIN API ENDPOINTS
===================

### Authentication
- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET  /api/user`

### Email
- `GET /api/email/verify/{id}/{hash}`
- `POST /api/email/resend`

### 2FA
- `POST /api/2fa/setup`
- `POST /api/2fa/enable`
- `POST /api/2fa/disable`

### Magic Links
- `POST /api/magic`
- `GET  /api/magic/consume/{token}`

### Organizations
- `POST /api/orgs`
- `GET /api/orgs`
- `POST /api/orgs/{org}/add-member`
- `POST /api/orgs/{org}/invites/accept/{token}`

### Analytics
- `GET /api/users/top-logins`
- `GET /api/users/inactive`

### GDPR
- `POST /api/users/{id}/export`
- `GET /api/users/{id}/export/download`
- `POST /api/users/gdpr/request-delete`
- `POST /api/users/gdpr/{id}/approve`
- `POST /api/users/gdpr/{id}/reject`

### User Management
- `GET /api/users`
- Filterable using **RSQL-like syntax** (`filter=name==kar*`)
- Cursor pagination supported  

--------------------------------------------------------------------------------

DATABASE STRUCTURE (SUMMARY)
=============================

Core tables:
- users  
- organizations  
- roles  
- permissions  
- organization_user_roles  

Auth-related:
- magic_links  
- invitations  
- password_reset_tokens  
- sessions  

Analytics:
- login_events  
- login_daily  

GDPR:
- gdpr_exports  
- gdpr_delete_requests  

--------------------------------------------------------------------------------

SECURITY MEASURES
==================

- JWT-based authentication  
- Email verification required  
- TOTP 2FA  
- Magic link one-time tokens  
- Idempotency keys  
- Rate-limited sensitive endpoints  
- Signed webhooks  
- Soft deletes  
- Role & permission checks  
- Validation on all inputs  

--------------------------------------------------------------------------------

DOCKER QUICK START
===================
```
./scripts/bootstrap.sh
```

After startup:

| Service | URL |
|--------|------|
| API | http://localhost:8001 |
| Swagger | http://localhost:8001/docs |
| Mailpit | http://localhost:8025 |
| phpMyAdmin | http://localhost:8082 |

--------------------------------------------------------------------------------

AUTHOR
======
**Kariman Nasr**  
Full Stack Engineer — Laravel / React  
Egypt  

--------------------------------------------------------------------------------

END OF DOCUMENT
================================================================================

