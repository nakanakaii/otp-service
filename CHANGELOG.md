# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2024-07-28

### Added
- Core OTP service with generate, verify, invalidate, and hasPending methods
- Event-driven architecture (OtpRequested event)
- Polymorphic model support for OTP ownership
- Laravel Facade for easy access
- Service provider with auto-discovery
- Configurable OTP length, expiration, and max verification attempts
- Database migration and model for OTP storage
- Artisan command for cleaning expired OTPs
- Rate limiting functionality to prevent OTP abuse
  - Configurable rate limit (enabled by default: false)
  - Per-model instance rate limiting
  - Custom identifier support (e.g., for IP-based limiting)
  - OtpThrottledException with retry-after information
  - availableIn() helper to check cooldown time
- Comprehensive test suite (38 tests)
- GitHub Actions for continuous integration
- Dependabot for automated dependency updates
- Issue templates for bug reports and feature requests
- Contributing guidelines
- Proper licensing (MIT)

### Changed
- Combined vendor:publish commands into single command with multiple tags
- Updated README with comprehensive documentation including rate limiting

### Fixed
- N/A (initial release)