#!/bin/bash

# EXTRA VEILIGHEIDSBACKUP VOOR SIDEBAR WIJZIGINGEN
# Datum: $(date)
# Commit voor wijzigingen: 23264cc

# Backup van belangrijkste bestanden
cp resources/views/layouts/app.blade.php resources/views/layouts/app.blade.php.backup-$(date +%Y%m%d-%H%M%S)

echo "✅ Extra backup gemaakt van app.blade.php"
echo "Als er iets misgaat, kun je het .backup bestand terugzetten"