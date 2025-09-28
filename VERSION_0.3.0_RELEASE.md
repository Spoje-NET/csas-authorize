# Version 0.3.0 Release Summary

## Files Updated for Version 0.3.0

### Debian Packaging
- `debian/changelog`: Added new entry for 0.3.0 with comprehensive feature list
- `debian/tmp/composer.json`: Updated version from 0.2.0 to 0.3.0

### Project Files
- `composer.json`: Added version field set to 0.3.0
- `CHANGELOG.md`: Marked 0.3.0 as released on 2024-09-29

## Version 0.3.0 Features

This release includes major enhancements:

### 🔄 Tool Unification
- Merged `import-from-portal` functionality into `csas-access-token`
- Single unified command-line interface for all operations
- Backward compatibility maintained with wrapper scripts

### 🛡️ Enhanced OAuth2 Handling
- Graceful handling of expired refresh tokens
- Automatic redirect to re-authorization flow
- Improved error messages and user experience

### 📊 Developer Portal Integration
- Bidirectional import/export with CSAS Developer Portal
- JSON format compatibility and validation
- Web and CLI interfaces for data import

### 🧪 Comprehensive Testing
- Full PHPUnit test suite implementation
- Unit and integration test coverage
- Mock-based testing for external dependencies

### 📚 Improved Documentation
- Updated README with unified command structure
- Enhanced man pages with all new options
- Comprehensive documentation for all features

## Package Information

**Package Name**: csas-authorize
**Version**: 0.3.0
**Distribution**: noble (Ubuntu)
**Urgency**: medium
**Maintainer**: Vítězslav Vitex Dvořák <info@vitexsoftware.cz>

This version represents a significant milestone in the project's evolution, providing a more robust, user-friendly, and feature-complete solution for CSAS authorization management.
