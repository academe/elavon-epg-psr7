# Contributing to Elavon EPG PSR-7

Thank you for considering contributing to this package! This document outlines the process and standards for contributing.

## Getting Started

### Prerequisites

- PHP 8.1 or higher
- Composer
- Git

### Setting Up Development Environment

1. **Clone the repository**
   ```bash
   git clone https://github.com/academe/elavon-epg-psr7.git
   cd elavon-epg-psr7
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Verify installation**
   ```bash
   composer test
   composer phpstan
   composer cs-check
   ```

## Development Workflow

### 1. Create a Feature Branch

```bash
git checkout -b feature/my-new-feature
# or
git checkout -b fix/bug-description
```

Branch naming conventions:
- `feature/{description}` - New features
- `fix/{description}` - Bug fixes
- `docs/{description}` - Documentation updates
- `refactor/{description}` - Code refactoring
- `test/{description}` - Test improvements

### 2. Make Your Changes

Follow the [Coding Standards](docs/coding-standards.md) when writing code:
- Use PHP 8.1+ features (enums, readonly, union types)
- Make classes `final` by default
- Use strict types: `declare(strict_types=1);`
- Type hint everything (properties, parameters, returns)
- Write immutable value objects with `readonly`

### 3. Write Tests

Every change should include tests:

```bash
# Run tests
composer test

# Run tests with coverage
composer test -- --coverage-html coverage
```

**Test Requirements:**
- Unit tests for all new classes
- Integration tests for complex interactions
- Minimum 80% code coverage (target 90%+)
- Tests must follow naming convention: `test_{method}_{scenario}_{expected}`

### 4. Check Code Quality

```bash
# Static analysis (must pass level 8)
composer phpstan

# Code style check
composer cs-check

# Auto-fix code style issues
composer cs-fix
```

### 5. Commit Your Changes

Follow conventional commit format:

```bash
git commit -m "feat: add Money value object with currency support"
git commit -m "fix: correct validation in CardNumber class"
git commit -m "docs: update architecture documentation"
git commit -m "test: add tests for Transaction DTO"
```

**Commit Types:**
- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation changes
- `test:` - Test additions/changes
- `refactor:` - Code refactoring
- `style:` - Code style changes (formatting)
- `chore:` - Build, dependencies, tooling

### 6. Push and Create Pull Request

```bash
git push origin feature/my-new-feature
```

Then create a pull request on GitHub.

## Coding Standards

### Overview

This project strictly follows:
- **PSR-1**: Basic Coding Standard
- **PSR-12**: Extended Coding Style Guide
- **PSR-4**: Autoloading Standard

See [docs/coding-standards.md](docs/coding-standards.md) for complete details.

### Quick Reference

```php
<?php

declare(strict_types=1);

namespace Academe\Elavon\Ept\Psr7\ValueObjects;

/**
 * Short description of the class.
 *
 * Longer description if needed.
 */
final readonly class Money
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $amount,
        public Currency $currency,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (!$this->isValidAmount($this->amount)) {
            throw new InvalidArgumentException('Invalid amount format');
        }
    }

    private function isValidAmount(string $amount): bool
    {
        return (bool) preg_match('/^\d+(\.\d{1,2})?$/', $amount);
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency->value,
        ];
    }
}
```

### Key Principles

1. **Type Safety**: Use strict types everywhere
2. **Immutability**: Value objects and DTOs should be readonly
3. **Validation**: Fail fast in constructors
4. **Final Classes**: Classes are final unless inheritance is intentional
5. **Documentation**: Clear docblocks for all public APIs

## Architecture

### Package Structure

```
src/
├── Messages/        # PSR-7 request/response implementations
├── DataObjects/     # DTOs for API resources
├── ValueObjects/    # Immutable value objects
├── Enums/           # PHP 8.1+ enumerations
├── Contracts/       # Interfaces
├── Exceptions/      # Custom exceptions
└── Support/         # Helper classes and traits
```

See [docs/architecture.md](docs/architecture.md) for detailed architecture documentation.

### Design Guidelines

#### Value Objects
- Immutable (use `readonly`)
- Validate in constructor
- Provide rich comparison methods
- No setters, only factory methods for transformations

#### DTOs
- Match API resource structures
- Readonly by default
- Provide `toArray()` and `fromArray()` methods
- Type hint all properties

#### Enums
- Use backed enums for API values
- String-backed for API strings
- Int-backed for numeric codes

## Testing

### Writing Tests

```php
<?php

declare(strict_types=1);

namespace Academe\Elavon\Ept\Psr7\Tests\Unit\ValueObjects;

use Academe\Elavon\Ept\Psr7\Enums\Currency;
use Academe\Elavon\Ept\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Ept\Psr7\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_constructor_withValidAmount_createsMoney(): void
    {
        // Arrange
        $amount = '10.50';
        $currency = Currency::USD;

        // Act
        $money = new Money($amount, $currency);

        // Assert
        $this->assertSame($amount, $money->amount);
        $this->assertSame($currency, $money->currency);
    }

    public function test_constructor_withInvalidAmount_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        new Money('invalid', Currency::USD);
    }
}
```

### Test Structure
- Use Arrange-Act-Assert pattern
- One assertion per test (when possible)
- Descriptive test names
- Cover edge cases and error conditions

## Pull Request Guidelines

### Before Submitting

Ensure your PR passes all checks:

- [ ] All tests pass (`composer test`)
- [ ] PHPStan level 8 passes (`composer phpstan`)
- [ ] Code style is correct (`composer cs-check`)
- [ ] Test coverage meets minimum 80%
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated (for significant changes)

### PR Description Template

```markdown
## Description
Brief description of the changes

## Type of Change
- [ ] Bug fix (non-breaking change fixing an issue)
- [ ] New feature (non-breaking change adding functionality)
- [ ] Breaking change (fix or feature causing existing functionality to change)
- [ ] Documentation update

## Checklist
- [ ] Tests added/updated
- [ ] Documentation updated
- [ ] PHPStan passes
- [ ] Code style check passes
- [ ] CHANGELOG.md updated

## Related Issues
Fixes #123
```

### Review Process

1. All PRs require at least one approval
2. All automated checks must pass
3. Maintainers may request changes or clarifications
4. Be responsive to feedback

## Documentation

### When to Update Documentation

- Adding new classes or features
- Changing public APIs
- Deprecating functionality
- Adding examples or guides

### Documentation Locations

- **API docs**: PHPDoc in code
- **Architecture**: `docs/architecture.md`
- **Standards**: `docs/coding-standards.md`
- **Guides**: `docs/guides/`
- **Examples**: `docs/examples/`

## Versioning

This project follows [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

## Code of Conduct

### Our Standards

- Be respectful and inclusive
- Welcome newcomers
- Focus on constructive feedback
- Assume good intentions

### Unacceptable Behavior

- Harassment or discrimination
- Trolling or insulting comments
- Personal attacks
- Unprofessional conduct

## Getting Help

- **Documentation**: Check `docs/` directory
- **Issues**: Browse existing [GitHub issues](https://github.com/academe/elavon-epg-psr7/issues)
- **Questions**: Open a discussion on GitHub

## Recognition

Contributors will be recognized in:
- GitHub contributors list
- CHANGELOG.md for significant contributions
- Release notes

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to Elavon EPG PSR-7!