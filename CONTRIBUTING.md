# Contributing to LionTech Laravel

Thank you for considering contributing to this package! We welcome contributions from the community.

## Code of Conduct

This project and everyone participating in it is governed by our [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the [issue list](https://github.com/nokimaro/liontech-laravel/issues) to see if the issue has already been reported. When you are creating a bug report, please include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples to demonstrate the steps**
- **Describe the behavior you observed and what behavior you expected**
- **Include PHP version, Laravel version, and package version**
- **Include any relevant error messages or stack traces**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

- **A clear and descriptive title**
- **A detailed description of the proposed functionality**
- **Explain why this enhancement would be useful**
- **List any similar features in other Laravel packages**

### Pull Requests

- Fill in the required template
- Do not include issue numbers in the PR title
- Include tests for new functionality
- Update documentation as needed
- End all files with a newline
- Follow the existing code style

## Development Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/YOUR_USERNAME/liontech-laravel.git
   cd liontech-laravel
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Run the test suite:
   ```bash
   composer test
   ```

5. Run static analysis:
   ```bash
   composer phpstan
   ```

6. Check code style:
   ```bash
   composer ecs
   ```

## Coding Standards

- We follow PSR-12 coding standards
- All code must be type-hinted where possible
- Use readonly classes and immutable objects where practical
- Write tests for all new functionality
- Document public APIs with PHPDoc blocks

### Code Style

```bash
# Check code style
composer ecs

# Auto-fix code style issues
composer ecs-fix
```

### Static Analysis

```bash
# Run PHPStan (level max)
composer phpstan
```

### Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run type coverage
composer test-type-coverage
```

## Git Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

## Review Process

- CI must pass before merging
- Maintainer will review and merge approved PRs

Thank you for contributing!
