# csas-authorize Zabbix monitoring

Package `csas-authorize-zabbix` (same source as `csas-authorize`, depends on `zabbix-agent2`).

* `csas-zabbix-token` - helper: `discovery`, `count`, `refresh_days <uuid>`, `access_seconds <uuid>`
* `csas-authorize.conf` - agent2 `UserParameter`s (`/etc/zabbix/zabbix_agent2.d/`)
* `csas_authorize_template.yaml` - Zabbix 7.2 template (import it and link it to the host)

Refresh token lasts 180 days (counted from the token creation); `-1` means the refresh token is missing.
Triggers: High `<=0`/missing, Average `<7`, Warning `<14` days (macros `{$CSAS.TOKEN.HIGH_DAYS}`, `{$CSAS.TOKEN.WARN_DAYS}`).
The agent user (`zabbix`) must be able to read `/etc/csas-authorize/csas-authorize.env` (default `664 root:www-data`).

Test: `zabbix_agent2 -t csas.authorize.token.lld`
