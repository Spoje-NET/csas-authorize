# Script Reorganization Summary

## Changes Made

### File Movement
- **Moved**: `bin/import-from-portal.php` → `libexec/import-from-portal.php`
- **Created**: `bin/import-from-portal` (launcher script)

### Updated References
Updated all documentation to use the new command structure:

#### Documentation Files Updated:
1. **README.md**
   - Changed `php bin/import-from-portal.php` → `import-from-portal`
   - Updated all usage examples

2. **DEVELOPER_PORTAL_IMPORT.md**
   - Updated command references to use launcher script
   - Updated backup/restore examples

3. **docs/EXPORT_FUNCTIONALITY.md**
   - Updated integration examples

4. **CHANGELOG.md** 
   - Updated tool description to reflect new structure

#### Debian Package Files:
1. **Created**: `debian/import-from-portal.1` (man page)
2. **Updated**: `debian/manpages` (added new man page)

### New Structure

#### Files in libexec/
- `csas-access-token.php` - Token management and export functionality
- `import-from-portal.php` - Developer Portal import functionality  

#### Launcher Scripts in bin/
- `csas-access` - Main daemon launcher
- `csas-access-token` - Token tool launcher  
- `import-from-portal` - Import tool launcher (NEW)

## Benefits

### Consistent Architecture
- All executable logic now in `/usr/libexec/csas-authorize/`
- All user commands available via `/usr/bin/` launchers
- Follows standard Unix/Linux directory conventions

### Package Management
- Clean separation between libraries and executables
- Proper man pages for all commands
- Consistent installation paths

### User Experience
- Simple command names: `import-from-portal`, `csas-access-token`
- No need to remember full PHP paths
- Tab completion support in shell
- Standard help/man page access

## Usage Examples

### Before (direct PHP execution)
```bash
php bin/import-from-portal.php --file data.json
php libexec/csas-access-token.php --export=1
```

### After (launcher scripts)
```bash
import-from-portal --file data.json
csas-access-token --export=1
```

## Verification

To verify the new structure works:

```bash
# Check launcher exists and is executable
ls -la bin/import-from-portal

# Test help functionality  
import-from-portal --help

# Check man page
man import-from-portal

# Test example usage
import-from-portal --example
```

## Deployment Notes

### For Package Installation
- The `debian/csas-authorize.install` already includes `bin/*` so new launcher will be packaged
- New man page will be installed via updated `debian/manpages`
- No additional configuration needed

### For Development
- Use launcher scripts for testing: `./bin/import-from-portal`
- Direct PHP execution still works: `php libexec/import-from-portal.php`
- All existing functionality preserved

This reorganization provides a cleaner, more maintainable structure while preserving all existing functionality and improving the user experience.
