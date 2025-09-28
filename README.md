# CSAS Authorize

![CSAS Auhorize Logo](src/images/csas-authorize.svg?raw=true)
![GitHub](https://img.shields.io/github/license/Spoje-Net/csas-authorize)


This application provides a simple way to authorize your application with CSAS API.

## Installation

```bash
sudo apt install lsb-release wget apt-transport-https bzip2


wget -qO- https://repo.vitexsoftware.com/keyring.gpg | sudo tee /etc/apt/trusted.gpg.d/vitexsoftware.gpg
echo "deb [signed-by=/etc/apt/trusted.gpg.d/vitexsoftware.gpg]  https://repo.vitexsoftware.com  $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/vitexsoftware.list
sudo apt update

sudo apt install csas-authorize
```

## Usage

1. Register your application at [CSAS Developer Portal](https://developers.csas.cz/)
2. Create new application and get your `client_id` and `client_secret`
3. Open in browser `csas-authorize` and fill in your `client_id` and `client_secret`
4. Open your browser and authorize your application

!['Apps'](apps-screenshot.png?raw=true)
Main screen with list of your applications registered at CSAS Developer Portal

![App](app-screenshot.png?raw=true)
One application can have multiple tokens

!['Token'](token-screenshot.png?raw=true)
Once is token created you can see it's details. Each Refresh Token can have multiple Access Tokens.
Refresh token is valid for 180 days and Access Token for 5 minutes.

## Command line usage

```shell
Usage: csas-access-token [options]

Token Operations:
  --tokenId, -t <ID>   The token ID (required for token operations)
  --output, -o <FILE>  The output file (optional)
  --environment, -e    The environment file with DB_* fields (optional)
  --list, -l           List available tokens
  --json, -j           Export token data in JSON format
  --accesTokenKey, -a  Specify custom Access Token key instead of CSAS_ACCESS_TOKEN
  --sandboxModeKey, -s Specify custom SandBox Mode key instead of CSAS_SANDBOX_MODE

Application Operations:
  --export, -x <ID>    Export application data in Developer Portal format
  --import, -i <JSON>  Import application data from JSON string
  --file <FILE>        Import application data from JSON file
  --example            Show example JSON format for import

General:
  --help, -h           Show help message

Examples:
  # Token operations
  csas-access-token -t 71004963-e3d4-471f-96fc-1aef79d17ec1 -o .env
  csas-access-token --list --json

  # Application export/import
  csas-access-token --export=1 --output=backup.json
  csas-access-token --file=backup.json
  csas-access-token --import='{"name":"Test", "id":"uuid", ...}'
  csas-access-token --example
```

If there is no output file specified, the access token is printed to the standard output. Use the `--json` option to export token data or the token list in JSON format.

## Development

We use two SQL tables to store data. For production we use MariaDB and for development we use SQLite.

### Table `application`

<pre>
+--------------------------+------------------+------+-----+---------------------+-------------------------------+
| Field                    | Type             | Null | Key | Default             | Extra                         |
+--------------------------+------------------+------+-----+---------------------+-------------------------------+
| id                       | int(11) unsigned | NO   | PRI | NULL                | auto_increment                |
| uuid                     | char(36)         | YES  |     | NULL                |                               |
| name                     | varchar(255)     | YES  |     | NULL                |                               |
| logo                     | varchar(255)     | YES  |     | NULL                |                               |
| sandbox_client_id        | char(36)         | YES  |     | NULL                |                               |
| sandbox_client_secret    | varchar(255)     | YES  |     | NULL                |                               |
| sandbox_redirect_uri     | varchar(255)     | YES  |     | NULL                |                               |
| sandbox_api_key          | char(36)         | YES  |     | NULL                |                               |
| production_client_id     | char(36)         | YES  |     | NULL                |                               |
| production_client_secret | varchar(255)     | YES  |     | NULL                |                               |
| production_redirect_uri  | varchar(255)     | YES  |     | NULL                |                               |
| production_api_key       | char(36)         | YES  |     | NULL                |                               |
| created_at               | timestamp        | YES  |     | current_timestamp() |                               |
| updated_at               | timestamp        | YES  |     | current_timestamp() | on update current_timestamp() |
+--------------------------+------------------+------+-----+---------------------+-------------------------------+
</pre>

### Table `token`

<pre>
+----------------+------------------------------+------+-----+---------------------+-------------------------------+
| Field          | Type                         | Null | Key | Default             | Extra                         |
+----------------+------------------------------+------+-----+---------------------+-------------------------------+
| id             | int(11) unsigned             | NO   | PRI | NULL                | auto_increment                |
| application_id | int(11)                      | NO   |     | NULL                |                               |
| environment    | enum('sandbox','production') | YES  |     | NULL                |                               |
| access_token   | varchar(550)                 | YES  |     | NULL                |                               |
| refresh_token  | varchar(550)                 | YES  |     | NULL                |                               |
| expires_in     | int(11)                      | YES  |     | NULL                |                               |
| scope          | varchar(255)                 | YES  |     | NULL                |                               |
| created_at     | timestamp                    | YES  |     | current_timestamp() |                               |
| updated_at     | timestamp                    | YES  |     | current_timestamp() | on update current_timestamp() |
| uuid           | char(36)                     | YES  |     | NULL                |                               |
+----------------+------------------------------+------+-----+---------------------+-------------------------------+
</pre>

## Data Import/Export with Developer Portal

CSAS Authorize supports bidirectional data flow with CSAS Developer Portal format for easy migration and backup.

### Import from Developer Portal

#### Web Interface

1. Navigate to the main page and click "Import from Developer Portal"
2. Upload a JSON file or paste JSON data directly
3. The application will be automatically imported and saved

#### Command Line

```bash
# Import from JSON file
csas-access-token --file ./example-import.json

# Import from JSON string  
csas-access-token --import '{"name":"My App", "id":"uuid", ...}'

# Show example JSON format
csas-access-token --example
```

### Export to Developer Portal Format

#### Command Line

```bash
# Export application by ID to stdout
php libexec/csas-access-token.php --export=1

# Export application by UUID to file
php libexec/csas-access-token.php --export=71004963-e3d4-471f-96fc-1aef79d17ec1 --output=backup.json

# Short form using -x option
php libexec/csas-access-token.php -x 1 -o export.json
```

### JSON Format

The import/export system uses a standardized JSON format compatible with Developer Portal data:

```json
{
  "name": "Application Name",
  "id": "application-uuid-from-portal",
  "logoUrl": "https://example.com/logo.png",
  "email": "developer@example.com",
  "sandbox": {
    "clientId": "sandbox-client-uuid",
    "clientSecret": "sandbox-client-secret",
    "apiKey": "sandbox-api-key-uuid",
    "redirectUri": "https://myapp.example.com/sandbox/callback"
  },
  "production": {
    "clientId": "production-client-uuid", 
    "clientSecret": "production-client-secret",
    "apiKey": "production-api-key-uuid",
    "redirectUri": "https://myapp.example.com/production/callback"
  }
}
```

### Bidirectional Workflow

```bash
# Export from one instance
csas-access-token --export=1 --output=backup.json

# Import to another instance (or same instance)
csas-access-token --file backup.json
```

For detailed documentation:
- Import instructions: [DEVELOPER_PORTAL_IMPORT.md](DEVELOPER_PORTAL_IMPORT.md)
- Export functionality: [docs/EXPORT_FUNCTIONALITY.md](docs/EXPORT_FUNCTIONALITY.md)
