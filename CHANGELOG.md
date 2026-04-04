# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/nokimaro/liontech-laravel/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/nokimaro/liontech-laravel/releases/tag/v1.0.0
