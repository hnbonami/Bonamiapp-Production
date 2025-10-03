# Check de huidige staff_notes tabel structuur
php artisan tinker --execute="
Schema::getColumnListing('staff_notes')
"

# Kijk of de kolom al bestaat
php artisan tinker --execute="
if (Schema::hasColumn('staff_notes', 'open_in_new_tab')) {
    echo 'open_in_new_tab kolom bestaat al';
} else {
    echo 'open_in_new_tab kolom ontbreekt - moet worden toegevoegd';
}
"