# Finale check - controleer of alle 3 items weg zijn
echo "=== FINALE CONTROLE SIDEBAR ITEMS ==="

echo -e "\n1. Database Debug check:"
grep -n "Database Debug" resources/views/layouts/app.blade.php || echo "✅ Database Debug is WEG"

echo -e "\n2. Notities & Taken check:"
grep -n "Notities.*Taken" resources/views/components/sidebar-notes-tab.blade.php | head -1 || echo "✅ Notities & Taken is uitgeschakeld"

echo -e "\n3. Staff Notes in admin check:"
grep -n -i "staff.*notes" resources/views/components/sidebar-admin-tab.blade.php || echo "✅ Staff Notes niet gevonden in admin tab"

echo -e "\n=== RESULTAAT ==="
echo "Als alle items ✅ zijn, dan zijn de sidebar wijzigingen succesvol!"