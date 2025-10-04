// Check KlantenController store method voor adresgegevens validatie
// Adresgegevens die moeten worden toegevoegd aan validatie:
// 'straatnaam' => 'nullable|string|max:255',
// 'huisnummer' => 'nullable|string|max:10', 
// 'postcode' => 'nullable|string|max:10',
// 'stad' => 'nullable|string|max:100',

// En in de Klant::create array moeten deze velden ook worden toegevoegd