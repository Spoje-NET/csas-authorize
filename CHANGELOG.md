# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
