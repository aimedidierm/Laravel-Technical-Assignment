# Employee Management API

A RESTful API for employee and attendance management, built with Laravel 12, Sanctum, and Docker via Laravel Sail.

## Tech Stack

- **PHP 8.5** / **Laravel 12**
- **Laravel Sail** — Docker-based local development (mandatory)
- **MySQL 8.4** — Database
- **Laravel Sanctum** — Stateless token-based authentication
- **maatwebsite/excel** — Excel report generation
- **barryvdh/laravel-snappy** — PDF report generation
- **Mailpit** — Local email capture (UI at port 8025)
- **OpenAPI v3** — Generated from PHP 8 attributes

## Setup (Using Laravel Sail)

```bash
# Clone and install
git clone <repo-url> && cd employee-management-api
composer install

# Environment
cp .env.example .env

# Start Sail (Docker)
./vendor/bin/sail up -d

# Generate app key and run migrations
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate

# Run queue worker (for email notifications)
./vendor/bin/sail artisan queue:listen
```

### Sail Alias (Recommended)

Add this to your `~/.bashrc` or `~/.zshrc`:

```bash
alias sail='./vendor/bin/sail'
```

Then use: `sail up`, `sail artisan migrate`, `sail test`, etc.

## Services

| Service | URL |
|---------|-----|
| API | http://localhost |
| Mailpit UI | http://localhost:8025 |
| MySQL | localhost:3306 |

## API Endpoints

### Authentication (public)

| Method | Endpoint                    | Description            |
|--------|-----------------------------|------------------------|
| POST   | `/api/auth/register`        | Register               |
| POST   | `/api/auth/login`           | Login                  |
| POST   | `/api/auth/password/forgot` | Request password reset |
| POST   | `/api/auth/password/reset`  | Reset password         |

### Authenticated (`Bearer` token required)

| Method | Endpoint                                  | Description           |
|--------|-------------------------------------------|-----------------------|
| POST   | `/api/auth/logout`                        | Logout                |
| GET    | `/api/employees`                          | List employees        |
| POST   | `/api/employees`                          | Create employee       |
| GET    | `/api/employees/{id}`                     | Get employee          |
| PUT    | `/api/employees/{id}`                     | Update employee       |
| DELETE | `/api/employees/{id}`                     | Delete employee       |
| GET    | `/api/attendance`                         | List attendance       |
| POST   | `/api/attendance/check-in`                | Record check-in       |
| POST   | `/api/attendance/check-out`               | Record check-out      |
| GET    | `/api/attendance/export/excel/{date}`     | Export Excel report   |
| GET    | `/api/attendance/export/pdf/{date}`       | Export PDF report     |

## OpenAPI Specification

Generate the spec from PHP 8 attributes:

```bash
./vendor/bin/sail artisan openapi:generate
```

Output: `docs/openapi.json`

## Running Tests

```bash
./vendor/bin/sail test
```

All 33 tests cover authentication, CRUD, attendance logic, email notifications, and exports.

## Stopping Sail

```bash
./vendor/bin/sail down
```
