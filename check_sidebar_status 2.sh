# Controleer of Database Debug nog bestaat in app.blade.php
grep -n -A2 -B2 "Database Debug" resources/views/layouts/app.blade.php

# Zoek waar Notities & Taken sidebar item staat  
grep -rn "Notities.*Taken" resources/views/layouts/ resources/views/components/

# Zoek Staff Notes in admin component
grep -rn -i "staff.*notes" resources/views/components/sidebar-admin-tab.blade.php 2>/dev/null || echo "Geen staff notes gevonden in admin tab"