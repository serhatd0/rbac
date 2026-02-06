# Pure PHP JWT RBAC Starter

A production-ready, framework-less PHP starter kit featuring JWT Authentication, Refresh Tokens, and Role-Based Access Control (RBAC). Built with pure PHP 8.2+, Docker, and Strict Types.

## What is this?

This is a clean-slate architecture pattern for building REST APIs in PHP without the overhead of massive frameworks (Laravel, Symfony). It demonstrates how to implement modern security features (JWT, Rotation, RBAC) from scratch using SOLID principles and PSR standards.

## Features

- **Pure PHP 8.2+**: Strict types enabled (`declare(strict_types=1)`).
- **JWT Authentication**: HS256 signed access tokens (short-lived).
- **Refresh Tokens**: Database-backed, hashed (SHA-256), revocable, 30-day expiry.
- **RBAC**: Database-driven Roles and Permissions.
- **Security**: 
  - `password_hash` (Bcrypt)
  - Rate Limiting (Login endpoint)
  - Input Validation
  - JSON Error Responses
  - Secure Headers & CORS
- **DevOps**: Docker Compose setup (Nginx + PHP-FPM + MySQL) & GitHub Actions (CI).

## Project Structure

```
src/
  Auth/       # Auth logic (JWT, Services)
  Config/     # Environment loading
  Database/   # PDO connection
  Http/       # Middleware, Request, Response, Router
  Rbac/       # RBAC Repository & Controllers
  User/       # User domain logic
public/       # Entry point
tests/        # PHPUnit tests
docker/       # Configs
```

## Quick Start (Docker)

1. **Clone & Setup**:
   ```bash
   git clone https://github.com/serhatd0/rbac.git
   cd rbac
   cp .env.example .env
   ```

2. **Start Docker**:
   ```bash
   docker compose up -d --build
   ```

3. **Install Dependencies**:
   ```bash
   docker compose exec app composer install
   ```

4. **Initialize Database**:
   The `docker-entrypoint-initdb.d` script runs automatically on the first container start, importing `database/schema.sql` and `database/seed.sql`.
   
   *Wait a few seconds for MySQL to initialize.*

## API Examples (curl)

**1. Register a new user**
```bash
curl -X POST http://localhost:8080/auth/register \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'
```

**2. Login (Get Tokens)**
```bash
# Save tokens from response
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'
```

**3. Access Protected Route (/me)**
```bash
curl -H "Authorization: Bearer <ACCESS_TOKEN>" http://localhost:8080/me
```

**4. Refresh Token**
```bash
curl -X POST http://localhost:8080/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{"refreshToken": "<REFRESH_TOKEN>"}'
```

**5. Admin: List Users (Requires Admin Token)**
*Use the seeded admin: `admin@example.com` / `secret123`*
```bash
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "secret123"}' 
# Use the returned Admin Access Token below:
```

```bash
curl -H "Authorization: Bearer <ADMIN_ACCESS_TOKEN>" http://localhost:8080/admin/users
```

**6. Admin: Assign Role**
```bash
curl -X POST http://localhost:8080/admin/users/1/roles \
  -H "Authorization: Bearer <ADMIN_ACCESS_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"roles": ["admin"]}'
```

## Security Notes

1. **JWT Secret**: Change `JWT_SECRET` in `.env`.
2. **Refresh Tokens**: Plain tokens are never stored. Only their SHA-256 hash is in the DB.
3. **Passwords**: Uses PHP's native `password_hash` (Bcrypt by default).

## Design Decisions

- **No Framework**: To demonstrate core understanding of HTTP lifecycles and Auth flows.
- **Dependency Injection**: Classes receive dependencies via constructor, making testing easy.
- **Minimal Dependencies**: Only `composer` for autoloading and `phpunit` for testing.
