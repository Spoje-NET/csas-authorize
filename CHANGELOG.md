# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Removing a token revokes it at the bank IDP (`{idp}/revoke`, RFC 7009) before deleting it
- Token expiration reminder is sent at most once per `CSAS_AUTHORIZE_NOTIFY_INTERVAL` seconds (default 86400) instead of every daemon cycle; new column `token.notified_at` (migration)

### Changed
- Debian: database migrations run by `/usr/lib/csas-authorize/csas-authorize-migrate` on `configure` of `csas-authorize` and also after (re)configuring the database in `csas-authorize-mysql` / `csas-authorize-sqlite`; they no longer run on `abort-*` actions
- Emails (authorization link, token renewal reminder) are sent by Symfony Mailer configured with `MAIL_DSN` and `MAIL_FROM`; deprecated `EASE_SMTP` is still understood. Failure reason is shown to the user
- New dependency `php-symfony-mailer`

### Fixed
- `welcomeback.php`: rejected consent (`error=access_denied`, also in the URL fragment) shows the reason with a re-authorize button instead of redirecting to the non-existent `{idp}/authorize`; failed code exchange is shown in the page
- `welcomeback.php`: a `state` of an already completed authorization is not accepted again
- Re-authorization link after an expired refresh token (`token.php`) pointed to `auth.php?id=` and ended on the index page
- Refresh token is cleared only when the IDP rejects it (`invalid_grant`, 7109, expired); transient errors like `server_error` keep it for the next attempt
- `token.php`: token of an unfinished authorization (no `expires_in`) no longer ends with a fatal `DateTime` error
- Redirects now stop the script, so removing or refreshing a token no longer fails in `PageTop`
- `mailauth.php`: undefined `$recipientEmail` in status messages
- Sending authorization link for an application without email no longer fails on unknown `WebPage` class
- Status messages carried over a redirect no longer store objects in the session (an `Ease\Mailer` there broke every following request). A session already affected must be reset once by clearing the browser cookie

## [0.5.0] - 2026-10-15

### Added
- Older tokens listed in `welcomeback.php` with individual Remove buttons and a bulk action removing all unusable tokens (`token.php?action=purge`)
- Czech translation of new messages
- Tests for `TokenObsolete` and CSRF helpers

### Security
- Token actions in `token.php` and `welcomeback.php` are protected by a CSRF token

### Changed
- Debian Test stage installs the Zabbix repository

## [0.4.0] - 2026-10-12

### Added
- New package `csas-authorize-zabbix`: Zabbix agent2 monitoring of token expiration (LLD discovery, refresh token days left, template included)
- `csas-authorize.service` restarts on failure and waits for the database

### Fixed
- `token.php`: missing refresh token no longer ends with HTTP 500, user is redirected to reauthorization
- Refresh token expiry calculation and expiring tokens notification
- `welcomeback.php`: main menu and buttons back to the token and application

### Changed
- Static Debian autoloader only, composer removed from Build-Depends

## [0.3.0] - 2024-09-29

### Added

#### Export Functionality
- **NEW**: Export mode in `csas-access-token.php` tool via `--export` / `-x` option
- Export application data in Developer Portal compatible JSON format
- Support for exporting by application ID or UUID
- File output or stdout output for piping to other tools
- Complete bidirectional data flow: import + export capabilities
- Comprehensive documentation in `docs/EXPORT_FUNCTIONALITY.md`

#### Enhanced OAuth2 Error Handling  
- Graceful handling of expired refresh tokens in `Token::refreshToken()`
- New methods: `isRefreshTokenExpired()`, `needsRefresh()`, `getTokenStatus()`
- Automatic redirect to re-authorization when refresh token expires
- Proper exception handling for `IdentityProviderException`

#### Developer Portal Import System
- Web interface for importing Developer Portal data (`src/import.php`)
- Command-line import tool (`import-from-portal` command) 
- `DeveloperPortalImporter` class with validation and field mapping
- Support for hierarchical and flat JSON formats
- Integration into main UI with "Import from Developer Portal" link
- Comprehensive documentation in `DEVELOPER_PORTAL_IMPORT.md`

#### Comprehensive Test Suite
- **NEW**: Complete PHPUnit test coverage for all SpojeNet classes
- Unit tests: `ApplicationTest`, `AuthTest`, `NotificatorTest`, `TokenInfoTest`, `WebPageTest`
- Integration test: `ApplicationAuthFlowTest` for complete OAuth2 workflow  
- Mock-based testing for external API dependencies
- Test documentation in `TEST_SUITE_DOCUMENTATION.md`

### Changed

#### Documentation Updates
- Updated `README.md` with export functionality and bidirectional workflow
- Enhanced command-line usage examples with export options
- Updated man page (`debian/csas-access-token.1`) with new export option
- Expanded `DEVELOPER_PORTAL_IMPORT.md` with export information
- Added comprehensive export documentation

#### Tool Enhancements
- Extended `csas-access-token.php` with new command-line options
- Updated help text and usage information
- Better error messages and exit codes
- Support for both numeric IDs and UUIDs in export mode

### Technical Details

#### Database Schema
No changes to existing database schema. All new functionality works with existing tables:
- `application` table: stores application data with sandbox/production environments
- `token` table: stores OAuth2 tokens with refresh capabilities

#### API Compatibility
- Maintains backward compatibility with existing OAuth2 flows
- All existing functionality preserved
- New features are additive and optional

#### Security Considerations
- Export functionality includes sensitive client secrets and API keys
- Proper validation and sanitization of import data
- Clear security warnings in documentation
- No exposure of sensitive data in logs or error messages

#### Performance Impact
- Export operations are lightweight database queries
- Import operations include validation overhead but process quickly
- Test suite runs efficiently with mock objects
- No impact on existing token refresh performance

## Previous Versions

### Historical Note
This project has been in development since June 2024. This changelog documents major enhancements added during development iterations in September 2024, focusing on:

1. **OAuth2 Robustness**: Handling refresh token expiration gracefully
2. **Data Portability**: Bidirectional import/export with Developer Portal format  
3. **Test Coverage**: Comprehensive automated testing infrastructure
4. **Documentation**: Complete usage and implementation documentation

### Dependencies
- PHP 8.4+ with OAuth2 and database support
- League OAuth2 Client for CSAS API integration  
- PHPUnit for testing infrastructure
- Bootstrap 5 via Ease framework for UI components
- MySQL/MariaDB or SQLite for data storage

### Migration Notes
- No database migrations required for new functionality
- Existing applications and tokens continue working without changes
- New export feature requires existing application data to be present
- Import feature can be used immediately without any setup
