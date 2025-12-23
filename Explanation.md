License Service – Explanation
1. Problem & Requirements (In My Own Words)

group.one operates multiple WordPress-focused brands (WP Rocket, RankMath, Imagify, etc.) that historically managed licenses independently. As the ecosystem grows, this leads to duplication, inconsistent entitlements, and difficulty understanding what products a customer can access across brands.

The goal of this project is to build a centralized License Service that:

Acts as the single source of truth for licenses and entitlements

Supports multi-tenancy across brands and products

Allows brand systems to provision and manage licenses

Allows end-user products (plugins, apps, CLIs) to activate and validate licenses

Is designed for scalability, observability, and extensibility

The service is API-only, with no UI, and focuses on correctness, clarity, and production-readiness.

2. Architecture & Design
   2.1 High-Level Architecture
   Brand Systems ──▶ License Service ◀── End-User Products
   │                   │
   │                   ├── License Keys
   │                   ├── Licenses (per product)
   │                   └── Activations (seats / instances)


Brand systems manage users, billing, and subscriptions

License Service manages license lifecycle and entitlements

End-user products call the service to activate and validate usage

2.2 Core Domain Model
Entity	Purpose
Brand	Represents a tenant (e.g. WP Rocket, RankMath)
Product	A licensable product belonging to a brand
LicenseKey	A single key shared across multiple licenses
License	Grants access to one product
Activation	Represents a seat or instance usage
Relationships

Brand → has many Products

Brand → has many LicenseKeys

LicenseKey → has many Licenses

License → belongs to one Product

License → has many Activations

2.3 Multi-Tenancy Strategy

Each request is scoped by Brand

Brand-facing APIs require a brand API token

License keys are brand-specific

No data is shared across brands unless explicitly designed

This avoids accidental cross-brand data leakage and keeps tenancy boundaries clear.

2.4 API Design
Brand-Facing APIs

Provision licenses

Change license lifecycle

List licenses by customer email

Authenticated using a brand API token.

Product-Facing APIs

Activate license

Check license status

Deactivate seat

Designed to be simple, fast, and cacheable.

2.5 Observability & Operability

Tracer

Trace ID per request

Span-based logging for actions

Metrics

License provisioned

License activated

Activation failures

Rate limiting

Product-facing endpoints protected per license key + product

Structured logging

JSON-friendly logs with context

This allows easy integration with systems like Datadog, New Relic, or ELK.

3. Trade-offs & Decisions
   3.1 Technology Choices
   Choice	Reason
   Laravel	Strong ecosystem, rapid development, clean architecture
   MySQL	Widely supported, easy local & CI setup
   Pest	Readable, expressive testing
   Laravel Pint	Official linter, zero-config
   3.2 Alternatives Considered
   Separate License Key per Product

❌ Rejected

Makes add-ons (e.g. Content AI) harder to manage

Worse user experience

Fully Event-Driven Architecture

❌ Overkill for assessment scope

Added complexity without immediate benefit

External Rate Limiter (API Gateway)

❌ Out of scope

In-app rate limiting sufficient for now

3.3 Scaling Plan

If this service grows:

Move activations & checks to Redis-backed cache

Add read replicas

Introduce event-driven updates for lifecycle changes

Add JWT-based product authentication

Introduce async seat cleanup jobs

The current design intentionally supports these evolutions.

4. User Stories Coverage
   ✅ US1 – Brand can provision a license

Implemented

ProvisionLicenseAction

Creates license key

Creates licenses per product

Supports add-ons under same key

✅ US2 – Brand can change license lifecycle

Implemented

ChangeLicenseLifecycleAction

Supports suspend, resume, cancel, renew

✅ US3 – End-user product can activate a license

Implemented

ActivateLicenseAction

Creates activation per instance

Seat limit enforced via SeatManager

✅ US4 – User can check license status

Implemented

CheckLicenseStatusAction

Returns:

License validity

Product entitlements

Seat usage

✅ US5 – Deactivate a seat

Implemented

DeactivateSeatAction

Frees activation slot

✅ US6 – List licenses by customer email

Implemented

ListLicensesByEmailAction

Brand-authenticated only

No end-user access

5. How to Run Locally
   5.1 Setup
   git clone <repo>
   cd license-service
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan db:seed
   php artisan serve

5.2 Environment Variables
APP_ENV=local
DB_CONNECTION=mysql
DB_DATABASE=license_service
DB_USERNAME=root
DB_PASSWORD=

5.3 Sample Requests
Provision License
curl -X POST /api/brand/licenses \
-H "Authorization: Bearer BRAND_API_TOKEN" \
-d '{"customer_email":"user@example.com","product_codes":["RANKMATH"]}'

Activate License
curl -X POST /api/product/license/activate \
-d '{"license_key":"XXXX","product_code":"RANKMATH","instance_id":"site_1"}'

Check Status
curl /api/product/license/status?license_key=XXXX


Postman collection is included in the repository.

6. Code Quality & CI

Laravel Pint enforces coding standards

Pest covers unit & integration tests

GitHub Actions CI

Runs lint + tests on every PR & commit

Fully automated quality gate

7. Known Limitations & Next Steps
   Limitations

Seat management is synchronous

No async event propagation

No per-product auth tokens yet

Next Steps

Add Redis caching

Introduce async event bus

Add audit logs

Introduce usage analytics

Add OpenTelemetry exporter

Final Notes

This solution prioritizes:

Correctness

Clarity

Production realism

Extensibility

While not all future features are implemented, the architecture intentionally supports growth, and all core requirements are fully satisfied.
