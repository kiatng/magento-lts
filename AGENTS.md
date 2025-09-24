# OpenMage Project AGENTS.md

## Project Overview
OpenMage is a community-driven fork of Magento CE 1.x, providing a modern, secure, and actively maintained e-commerce platform. This project maintains backward compatibility while adding security updates, bug fixes, and modern PHP support.

## PHP Coding Standards
- Use `array_key_exists()` instead of `isset()` for checking array key existence
- Use `str_contains()` instead of `strpos() !== false` for string contains checks
- Use `str_starts_with()` instead of `substr()` for prefix checks
- Use `str_ends_with()` instead of `substr()` for suffix checks
- Use `is_null` instead of `$var === null`
- Apply Don't Repeat Yourself (DRY) principle
- Apply Single Responsibility Principle (SRP)

### Continuous Integration (CI) Workflow
- Check code style on modified files: `composer php-cs-fixer:test -- <file_path>`
- Fix code style on modified files: `composer php-cs-fixer:fix -- <file_path>`
- Run static analysis on modified files: `composer phpstan -- <file_path>`
- Run mess detector on modified files: `composer phpmd <file_path> text .phpmd.dist.xml`

### Type Declarations
- Always use strict type declarations: `declare(strict_types=1);` in new files
- Use proper type hints for parameters and return types
- Use nullable types (`?Type`) instead of `Type|null`

### Error Handling
- Use specific exception types instead of generic `Exception`
- Provide meaningful error messages

### Code Organization
- Keep methods focused and single-purpose
- Use meaningful variable names
- Remove unused parameters and variables
- Use consistent indentation (4 spaces)
- Add proper PHPDoc comments for public methods

### Documentation
- Add clear comments for complex logic
- Explain why certain approaches are used
- Keep comments up-to-date with code changes

## Testing Standards

### Running Tests with Composer
- **Basic test run**: `composer run phpunit:test`
- **Specific test file**: `composer run phpunit:test -- tests/unit/Mage/Oauth/Model/ServerTest.php`
- **Filtered tests**: `composer run phpunit:test -- --filter="testCompleteOAuthFlow"`
- **TestDox output**: `composer run phpunit:test -- --testdox`
- **Specific testsuite**: `composer run phpunit:test -- --testsuite Mage_Oauth`
- **With coverage**: `composer run phpunit:coverage` (requires Xdebug)
- **Full test suite**: `composer run test`

### Running Tests with DDEV
- **Basic test run**: `ddev exec composer run phpunit:test`
- **Specific test file**: `ddev exec composer run phpunit:test -- tests/unit/Mage/Oauth/Model/ServerTest.php`
- **Filtered tests**: `ddev exec composer run phpunit:test -- --filter="testCompleteOAuthFlow" --testdox`
- **TestDox output**: `ddev exec composer run phpunit:test -- --testdox`
- **Specific testsuite**: `ddev exec composer run phpunit:test -- --testsuite Mage_Oauth --testdox`
- **With coverage**: `ddev exec composer run phpunit:coverage` (requires Xdebug in ddev)

### Test Organization
- **Unit tests**: Located in `tests/unit/` directory
- **Integration tests**: Use `@group Integration` annotation
- **OAuth tests**: Use `@group Oauth` annotation
- **Success tests**: Use `@group Success` annotation
- **Data providers**: Use traits for shared test data

### Test Best Practices
- Use descriptive test method names
- Group related tests with `@group` annotations
- Use data providers for parameterized tests
- Avoid verbose assertions - keep tests focused
- Use real objects instead of excessive mocking for integration tests
- Test complete OAuth flows end-to-end
- Use real database consumers for integration tests

## Code Quality Workflow
- After modifying files, check style: `composer php-cs-fixer:test -- <modified_file>`
- Fix style issues: `composer php-cs-fixer:fix -- <modified_file>`
- Run static analysis: `composer phpstan -- <modified_file>`
- Run mess detector: `composer phpmd <modified_file> text .phpmd.dist.xml`
- Run relevant tests: `composer phpunit:test -- <test_file>`
- Only run full test suite for major changes: `composer test`

## Security
- Use `hash_equals()` for string comparison to prevent timing attacks
- Validate all input parameters
- Use proper OAuth signature validation
- Sanitize user input

## Performance
- Use `array_merge()` efficiently
- Avoid unnecessary object instantiation
- Use appropriate data structures
- Cache frequently used objects when appropriate

## Pull Request Guidelines
- Title format: `[Component] Brief description`
- Check code style on modified files: `composer php-cs-fixer:test -- <file_path>`
- Fix any style issues: `composer php-cs-fixer:fix -- <file_path>`
- Run static analysis on modified files: `composer phpstan -- <file_path>`
- Run mess detector on modified files: `composer phpmd <file_path> text .phpmd.dist.xml`
- Run relevant tests for modified components: `composer phpunit:test -- <test_file>`
- Include tests for new functionality
- Update documentation for API changes
- Follow existing code style and patterns

## Security Considerations
- Never commit sensitive data (API keys, passwords, etc.)
- Validate all user input
- Use prepared statements for database queries
- Follow OWASP guidelines for web security
