# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.3] - 2026-04-05

### Added
- `WebhookController` example updated to use typed `WebhookPayload` DTO from SDK v1.1.3 — replaces manual array parsing with `isSuccessful()`, `isDeclined()`, and `error->hasError()`

### Fixed
- `LionTechConfig`: empty string key values (e.g. `LIONTECH_KEY=`) are now treated as `null`, triggering API auto-fetch fallback instead of passing an empty string to `WebhookSignatureVerifier` and `CardEncryptor`

### Changed
- Updated `nokimaro/liontech-php-sdk` dependency to `^1.1.3`

## [1.1.0] - 2026-04-05

### Added
- `EncryptionKeyClient` registered as a singleton in the service container and exposed via the `LionTech` facade

### Fixed
- `CardEncryptor` fallback was incorrectly fetching the webhook signature key (`/signature-key`) instead of the card encryption key (`/encryption-key`)
- `CreateRefundRequest` example now passes the required `webhookUrl` parameter
- `ResponseStatus` comparison in examples updated to use `.value`
- `CreateOrderRequest` README example now includes the required `description` field

### Changed
- Updated `nokimaro/liontech-php-sdk` dependency to `^1.1`

## [1.0.0] - 2026-04-04

### Added
- Laravel service provider with auto-discovery
- Facade `Nokimaro\LionTech\Laravel\Facades\LionTech` for static access to all SDK clients
- Dependency injection support for `Client` and all individual API clients as singletons
- `LionTechConfig` helper for configuration access and validation
- Publishable config file (`config/liontech.php`) with environment variable defaults
- Support for Laravel 11, 12, 13 and PHP 8.3, 8.4, 8.5
- Built on [nokimaro/liontech-php-sdk](https://github.com/nokimaro/liontech-php-sdk) ^1.0
- PHPStan level max with strict rules, 0 errors
- 100% test coverage and 100% type coverage
- CI workflow covering all supported PHP and Laravel version combinations

[Unreleased]: https://github.com/nokimaro/liontech-laravel/compare/v1.1.3...HEAD
[1.1.3]: https://github.com/nokimaro/liontech-laravel/compare/v1.1.0...v1.1.3
[1.1.0]: https://github.com/nokimaro/liontech-laravel/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/nokimaro/liontech-laravel/releases/tag/v1.0.0
