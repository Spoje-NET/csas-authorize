# AGENTS.md - csas-authorize

## What it is
PHP web application (Debian package `csas-authorize`) that obtains and renews OAuth2 tokens for the
Česká spořitelna / Erste (CSAS) API. Applications and tokens live in a SQL database (SQLite or MariaDB).
Source: https://github.com/Spoje-NET/csas-authorize

The refresh token is valid 180 days; after that a person has to reauthorize the application in the bank
(`auth.php?id=<application id>`). The access token is short-lived and renewed automatically.

## Layout
```
src/                  web pages (index, application, token, auth, welcomeback, import ...) -> /usr/share/csas-authorize
src/SpojeNet/CSas/    classes (Application, Token, Auth, Notificator, ZabbixMonitor ...)     -> /usr/lib/csas-authorize/CSas
libexec/              CLI scripts (csas-access-token, daemon)                                 -> /usr/libexec/csas-authorize
bin/                  wrappers for libexec scripts                                            -> /usr/bin
zabbix/               Zabbix helper, agent2 UserParameters and template (package csas-authorize-zabbix)
db/migrations/        Phinx migrations
tests/                PHPUnit tests
debian/               packaging (static debian/autoload.php, no composer at runtime)
```

## Debian packages built from this source
- `csas-authorize` - application, `csas-authorize-sqlite` / `csas-authorize-mysql` - database backend (dbconfig-common)
- `multiflexi-csas-authorize` - MultiFlexi integration
- `csas-authorize-zabbix` - Zabbix agent2 monitoring (Depends: zabbix-agent2). Keys `csas.authorize.token.lld`,
  `csas.authorize.token.count`, `csas.authorize.token.refresh_days[uuid]`, `csas.authorize.token.access_seconds[uuid]`.
  Template: `zabbix/csas_authorize_template.yaml` (triggers: refresh token days left <14 warning, <7 average, <=0 or missing high).
  The `zabbix` user must read `/etc/csas-authorize/csas-authorize.env` (and the SQLite directory - postinst adds it to `www-data`).

## Rules (see /home/vitex/Projects/AGENTS.md)
- Autoloader is the static `debian/autoload.php`; never run composer in packaging or postinst. Entry points are rewritten by `debian/rules`.
- `debian/Jenkinsfile` builds the testing repo, `debian/Jenkinsfile.release` the production one (branch `production`).
  Both keep the `buildBlocker`; `debian:forky` stays disabled; no `--ipc=host`.
- Files under `debian/` in `*.install` need the `debian/` prefix.

## Development
```bash
composer install
vendor/bin/phpunit            # or: make phpunit
dpkg-buildpackage -us -uc -b  # local .deb
```
Commit messages are generated with the local LLM (see global instructions). Token secrets must never be logged or committed.

## Operations
- Daemon `csas-authorize.service` sends e-mail notifications about expiring tokens (`libexec/daemon.php`).
- `csas-access-token --list|--import|--export` manages tokens and applications from CLI.
- Deployments: testing on vyvojar.spoje.net, production on multi.spojenet.cz (Ubuntu noble, MySQL).
