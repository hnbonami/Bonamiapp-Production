# Backup database eerst
php artisan tinker --execute="
\$backup = [];
\$backup['staff_notes'] = DB::table('staff_notes')->get()->toArray();
file_put_contents('staff_notes_backup_'.date('Y-m-d_H-i-s').'.json', json_encode(\$backup, JSON_PRETTY_PRINT));
echo 'Staff notes backup gemaakt\n';
"

# Voer de migratie uit
php artisan migrate --path=database/migrations/2025_09_30_202800_add_open_in_new_tab_to_staff_notes_table.php

echo "✅ Migratie uitgevoerd voor open_in_new_tab kolom"