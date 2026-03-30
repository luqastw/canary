# Feature Flags Service

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
git clone <repository-url>
cd canary
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Configure environment**
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

4. **Start database (if using Docker)**
```bash
docker run -d --name feature-flags-db \
  -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
  -e MYSQL_DATABASE=feature_flags \
  -p 3306:3306 \
  mariadb:latest
```

5. **Run migrations and seed demo data**
```bash
php artisan migrate:fresh --seed
```

This creates a demo tenant with:
- **Login**: `admin@demo.com` / `password`
- 5 sample flags
- 3 user groups
- Pre-configured targeting rules

6. **Build frontend assets**
```bash
npm run build
```

7. **Start the server**
```bash
php artisan serve
```

Visit http://localhost:8000 and login with the demo credentials.

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

**Access:** http://localhost:8000

#### Key Features:

1. **Dashboard**: Overview with metrics (total flags, active flags, groups)
2. **Flags Management**:
   - List all flags with inline toggle
   - Create/Edit/Delete flags
   - View flag details with API usage examples
   - Manage targeting rules per flag
3. **Groups Management**: CRUD operations for user groups
4. **Targeting Interface**: Visual drag-and-drop style interface for assigning groups to flags

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

| Endpoint | Limit | Period |
|----------|-------|--------|
| `/api/v1/evaluate` | 1000 requests | per minute |
| `/api/v1/evaluate/batch` | 1000 requests | per minute |
| All other API endpoints | 60 requests | per minute |

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

## Roadmap

Potential future enhancements:

- [ ] **Variants**: A/B testing support (return different values instead of just true/false)
- [ ] **Percentage Rollouts**: Enable for X% of users
- [ ] **Audit Log**: Track all flag changes with user attribution
- [ ] **Webhooks**: Notify external services on flag changes
- [ ] **SDK**: Client libraries for popular languages
- [ ] **Analytics**: Flag usage statistics and dashboards
- [ ] **Environments**: Separate flags for dev/staging/production
- [ ] **Flag Dependencies**: Require other flags to be enabled
- [ ] **Scheduled Toggles**: Auto-enable/disable at specific times
- [ ] **Health Checks**: `/health/live` and `/health/ready` endpoints

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (follow conventional commits)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-source software licensed under the MIT license.

## Credits

Built with:
- [Laravel 13](https://laravel.com)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Tailwind CSS 4](https://tailwindcss.com)
- [Alpine.js](https://alpinejs.dev)
- [Vite](https://vitejs.dev)

## Support

For issues, questions, or contributions, please open an issue on GitHub.

---

**Made with ❤️ using Laravel**
