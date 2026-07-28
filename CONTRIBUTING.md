# Contributing

Thank you for considering contributing to the OTP Service package! Please follow these guidelines to help make the process smooth and effective.

## Code of Conduct

Please note that this project is released with a Contributor Code of Conduct. By participating in this project you agree to abide by its terms.

## Reporting Issues

Before submitting an issue, please search the issue tracker to see if your issue has already been reported.

When submitting an issue, please include:
- A clear and descriptive title
- Steps to reproduce the issue
- Expected vs actual behavior
- Environment details (PHP version, Laravel version, package version)
- Any relevant code snippets or stack traces

## Pull Requests

1. Fork the repository and create your branch from `main`
2. If you've added code that should be tested, add tests
3. Ensure the test suite passes (`php vendor/bin/phpunit`)
4. Make sure your code follows the PSR-12 coding standard
5. Update the README.md with details of changes if applicable
6. Issue that pull request!

## Development Setup

```bash
# Clone your fork
git clone https://github.com/your-username/otp-service.git

# Navigate to the project directory
cd otp-service

# Install dependencies
composer install

# Run tests
vendor/bin/phpunit
```

## Code Style

This project follows PSR-12. We use PHP_CodeSniffer to enforce coding standards. You can run:

```bash
vendor/bin/phpcs src
vendor/bin/phpcs tests
```

To automatically fix issues:

```bash
vendor/bin/phpcbf src
vendor/bin/phpcbf tests
```

## Writing Tests

When adding new features or fixing bugs, please include appropriate tests. Tests should:
- Be placed in the `tests` directory
- Follow the existing test structure
- Cover both positive and negative test cases
- Mock external dependencies when necessary

Thank you for your contributions!