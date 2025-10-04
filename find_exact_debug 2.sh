# Vind exact de Database Debug regels
grep -n "Database Debug" resources/views/layouts/app.blade.php

# Toon de context rond die regels  
sed -n '220,230p' resources/views/layouts/app.blade.php