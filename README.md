![CSAS Auhorize Logo](src/images/csas-authorize.svg?raw=true)

# CSAS Authorize

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
Usage: php csas-access-token --tokenId=<TOKEN_ID> [--output=<OUTPUT_FILE>] [--environment=<ENVIRONMENT>] [--list]
Options:
  --tokenId, -t        The token ID (required)
  --output, -o         The output file (optional)
  --environment, -e    The environment file with DB_* fields (optional)
  --list, -l           List available tokens (optional)
  --accesTokenKey, -a  Specify custom Access Token key instead of ACCESS_TOKEN
  --sandboxModeKey, -s Specify custom SandBox Mode key instead of SANDBOX_MODE

Example:  csas-access-token -t71004963-e3d4-471f-96fc-1aef79d17ec1 -aCSAS_TOKEN -o.env
```

If there is no output file specified, the access token is printed to the standard output.


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
