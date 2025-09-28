# CSAS Developer Portal Data Import

## Přehled

CSAS Authorize nyní podporuje **bidirectional** data flow s CSAS Developer Portal - jak import, tak export dat aplikací.

### Import z Developer Portal
Import dat aplikací z CSAS Developer Portal do CSAS Authorize eliminuje nutnost ručního opisování konfiguračních údajů.

### Export do formátu Developer Portal
Nový export umožňuje exportovat data z CSAS Authorize do formátu kompatibilního s Developer Portal pro zálohu, migraci nebo sdílení mezi instancemi.

## Aktuální stav Developer Portal

K datu 29. září 2025 **CSAS Developer Portal (https://developers.erstegroup.com/) neposkytuje automatizovaný export dat aplikací**. Proto jsme implementovali:
1. **Import nástroj** pro ruční import dat ve formátu JSON 
2. **Export nástroj** pro generování dat ve formátu kompatibilním s Developer Portal

## Implementované nástroje

### Export funkčnost

**Umístění:** `libexec/csas-access-token.php` (rozšířeno o `--export` možnost)

**Použití:**
```bash
# Export aplikace podle ID do stdout
php libexec/csas-access-token.php --export=1

# Export aplikace podle UUID do souboru  
php libexec/csas-access-token.php --export=71004963-e3d4-471f-96fc-1aef79d17ec1 --output=backup.json

# Export s krátkou volbou
php libexec/csas-access-token.php -x 1 -o export.json
```

**Funkce:**
- Export dat z CSAS Authorize do Developer Portal kompatibilního JSON formátu
- Podpora exportu podle ID nebo UUID aplikace
- Filtrování prázdných hodnot pro čistý JSON výstup
- Výstup do souboru nebo stdout pro piping do jiných nástrojů
- Úplná kompatibilita s import nástrojem

### Import funkčnost

**Umístění:** `src/SpojeNet/CSas/DeveloperPortalImporter.php`

**Funkce:**
- Import dat z JSON souboru
- Import dat z JSON řetězce 
- Mapování různých formátů dat z Developer Portal
- Validace povinných polí
- Automatické uložení do databáze

### 2. Webové rozhraní pro import

**Umístění:** `src/import.php`

**Funkce:**
- Formulář pro nahrání JSON souboru
- Možnost vložení JSON dat přímo do textového pole
- Zobrazení příkladu očekávaného formátu JSON
- Průvodce manuálním exportem z Developer Portal

### 3. Integrace do uživatelského rozhraní

- Přidán odkaz "Import from Developer Portal" na hlavní stránku
- Breadcrumb navigace pro snadný návrat
- Automatické přesměrování na detail aplikace po úspěšném importu

## Podporované formáty JSON

### Hierarchická struktura (doporučená)

```json
{
  "name": "Název aplikace",
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

### Plochá struktura (alternativní)

```json
{
  "name": "Název aplikace",
  "uuid": "application-uuid",
  "sandboxClientId": "sandbox-client-uuid",
  "sandboxClientSecret": "sandbox-client-secret",
  "sandboxApiKey": "sandbox-api-key",
  "sandboxRedirectUri": "https://example.com/sandbox/callback",
  "productionClientId": "production-client-uuid", 
  "productionClientSecret": "production-client-secret",
  "productionApiKey": "production-api-key",
  "productionRedirectUri": "https://example.com/production/callback"
}
```

## Mapování polí

Importér automaticky mapuje různé názvy polí z Developer Portal:

| Developer Portal | CSAS Authorize DB |
|------------------|-------------------|
| `name` / `applicationName` | `name` |
| `id` / `applicationId` / `uuid` | `uuid` |
| `logoUrl` / `logo` | `logo` |
| `email` / `contactEmail` | `email` |
| `sandbox.clientId` | `sandbox_client_id` |
| `sandbox.clientSecret` | `sandbox_client_secret` |
| `sandbox.apiKey` | `sandbox_api_key` |
| `sandbox.redirectUri` | `sandbox_redirect_uri` |

## Manuální postup exportu

Protože Developer Portal nemá export funkci:

1. **Přihlaste se do Developer Portal:** https://developers.erstegroup.com/portal/organizations/vitezslav-dvorak/applications
2. **Otevřete detail aplikace** kterou chcete importovat
3. **Zkopírujte následující údaje:**
   - Název aplikace
   - Application ID (UUID)
   - Logo URL (pokud existuje)
   - Sandbox Client ID
   - Sandbox Client Secret  
   - Sandbox API Key
   - Production Client ID
   - Production Client Secret
   - Production API Key
   - Redirect URI pro oba prostředí
4. **Vytvořte JSON** podle výše uvedeného formátu
5. **Použijte import formulář** v CSAS Authorize

## Validace dat

Importér ověřuje:

- **Povinná pole:** `name` a `uuid` musí být vyplněny
- **Alespoň jedno prostředí:** Sandbox NEBO production musí mít kompletní credentials (client_id a client_secret)
- **Formát JSON:** Data musí být ve validním JSON formátu

## Bezpečnostní upozornění

⚠️ **Bezpečnost dat:**
- Client secrets a API klíče jsou citlivé údaje
- Importujte pouze z důvěryhodných zdrojů
- Ujistěte se, že JSON soubory neuchovávate v nezabezpečených místech
- Po importu si ověřte, že údaje jsou správné

## Testování

Vytvořena kompletní testovací sada v `tests/DeveloperPortalImporterTest.php` která pokrývá:

- Import z JSON souboru a dat
- Mapování různých formátů polí
- Validaci povinných údajů
- Zpracování chybových stavů
- Bezpečnostní kontroly

## Bidirectional workflow

Nyní je možný úplný bidirectional workflow:

```bash
# 1. Export z jedné instance CSAS Authorize
php libexec/csas-access-token.php --export=1 --output=backup.json

# 2. Import do jiné instance (nebo stejné instance)
# Import to another instance (or same instance)
import-from-portal --file backup.json

# 3. Nebo přes webové rozhraní
# Nahrajte backup.json přes /import.php
```

### Použití pro zálohy a migrace

```bash
# Záloha všech aplikací (může být automatizováno)
for app_id in $(php -r "/* získej seznam ID aplikací */"); do
    php libexec/csas-access-token.php --export=$app_id --output="backup-$app_id.json"
done

# Obnovení ze zálohy
for backup_file in backup-*.json; do
    import-from-portal --file "$backup_file"
done
```

## Doporučení pro CSAS

**Navrhujeme CSAS aby do Developer Portal přidal:**

1. **Export API** - programový přístup k datům aplikací
2. **Export tlačítko** - možnost stažení JSON/CSV s daty aplikace
3. **Webhook notifikace** - automatická synchronizace změn
4. **Bulk export** - export všech aplikací najednou

To by eliminovalo nutnost ručního kopírování a snížilo riziko chyb.

## Použití

### Z webového rozhraní:
1. Přejděte na `import.php` 
2. Nahrajte JSON soubor NEBO vložte JSON data
3. Klikněte "Import Application"

### Z kódu:
```php
$importer = new DeveloperPortalImporter();

// Import z souboru
$success = $importer->importFromJson('/path/to/export.json');

// Import z pole
$success = $importer->importFromArray($applicationData);

// Získání importované aplikace
$app = $importer->getApplication();
```

Toto řešení poskytuje flexibilní a bezpečný způsob importu dat z CSAS Developer Portal do CSAS Authorize aplikace.
