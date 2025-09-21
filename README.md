# Currency Conversion API

A Laravel-based REST API that converts currencies using live exchange rates. Built with authentication and clean architecture principles.

## Getting Started

**Requirements:**

-   PHP 8.2+
-   Composer
-   Docker

**Installation:**

Clone and install dependencies:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

**Run the application:**

```bash
# Full Docker environment (recommended for production-like testing)
docker-compose up -d --build

# Or run Laravel locally while using Docker Redis (best for development)
php artisan serve
```

> **Note**: This API always uses Redis for caching and sessions. Local development connects to Docker Redis for optimal performance and consistency.

## Usage

**Authentication:**
First register or login to get your API token:

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"password"}'
```

**Convert currencies:**

```bash
curl -X POST http://localhost:8000/api/currency/convert \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"from":"USD","to":"EUR","amount":100}'
```

## Testing

Run the test suite:

```bash
php artisan test
```

## API Documentation

Visit `/api/documentation` for the complete Swagger docs when the app is running.
