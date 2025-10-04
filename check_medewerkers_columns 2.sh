#!/bin/bash

# Controleer alle bestaande kolommen in medewerkers tabel

echo "📊 Alle kolommen in medewerkers tabel:"
mysql -u Hannes -pHannes1986 Bonamisportcoaching -e "DESCRIBE medewerkers;" 

echo ""
echo "🔍 Zoek naar rol en toegang gerelateerde kolommen:"
mysql -u Hannes -pHannes1986 Bonamisportcoaching -e "DESCRIBE medewerkers;" | grep -i -E "(rol|access|auth|permission|right|level|privilege)"

echo ""
echo "📋 Volledige tabel structuur voor analyse:"
mysql -u Hannes -pHannes1986 Bonamisportcoaching -e "SHOW CREATE TABLE medewerkers\G"