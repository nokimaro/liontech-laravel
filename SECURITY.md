# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 1.x     | ✅ Yes    |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

### How to Report

Use [GitHub Security Advisories](https://github.com/nokimaro/liontech-laravel/security/advisories/new)
to submit a vulnerability report privately.

Please include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

### Response Timeline

| Stage | Timeline |
|-------|----------|
| Acknowledgement | within 48 hours |
| Status update | within 7 days |
| Fix or mitigation | within 30 days for critical issues |

## Scope

**In scope:**

- Service provider registration and configuration handling
- Webhook signature verification integration (`Security\WebhookSignatureVerifier`)
- Card encryption integration (`Security\CardEncryptor`)
- Sensitive configuration values (tokens, keys) exposure

**Out of scope:**

- Vulnerabilities in the LionTech API itself — report directly to LionTech
- Vulnerabilities in [nokimaro/liontech-php-sdk](https://github.com/nokimaro/liontech-php-sdk) — report to that repository
- Issues requiring physical access to the server
- Vulnerabilities in third-party dependencies — report to the respective maintainers

## Disclosure Policy

We follow [Coordinated Vulnerability Disclosure](https://en.wikipedia.org/wiki/Coordinated_vulnerability_disclosure).
Once a fix is released, the vulnerability details will be published in the GitHub Security Advisory.
