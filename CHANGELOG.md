# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-01-20

### Added
- Account Verification API integration (moved to Onboarding Solution)
  - Account verification API integration
  - Verify account link with CNIC and mobile number
  - Account status, title, and type retrieval
  - PIN status checking
  - Comprehensive request/response DTOs
- Account verification repository and model
- Account verification events
- Account verification controller method in OnboardingController

### Changed
- Moved Account Verification from Inquiry Solution to Onboarding Solution
- Account verification is now accessible via `ZindagiZconnect::onboarding()->verifyAccount()`
- Updated configuration to include account_verification under onboarding module

## [1.0.0] - 2025-01-20

### Added
- Initial release of Zindagi Z-Connect package
- Onboarding Solution module with complete workflow
  - Customer onboarding initiation
  - Customer verification
  - Onboarding status tracking
  - Onboarding completion
- Core services
  - Authentication service with token caching
  - HTTP client service with automatic authentication
  - Comprehensive logging service with sensitive data masking
  - Audit trail service for all operations
- Database migrations
  - Audit logs table
  - Onboardings table
- Security features
  - Request validation middleware
  - API key validation
  - Request signature validation
  - Sensitive data masking in logs
- Validation helpers
  - CNIC validation and formatting
  - Mobile number validation and formatting
- Events system
  - OnboardingInitiated event
  - OnboardingVerified event
  - OnboardingCompleted event
- Comprehensive test suite
- Full documentation

### Security
- All sensitive data is automatically masked in logs
- Secure credential management
- Request/response validation
- Comprehensive audit trail

