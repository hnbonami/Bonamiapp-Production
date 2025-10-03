// In app/Http/Requests/KlantStoreRequest.php or similar validation file
// Change the geslacht validation from:
'geslacht' => 'required|in:man,vrouw'
// to:
'geslacht' => 'required|in:Man,Vrouw,Anders'

// Or check what values are being sent from the frontend dropdown