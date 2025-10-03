# Check het User model - deze velden moeten in $fillable staan:
# 
# In app/Models/User.php zou dit moeten staan:
#
# protected $fillable = [
#     'name',
#     'email', 
#     'password',
#     'avatar_path',
#     'geboortedatum',
#     'adres',
#     'stad', 
#     'postcode',
#     'telefoon',
# ];

# EN in de ProfileSettingsController moeten deze velden worden gevalideerd en opgeslagen.

echo "Check User.php model fillable array"
echo "Check ProfileSettingsController validation en update logic"