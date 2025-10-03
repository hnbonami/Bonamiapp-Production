# Handmatige Database Update Instructies

## Optie 1: Via TablePlus (Aanbevolen)
1. Open TablePlus
2. Verbind met je lokale database (bonamiapp)
3. Voer deze SQL query uit:

```sql
ALTER TABLE staff_notes 
MODIFY COLUMN tile_size ENUM('mini', 'small', 'medium', 'large', 'banner') 
DEFAULT 'medium';
```

## Optie 2: Via Herd Database
1. Open Herd app
2. Ga naar Sites > bonamiapp
3. Klik op "Database" 
4. Voer de bovenstaande SQL uit

## Optie 3: Via phpMyAdmin
1. Ga naar http://localhost:8080/phpMyAdmin (of je phpMyAdmin URL)
2. Selecteer database 'bonamiapp'
3. Ga naar SQL tab
4. Voer de bovenstaande SQL uit

## Optie 4: Via Terminal (probeer verschillende wachtwoorden)
```bash
# Probeer leeg wachtwoord (gewoon Enter drukken)
mysql -u root -p bonamiapp

# Of probeer standaard wachtwoord:
# wachtwoord: password
# wachtwoord: root
# wachtwoord: (leeg - alleen Enter)
```

## Verificatie
Na het uitvoeren van de SQL, controleer of het gelukt is:
```sql
DESCRIBE staff_notes;
```

Je zou moeten zien:
`tile_size | enum('mini','small','medium','large','banner') | YES | | medium |`