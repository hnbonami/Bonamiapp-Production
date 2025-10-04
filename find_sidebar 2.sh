# Zoek waar de sidebar wordt gedefinieerd
grep -r "Notities & Taken" resources/views/ 2>/dev/null
grep -r "Database Debug" resources/views/ 2>/dev/null  
grep -r "Staff Notes" resources/views/ 2>/dev/null

# Zoek layout bestanden
find resources/views -name "*layout*" -o -name "*sidebar*" -o -name "*navigation*" -o -name "*menu*"

# Zoek in app.blade.php
grep -n -A5 -B5 "Notities\|Database\|Staff" resources/views/layouts/app.blade.php 2>/dev/null