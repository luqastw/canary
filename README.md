# Canary - Feature Flags Service

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)
![Redis](https://img.shields.io/badge/Redis-6.0%2B-DC382D?logo=redis&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?logo=tailwind-css&logoColor=white)

> Multi-tenant feature flag management system built with Laravel 13

A production-ready, enterprise-grade feature flag service with complete multi-tenancy support. Provides a REST API and modern web dashboard for managing feature flags with role-based targeting, Redis caching for sub-50ms evaluation performance, and complete tenant isolation.

## Features

### Core Capabilities

- **Multi-Tenancy**: Complete tenant isolation with automatic scoping at database level
- **Feature Flags**: Create, update, toggle, and delete feature flags per tenant
- **Targeting Rules**: Enable flags for specific user groups/roles
- **Fast Evaluation**: Redis-cached flag evaluation with <50ms p95 latency
- **REST API**: Full RESTful API with Sanctum authentication
- **Web Dashboard**: Modern admin panel built with Blade, Alpine.js, and Tailwind CSS 4

### Technical Highlights

- **Laravel 13**: Built on the latest Laravel framework
- **Repository Pattern**: Clean architecture with interfaces and dependency injection
- **Tenant Scope**: Automatic query filtering using Laravel's Global Scopes
- **API Resources**: Consistent JSON responses with Laravel Resources
- **Form Validation**: Comprehensive request validation with custom rules
- **Fail-Safe**: Evaluation returns `false` for non-existent flags (never breaks your app)
- **Soft Deletes**: Flags and targeting rules support soft deletion
- **Transaction Support**: Atomic operations for data consistency

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Usage](#usage)
  - [REST API](#rest-api)
  - [Web Dashboard](#web-dashboard)
  - [Client Integration Examples](#client-integration-examples)
- [Architecture](#architecture)
- [Performance](#performance)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Development](#development)
- [Roadmap](#roadmap)

## Installation

### Requirements

- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.3+
- Redis 6.0+
- Composer 2.x
- Node.js 18+ & NPM

### Setup

1. **Clone the repository**

```bash
git clone https://github.com/luqastw/canary
cd canary
```

1. **Install dependencies**

```bash
composer install
npm install
```

1. **Configure environment**

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database and Redis credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=feature_flags
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_STORE=redis
```

1. **Start database (if using Docker)**

```bash
docker run -d --name feature-flags-db \
  -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
  -e MYSQL_DATABASE=feature_flags \
  -p 3306:3306 \
  mariadb:latest
```

1. **Run migrations and seed demo data**

```bash
php artisan migrate:fresh --seed
```

This creates a demo tenant with:

- **Login**: `admin@demo.com` / `password`
- 5 sample flags
- 3 user groups
- Pre-configured targeting rules

1. **Build frontend assets**

```bash
npm run build
```

1. **Start the server**

```bash
php artisan serve
```

Visit <http://localhost:8000> and login with the demo credentials.

## Quick Start

After installation, here's a 2-minute workflow to get started:

```bash
# 1. Login to get API token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@demo.com", "password": "password"}'

# Save the token from response
export TOKEN="your-token-here"

# 2. List existing flags
curl http://localhost:8000/api/v1/flags \
  -H "Authorization: Bearer $TOKEN"

# 3. Evaluate a flag (check the demo seeder for available flags)
curl -X POST http://localhost:8000/api/v1/evaluate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "flag_key": "dark-mode",
    "context": {"user_id": "user-1", "role": "admin"}
  }'

# 4. Or use the web dashboard at http://localhost:8000
```

## Usage

### REST API

#### Authentication

**Register a new tenant:**

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Acme Inc",
    "email": "admin@acme.com",
    "password": "secure-password",
    "password_confirmation": "secure-password"
  }'
```

Response:

```json
{
    "tenant": {
        "id": 1,
        "name": "Acme Inc",
        "email": "admin@acme.com",
        "status": "active"
    },
    "token": "1|abc123..."
}
```

**Login:**

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@acme.com",
    "password": "secure-password"
  }'
```

#### Flags Management

All subsequent requests require the `Authorization: Bearer {token}` header.

**Create a flag:**

```bash
curl -X POST http://localhost:8000/api/v1/flags \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "key": "new-dashboard",
    "name": "New Dashboard",
    "description": "Redesigned dashboard experience",
    "is_enabled": true
  }'
```

**List all flags:**

```bash
curl http://localhost:8000/api/v1/flags \
  -H "Authorization: Bearer {token}"
```

**Toggle a flag:**

```bash
curl -X PATCH http://localhost:8000/api/v1/flags/{id}/toggle \
  -H "Authorization: Bearer {token}"
```

#### Groups

**Create a group:**

```bash
curl -X POST http://localhost:8000/api/v1/groups \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "beta-testers",
    "name": "Beta Testers",
    "description": "Users in beta program"
  }'
```

#### Targeting Rules

**Add groups to a flag:**

```bash
curl -X POST http://localhost:8000/api/v1/targeting \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "flag_id": 1,
    "group_ids": [1, 2]
  }'
```

#### Flag Evaluation (The Important Part!)

**Evaluate a single flag:**

```bash
curl -X POST http://localhost:8000/api/v1/evaluate \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "flag_key": "new-dashboard",
    "context": {
      "user_id": "user-123",
      "role": "beta-tester"
    }
  }'
```

Response:

```json
{
    "data": {
        "enabled": true,
        "reason": "targeting",
        "variant": null
    }
}
```

**Batch evaluation (multiple flags at once):**

```bash
curl -X POST http://localhost:8000/api/v1/evaluate/batch \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "flag_keys": ["new-dashboard", "dark-mode", "ai-features"],
    "context": {
      "user_id": "user-123",
      "role": "beta-tester"
    }
  }'
```

Response:

```json
{
    "data": {
        "new-dashboard": {
            "enabled": true,
            "reason": "targeting"
        },
        "dark-mode": {
            "enabled": true,
            "reason": "global"
        },
        "ai-features": {
            "enabled": false,
            "reason": "global"
        }
    }
}
```

### Web Dashboard

The web dashboard provides a user-friendly interface for managing flags, groups, and targeting rules.

**Access:** <http://localhost:8000>

#### Key Features

1. **Dashboard**: Overview with metrics (total flags, active flags, groups)
2. **Flags Management**:
    - List all flags with inline toggle
    - Create/Edit/Delete flags
    - View flag details with API usage examples
    - Manage targeting rules per flag
3. **Groups Management**: CRUD operations for user groups
4. **Targeting Interface**: Visual drag-and-drop style interface for assigning groups to flags

### Client Integration Examples

Here's how to integrate Canary into your applications:

#### PHP/Laravel Application

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FeatureFlagService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.canary.url');
        $this->token = config('services.canary.token');
    }

    public function isEnabled(string $flagKey, array $context): bool
    {
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/api/v1/evaluate", [
                'flag_key' => $flagKey,
                'context' => $context,
            ]);

        return $response->successful()
            ? $response->json('data.enabled', false)
            : false; // Fail-safe
    }

    public function evaluateBatch(array $flagKeys, array $context): array
    {
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/api/v1/evaluate/batch", [
                'flag_keys' => $flagKeys,
                'context' => $context,
            ]);

        return $response->successful()
            ? $response->json('data', [])
            : [];
    }
}

// Usage in your controller
if ($featureFlags->isEnabled('new-dashboard', ['user_id' => $userId, 'role' => $userRole])) {
    return view('dashboard.new');
}
return view('dashboard.old');
```

#### JavaScript/Node.js Application

```javascript
// featureFlags.js
const axios = require("axios");

class FeatureFlagService {
    constructor(baseUrl, token) {
        this.client = axios.create({
            baseURL: baseUrl,
            headers: { Authorization: `Bearer ${token}` },
        });
    }

    async isEnabled(flagKey, context) {
        try {
            const { data } = await this.client.post("/api/v1/evaluate", {
                flag_key: flagKey,
                context: context,
            });
            return data.data.enabled;
        } catch (error) {
            console.error("Flag evaluation failed:", error);
            return false; // Fail-safe
        }
    }

    async evaluateBatch(flagKeys, context) {
        try {
            const { data } = await this.client.post("/api/v1/evaluate/batch", {
                flag_keys: flagKeys,
                context: context,
            });
            return data.data;
        } catch (error) {
            console.error("Batch evaluation failed:", error);
            return {};
        }
    }
}

// Usage in Express.js
const flags = new FeatureFlagService(
    process.env.CANARY_URL,
    process.env.CANARY_TOKEN,
);

app.get("/dashboard", async (req, res) => {
    const enabled = await flags.isEnabled("new-dashboard", {
        user_id: req.user.id,
        role: req.user.role,
    });

    res.render(enabled ? "dashboard-new" : "dashboard-old");
});
```

#### Python Application

```python
# feature_flags.py
import requests
from typing import Dict, List, Any

class FeatureFlagService:
    def __init__(self, base_url: str, token: str):
        self.base_url = base_url
        self.headers = {"Authorization": f"Bearer {token}"}

    def is_enabled(self, flag_key: str, context: Dict[str, str]) -> bool:
        try:
            response = requests.post(
                f"{self.base_url}/api/v1/evaluate",
                json={"flag_key": flag_key, "context": context},
                headers=self.headers
            )
            return response.json()["data"]["enabled"]
        except Exception as e:
            print(f"Flag evaluation failed: {e}")
            return False  # Fail-safe

    def evaluate_batch(self, flag_keys: List[str], context: Dict[str, str]) -> Dict[str, Any]:
        try:
            response = requests.post(
                f"{self.base_url}/api/v1/evaluate/batch",
                json={"flag_keys": flag_keys, "context": context},
                headers=self.headers
            )
            return response.json()["data"]
        except Exception as e:
            print(f"Batch evaluation failed: {e}")
            return {}

# Usage in Flask
flags = FeatureFlagService(
    os.getenv("CANARY_URL"),
    os.getenv("CANARY_TOKEN")
)

@app.route('/dashboard')
def dashboard():
    enabled = flags.is_enabled('new-dashboard', {
        'user_id': current_user.id,
        'role': current_user.role
    })
    return render_template('dashboard_new.html' if enabled else 'dashboard_old.html')
```

## Architecture

### Multi-Tenancy Strategy

The system uses **database-level tenant isolation** via Laravel Global Scopes:

```php
// Automatically applied to all queries for Flag model
#[ScopedBy([TenantScope::class])]
class Flag extends Model { ... }

// All queries automatically filtered by authenticated user's tenant_id
$flags = Flag::all(); // Only returns current tenant's flags
```

### Repository Pattern

Clean separation of concerns:

- **Contracts (Interfaces)**: Define the API contracts
- **Repositories**: Handle data access and queries
- **Services**: Implement business logic
- **Controllers**: Handle HTTP requests/responses

```
app/
├── Contracts/
│   ├── Repositories/     # Repository interfaces
│   └── Services/         # Service interfaces
├── Repositories/         # Data access implementations
├── Services/             # Business logic
├── Http/
│   ├── Controllers/      # API & Web controllers
│   ├── Requests/         # Form validation
│   └── Resources/        # API response formatting
└── Models/               # Eloquent models with relationships
```

### Evaluation Logic

Flag evaluation follows this priority:

1. **Flag exists?** → If not, return `false` (fail-safe)
2. **Has targeting rules?** → Check if user's role matches any assigned group
3. **No targeting rules?** → Return global `is_enabled` status

```php
// Pseudocode
if (!flag_exists) return false;
if (has_targeting_rules) {
    return user_role in assigned_groups;
}
return flag.is_enabled;
```

### Caching Strategy

- **Cache Key Format**: `flag:{tenant_id}:{flag_key}`
- **TTL**: 5 minutes
- **Invalidation**: Automatic on flag updates or targeting changes
- **Driver**: Redis (configurable)

## Performance

### Benchmarks

- **Evaluation endpoint**: <50ms p95 latency (with Redis cache)
- **Cache hit rate**: >80% after warm-up
- **Rate limits**:
  - Evaluation: 1000 req/min per token
  - Other API: 60 req/min per token

### Optimization Tips

1. **Use batch evaluation** when checking multiple flags
2. **Cache is your friend**: First evaluation is slower (cache miss), subsequent ones are fast
3. **Redis recommended**: In-memory cache for production
4. **Database indexes**: All critical columns are indexed (tenant_id, key, etc.)

## Testing

Run the test suite:

```bash
# All tests
php artisan test

# With coverage
php artisan test --coverage --min=80

# Specific test suite
php artisan test --testsuite=Feature
```

### Test Coverage

- **AuthTest**: Registration, login, logout, token validation
- **FlagTest**: CRUD operations, tenant isolation, toggle
- **EvaluationTest**: Targeting logic, batch evaluation, fail-safe behavior

## Troubleshooting

### Common Issues

#### 1. "Connection refused" when accessing database

**Problem**: Can't connect to MySQL/MariaDB

**Solution**:

```bash
# Check if database is running
docker ps | grep mariadb

# Start database if not running
docker start feature-flags-db

# Check credentials in .env match your setup
cat .env | grep DB_
```

#### 2. "Class 'Redis' not found" or cache errors

**Problem**: Redis extension not installed

**Solution**:

```bash
# Install Redis PHP extension
sudo pecl install redis
# or via package manager
sudo apt-get install php8.2-redis  # Debian/Ubuntu
sudo yum install php82-redis       # CentOS/RHEL

# Verify installation
php -m | grep redis

# Restart PHP-FPM if needed
sudo systemctl restart php8.2-fpm
```

#### 3. Assets not loading (404 on CSS/JS)

**Problem**: Frontend assets not compiled

**Solution**:

```bash
# Build assets
npm run build

# For development with hot reload
npm run dev
```

#### 4. "SQLSTATE[42S02]: Base table or view not found"

**Problem**: Database migrations not run

**Solution**:

```bash
# Run migrations
php artisan migrate

# Fresh install with demo data
php artisan migrate:fresh --seed
```

#### 5. API returns 401 Unauthorized

**Problem**: Invalid or expired token

**Solution**:

```bash
# Generate new token via login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@demo.com", "password": "password"}'

# Check token is being sent correctly
curl -v http://localhost:8000/api/v1/flags \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 6. Slow evaluation responses (>100ms)

**Problem**: Redis cache not working or not configured

**Solution**:

```bash
# Check Redis is running
redis-cli ping  # Should return "PONG"

# Verify .env configuration
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Clear config cache
php artisan config:clear
php artisan cache:clear

# Test cache manually
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');  // Should return 'value'
```

#### 7. Tests failing with "Database does not exist"

**Problem**: Test database not configured

**Solution**:

```bash
# Create test database
mysql -u root -e "CREATE DATABASE feature_flags_test;"

# Update .env.testing or phpunit.xml
DB_DATABASE=feature_flags_test

# Run migrations for test database
php artisan migrate --env=testing

# Run tests
php artisan test
```

#### 8. Multi-tenancy not working (seeing other tenant's data)

**Problem**: TenantScope not applied or middleware issue

**Solution**:

```php
// Verify model has TenantScope attribute
#[ScopedBy([TenantScope::class])]
class Flag extends Model { ... }

// Check authentication is working
dd(auth()->user()->tenant_id);

// Manually test scope
Flag::withoutGlobalScopes()->get(); // All flags (admin only)
Flag::all(); // Only current tenant's flags
```

### Getting Help

If you encounter issues not covered here:

1. **Check logs**: `storage/logs/laravel.log`
2. **Enable debug mode**: Set `APP_DEBUG=true` in `.env` (development only!)
3. **Run diagnostics**:

    ```bash
    php artisan about
    php artisan config:show database
    php artisan route:list
    ```

4. **Open an issue**: [GitHub Issues](https://github.com/luqastw/canary/issues)

## Database Schema

### Key Tables

**tenants**

- `id`, `name`, `email`, `status` (active/suspended/deleted)

**flags**

- `id`, `tenant_id`, `key`, `name`, `description`, `is_enabled`
- Soft deletes enabled
- Unique constraint on (tenant_id, key)

**groups**

- `id`, `tenant_id`, `identifier`, `name`, `description`
- Unique constraint on (tenant_id, identifier)

**flag_targeting**

- `id`, `flag_id`, `group_id`
- Soft deletes enabled
- Unique constraint on (flag_id, group_id)

**users**

- Standard Laravel users table + `tenant_id`
- Used for web dashboard authentication

## API Rate Limits

| Endpoint                 | Limit         | Period     |
| ------------------------ | ------------- | ---------- |
| `/api/v1/evaluate`       | 1000 requests | per minute |
| `/api/v1/evaluate/batch` | 1000 requests | per minute |
| All other API endpoints  | 60 requests   | per minute |

## Security

- **Sanctum Authentication**: Secure token-based API auth
- **Tenant Isolation**: Automatic via Global Scopes (impossible to access other tenant's data)
- **CSRF Protection**: Enabled for web routes
- **Password Hashing**: Bcrypt with configurable rounds
- **Validation**: All inputs validated via Form Requests
- **SQL Injection**: Protected via Eloquent ORM
- **XSS**: Blade template engine auto-escapes output

## Environment Variables

Key configuration options:

```env
# App
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_DATABASE=feature_flags

# Cache (Redis recommended for production)
CACHE_STORE=redis
REDIS_HOST=127.0.0.1

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

## Development

### Project Structure

```
.
├── app/
│   ├── Contracts/          # Interfaces
│   ├── Enums/              # Enums (TenantStatus)
│   ├── Http/               # Controllers, Requests, Resources
│   ├── Models/             # Eloquent models + TenantScope
│   ├── Repositories/       # Data access layer
│   └── Services/           # Business logic
├── database/
│   ├── factories/          # Model factories for testing
│   ├── migrations/         # Database migrations
│   └── seeders/            # Demo data seeder
├── resources/
│   ├── css/                # Tailwind CSS
│   ├── js/                 # Alpine.js
│   └── views/              # Blade templates
├── routes/
│   ├── api.php             # API routes
│   └── web.php             # Web dashboard routes
└── tests/
    └── Feature/            # API integration tests
```

### Coding Standards

- **PSR-12**: PHP coding standard
- **Strict Types**: All PHP files use `declare(strict_types=1)`
- **Type Hints**: All parameters and return types are typed
- **Readonly Properties**: Used for dependency injection
- **PHP 8.2+**: Leverages modern PHP features (attributes, enums, etc.)

### Development Workflow

```bash
# Install dev dependencies
composer install
npm install

# Run tests
php artisan test

# Code style check (if configured)
./vendor/bin/phpcs

# Fix code style
./vendor/bin/phpcbf
```

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
