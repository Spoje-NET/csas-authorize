# CSAS Access Token Tool Unification

## Overview

The `csas-access-token` tool has been enhanced to include all Developer Portal import functionality, creating a unified command-line interface for:

- **Token Management**: List, export, and manage OAuth2 access tokens
- **Application Export**: Export application data to Developer Portal JSON format  
- **Application Import**: Import application data from Developer Portal JSON format

## Merged Functionality

### From `import-from-portal.php`
- Import from JSON file (`--file`)
- Import from JSON string (`--import`) 
- Show example format (`--example`)
- Full validation and error handling

### Existing Token Features
- Token listing (`--list`)
- Token export to .env format (`--tokenId`)
- Application export (`--export`)
- Environment management (`--environment`)

## New Command Structure

### Token Operations
```bash
# List tokens
csas-access-token --list
csas-access-token --list --json

# Export token data
csas-access-token --tokenId=<TOKEN_ID> --output=.env
csas-access-token --tokenId=<TOKEN_ID> --json
```

### Application Export
```bash
# Export by ID or UUID
csas-access-token --export=1 --output=backup.json
csas-access-token --export=71004963-e3d4-471f-96fc-1aef79d17ec1
```

### Application Import (NEW)
```bash
# Import from file
csas-access-token --file=backup.json

# Import from JSON string
csas-access-token --import='{"name":"App", "id":"uuid", ...}'

# Show example format
csas-access-token --example
```

### Help and Information
```bash
# Comprehensive help
csas-access-token --help

# Example JSON format
csas-access-token --example
```

## Unified Command Options

| Option | Short | Description |
|--------|-------|-------------|
| `--help` | `-h` | Show detailed help message |
| `--tokenId` | `-t` | Token ID for token operations |
| `--output` | `-o` | Output file path |
| `--environment` | `-e` | Environment file with DB settings |
| `--list` | `-l` | List available tokens |
| `--json` | `-j` | JSON output format |
| `--export` | `-x` | Export application data |
| `--import` | `-i` | Import from JSON string |
| `--file` | | Import from JSON file |
| `--example` | | Show example JSON format |
| `--accesTokenKey` | `-a` | Custom access token key |
| `--sandboxModeKey` | `-s` | Custom sandbox mode key |

## Benefits of Unification

### User Experience
- **Single Command**: One tool for all CSAS operations
- **Consistent Interface**: Same option patterns and help system
- **Reduced Learning Curve**: Fewer commands to remember
- **Better Help**: Comprehensive help with all options

### Maintenance
- **Single Codebase**: Easier to maintain and update
- **Consistent Error Handling**: Same patterns throughout
- **Unified Testing**: Test all functionality in one place
- **Documentation**: Single man page and help system

### Package Management
- **Simplified Installation**: Fewer files to package
- **Cleaner Dependencies**: Single autoload and initialization
- **Consistent Paths**: All functionality in one binary

## Migration Guide

### For Users
Old commands still work via wrapper scripts:
```bash
# Old way (still works)
import-from-portal --file=data.json

# New way (recommended)
csas-access-token --file=data.json
```

### For Scripts
Update automation scripts to use unified command:
```bash
# Before
import-from-portal --file=backup.json
csas-access-token --export=1 --output=backup.json

# After  
csas-access-token --file=backup.json
csas-access-token --export=1 --output=backup.json
```

## Backward Compatibility

- **Wrapper Script**: `import-from-portal` still exists as launcher
- **All Options**: All original functionality preserved
- **Same Behavior**: Identical output and error handling
- **Documentation**: Old examples still work

## Implementation Details

### Code Structure
- **Unified Options**: Single `getopt()` with all parameters
- **Modular Logic**: Separate blocks for each operation type
- **Shared Dependencies**: Common autoload and initialization
- **Consistent Error Handling**: Same patterns and exit codes

### File Organization
```
libexec/
├── csas-access-token.php  # Unified tool (enhanced)
├── import-from-portal.php # Legacy (can be removed)
└── daemon.php

bin/
├── csas-access-token      # Main launcher
├── import-from-portal     # Legacy wrapper (→ csas-access-token)
└── csas-access
```

## Future Enhancements

The unified tool provides a foundation for additional features:
- **Batch Operations**: Multi-application export/import
- **Validation Mode**: Check data without importing
- **Format Conversion**: Between different JSON schemas
- **Integration Testing**: End-to-end workflow testing

This unification creates a more professional, maintainable, and user-friendly command-line interface while preserving all existing functionality and backward compatibility.
