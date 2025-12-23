# License Service

A centralized License Service for the group.one ecosystem, designed to manage licenses, entitlements, and activations across multiple WordPress-focused brands (e.g., WP Rocket, RankMath, Imagify, BackWPup). This service provides **brand-facing APIs** to provision and manage licenses, as well as **product-facing APIs** to activate, validate, and deactivate license keys and seats.

For a complete explanation of the architecture, design decisions, trade-offs, and instructions for running the service locally, please see [Explanation.md](./Explanation.md).

## Key Features

- Multi-brand and multi-product license management
- License provisioning, activation, and lifecycle control
- Seat/instance management
- Observable and production-ready design
- Fully tested with Pest and linted with Laravel Pint

## Getting Started

1. Clone the repository
2. Follow the setup instructions in [Explanation.md](./Explanation.md) to run locally, seed the database, and test the APIs.
