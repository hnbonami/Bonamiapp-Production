// Clear cache script
echo "Clearing Laravel cache...\n";
exec('cd /Users/hannesbonami/Desktop/Bonamiapp && php artisan route:clear');
exec('cd /Users/hannesbonami/Desktop/Bonamiapp && php artisan config:clear');
exec('cd /Users/hannesbonami/Desktop/Bonamiapp && php artisan cache:clear');
exec('cd /Users/hannesbonami/Desktop/Bonamiapp && php artisan route:cache');
echo "Cache cleared!\n";