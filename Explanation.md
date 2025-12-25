# License Service – Architecture & Implementation Explanation

## 1. Problem Statement

group.one operates multiple WordPress-focused brands (WP Rocket, RankMath, Imagify, BackWPup, etc.) that historically manage licenses independently. As the ecosystem grows, there is a need for a **centralized License Service** that acts as the **single source of truth** for license lifecycle and entitlements, while still allowing each brand to independently manage users, subscriptions, and billing.

This License Service must:
- Be multi-tenant (support multiple brands)
- Allow brands to provision and manage licenses
- Allow end-user products (plugins, apps, CLIs) to activate and validate licenses
- Be scalable, observable, and production-ready
- Expose clear APIs for both brand systems and products

The goal of this assessment is to demonstrate **backend system design, API modeling, and clean implementation**, not to build a full commercial system.

---

## 2. Architecture & Design

### 2.1 High-Level Architecture
Brand Systems (Billing, Users) ───────▶ License Service (Laravel API) ◀─────── End-User Products (Plugins / Apps)

    ├─ Brands
    ├─ Products
    ├─ License Keys
    ├─ Licenses
    └─ Activations (Seats)

- **Brand systems** integrate with the License Service to provision and manage licenses.
- **End-user products** call the License Service to activate, validate, and deactivate licenses.
- The License Service is the **single source of truth** for license state and entitlements.

---

### 2.2 Multi-Tenancy Model

Multi-tenancy is modeled explicitly at the **data level**:

- `Brand`
    - Owns products
    - Owns license keys
    - Authenticates via `api_token`

- `Product`
    - Belongs to exactly one brand
    - Identified by a unique `code` (e.g. `RANKMATH`, `WP_ROCKET`)

- `LicenseKey`
    - Scoped to one brand
    - Shared across multiple licenses within that brand
    - Linked to a `customer_email`

- `License`
    - Represents entitlement to **one product**
    - Has lifecycle state and expiration

This ensures:
- Brands cannot see or manage other brands’ licenses
- License keys are reusable **within** a brand but never across brands

> Multi-tenancy is fully designed at the database and API level. Production-level scaling strategies (database sharding, caching, async processing) are documented in the trade-offs section but not implemented.

---

### 2.3 Core Domain Model

| Entity | Purpose |
|------|--------|
| Brand | Tenant boundary, authentication |
| Product | Sellable product or addon |
| LicenseKey | Key shared by multiple licenses |
| License | Entitlement for one product |
| Activation | Seat / instance activation |

**Relationships**
- Brand → Products
- Brand → LicenseKeys
- LicenseKey → Licenses
- License → Activations

---

### 2.4 API Design

The system exposes **two API surfaces**:

#### Brand-Facing API
- Authenticated using `api_token`
- Used by internal brand systems

#### Product-Facing API
- Used by plugins / apps
- Rate-limited and observable
- Stateless requests

---

## 3. Trade-Offs & Design Decisions

### 3.1 LicenseKey vs License Separation

**Chosen approach**
- One `LicenseKey` per customer per brand
- Multiple `Licenses` per key (one per product)

**Why**
- Matches real-world SaaS behavior (addons, bundles)
- Supports scenarios like RankMath + Content AI under one key
- Allows independent lifecycle per product

**Alternative**
- One license per key → rejected due to inflexibility

---

### 3.2 Seat Management

**Current**
- Seat model (`Activation`) fully designed
- Core activation & deactivation implemented
> Note: The seat/activation system is fully designed, including data model (`Activation`), flows, and API.  
> For this assessment, the implementation supports basic activation/deactivation, but advanced concurrency and distributed enforcement are not implemented. These are outlined in the scaling plan.

**Trade-off**
- No advanced concurrency handling (e.g. distributed locks)

**Future**
- Redis-backed seat counters
- Optimistic locking on activation creation

---

### 3.3 Authentication Choices

**Brand API**
- Token-based (`api_token`)
- Simple and sufficient for internal services

**Product API**
- No auth for assessment scope
- Protected by rate limiting

**Future**
- Signed product tokens
- mTLS or OAuth for products

---

### 3.4 Observability

**Implemented**
- Structured tracing (`Tracer`)
- Spans for critical actions
- Metrics hooks

**Not Implemented**
- Tracing and structured logging (`Tracer`) are implemented. A full production-grade observability stack (e.g., OpenTelemetry, dashboards) is designed but not fully implemented.

---

## 4. User Stories Coverage

| User Story | Status | Notes |
|----------|--------|------|
| US1 – Provision license | ✅ Implemented | `ProvisionLicenseAction` |
| US2 – Change lifecycle | ✅ Implemented | Suspend / resume / cancel |
| US3 – Activate license | ✅ Implemented | Seat-aware |
| US4 – Check license status | ✅ Implemented | Returns entitlements |
| US5 – Deactivate seat | ✅ Implemented | Frees activation |
| US6 – List licenses by email | ✅ Implemented | Brand-only |

All **recommended core stories** are fully implemented.

---

## 5. How to Run Locally

### 5.1 Requirements

- PHP 8.2+
- Composer
- PostgreSQL (or MySQL)
- Node.js (optional)

---

### 5.2 Setup Steps

```bash
git clone https://github.com/oolayemi/license-service.git
cd license-service

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate --seed
php artisan serve
```
### 5.3 Environment Variables

```bash
DB_CONNECTION=pgsql
DB_DATABASE=license_service
DB_USERNAME=postgres
DB_PASSWORD=postgres

CACHE_DRIVER=database
QUEUE_CONNECTION=sync
```

### 5.4 Sample Requests

#### Provision License (Brand)
```bash
curl -X POST http://localhost:8000/api/brand/licenses \
  -H "Authorization: Bearer BRAND_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_email": "user@example.com",
    "product_codes": ["RANKMATH", "CONTENT_AI"],
    "expires_at": "2026-12-31"
  }'
```

#### Activate License (Product)
```bash
curl -X POST http://localhost:8000/api/product/license/activate \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "XXXX-XXXX",
    "product_code": "RANKMATH",
    "instance_id": "site_123"
  }'
```

## 6. Testing & CI
#### Testing
- Pest for unit and feature tests
- Coverage for actions, models, and APIs

```bash
php artisan test
```

#### Linting
- Laravel Pint

```bash
./vendor/bin/pint
```

#### Code Quality 
- PHPStan (Level 8)
```bash
composer analyse
```

#### CI
- GitHub Actions
- Run Pint + Larastan + Pest on every PR and commit to main

All checks run automatically via GitHub Actions on every pull request and push to `main`.

## 7. Docker Architecture
### Running the project with Docker
#### 1. Build and start containers
```bash
docker compose up -d --build
```
#### 2. Run database migrations
```bash
docker compose exec app php artisan migrate
```
#### (Optional) Seed demo data if available:
```bash
docker compose exec app php artisan db:seed
```

#### 3. Access the API
The API is available at:
```bash
http://localhost:8000
```


## 8. Known Limitations & Next Steps
### Limitations
- No real-time eventing (webhooks)
- No UI dashboard

### Next Steps
- Redis-backed rate limiting & seats
- Webhooks for brand systems
- License usage analytics

