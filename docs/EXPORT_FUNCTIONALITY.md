# CSAS Access Token Export Functionality

## Overview

The `csas-access-token.php` tool has been enhanced with export functionality that allows exporting application data in a format compatible with the Developer Portal import system.

## Usage

### Export Application Data

```bash
# Export application by ID to stdout
php libexec/csas-access-token.php --export=<APPLICATION_ID>

# Export application by UUID to stdout  
php libexec/csas-access-token.php --export=<APPLICATION_UUID>

# Export application to a specific file
php libexec/csas-access-token.php --export=<APPLICATION_ID> --output=export.json

# Short form using -x option
php libexec/csas-access-token.php -x <APPLICATION_ID> -o export.json
```

### Examples

```bash
# Export application with ID 1 to stdout
php libexec/csas-access-token.php --export=1

# Export application with UUID to file
php libexec/csas-access-token.php --export=71004963-e3d4-471f-96fc-1aef79d17ec1 --output=my-app-export.json

# Export application using short options
php libexec/csas-access-token.php -x 1 -o /tmp/app-backup.json
```

## Export Format

The export generates JSON data in the same format expected by the `import-from-portal.php` tool:

```json
{
    "name": "My Application",
    "id": "71004963-e3d4-471f-96fc-1aef79d17ec1",
    "logoUrl": "https://example.com/logo.png",
    "email": "admin@example.com",
    "sandbox": {
        "clientId": "sandbox_client_id_here",
        "clientSecret": "sandbox_client_secret_here", 
        "apiKey": "sandbox_api_key_here",
        "redirectUri": "https://example.com/callback"
    },
    "production": {
        "clientId": "prod_client_id_here",
        "clientSecret": "prod_client_secret_here",
        "apiKey": "prod_api_key_here", 
        "redirectUri": "https://example.com/callback"
    }
}
```

## Field Mapping

The export maps CSAS Authorize database fields to Developer Portal format:

| CSAS Authorize Field | Developer Portal Field | Environment |
|---------------------|------------------------|-------------|
| `name`              | `name`                 | -           |
| `uuid`              | `id`                   | -           |
| `logo`              | `logoUrl`              | -           |
| `email`             | `email`                | -           |
| `sandbox_client_id` | `sandbox.clientId`     | Sandbox     |
| `sandbox_client_secret` | `sandbox.clientSecret` | Sandbox |
| `sandbox_api_key`   | `sandbox.apiKey`       | Sandbox     |
| `sandbox_redirect_uri` | `sandbox.redirectUri` | Sandbox   |
| `production_client_id` | `production.clientId` | Production |
| `production_client_secret` | `production.clientSecret` | Production |
| `production_api_key` | `production.apiKey`   | Production  |
| `production_redirect_uri` | `production.redirectUri` | Production |

## Features

### Data Validation
- Validates that the application exists in the database
- Supports lookup by both numeric ID and UUID
- Removes empty values from export to keep JSON clean
- Only includes environment sections if they have actual data

### Output Options
- Stdout output (default) for piping to other tools
- File output using `--output` option
- Pretty-formatted JSON with proper indentation
- Success/failure messages with byte count information

### Error Handling
- Clear error messages for missing applications
- Proper exit codes for scripting integration
- Informative usage help when parameters are missing

## Integration with Import System

The exported JSON is fully compatible with the existing import tools:

```bash
# Export from one instance
php libexec/csas-access-token.php --export=1 --output=backup.json

# Import to another instance (or same instance) 
import-from-portal backup.json

# Or use the web interface
# Upload backup.json through the web import form at /import.php
```

## Bidirectional Data Flow

This export functionality completes the bidirectional data flow:

1. **Developer Portal → CSAS Authorize**: Import using `import-from-portal.php` or web interface
2. **CSAS Authorize → Developer Portal**: Export using `csas-access-token.php --export`  
3. **CSAS Authorize → CSAS Authorize**: Full backup/restore capability

## Security Considerations

- Exported data includes sensitive client secrets and API keys
- Ensure exported files are handled securely
- Consider encrypting exported files for storage
- Review file permissions on output files
- Never commit exported files to version control

## Command Line Options

The tool now supports these options:

| Option | Short | Description |
|--------|-------|-------------|
| `--help` | `-h` | Show help message |
| `--export` | `-x` | Export application data (requires APPLICATION_ID) |
| `--output` | `-o` | Output file path (optional, defaults to stdout) |
| `--environment` | `-e` | Environment file with DB_* fields |
| `--list` | `-l` | List available tokens |
| `--json` | `-j` | JSON output format for token operations |
| `--tokenId` | `-t` | Token ID for token operations |

## Return Codes

- `0`: Success
- `1`: Error (missing application, invalid parameters, write failure)

## Implementation Details

The export functionality:

1. Parses command line arguments for `--export` or `-x` options
2. Validates the application ID/UUID parameter is provided
3. Loads the application from the database using either numeric ID or UUID
4. Maps database fields to Developer Portal JSON format
5. Filters out empty values and unused environment sections
6. Outputs formatted JSON to stdout or specified file
7. Reports success/failure with appropriate exit codes
