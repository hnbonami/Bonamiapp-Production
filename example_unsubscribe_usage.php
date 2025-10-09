// Voorbeeld hoe je de unsubscribe link genereert in je EmailController

public function sendBulkToCustomers(Request $request)
{
    // ... validation code ...
    
    $customers = Klant::where('email', '!=', null)->get();
    
    foreach ($customers as $customer) {
        // Get or create subscription
        $subscription = EmailSubscription::firstOrCreate([
            'email' => $customer->email,
            'subscriber_type' => 'klant',
            'subscriber_id' => $customer->id
        ]);
        
        // Only send if subscribed
        if ($subscription->isSubscribed()) {
            
            // Generate unsubscribe token if not exists
            if (!$subscription->unsubscribe_token) {
                $subscription->unsubscribe_token = Str::random(64);
                $subscription->save();
            }
            
            $variables = [
                'voornaam' => $customer->voornaam,
                'naam' => $customer->naam,
                'email' => $customer->email,
                'custom_message' => $validated['custom_message'] ?? '',
                'unsubscribe_token' => $subscription->unsubscribe_token,
                'unsubscribe_url' => route('email.unsubscribe', ['token' => $subscription->unsubscribe_token])
            ];
            
            // Send email with unsubscribe link
            // ... rest of email sending code ...
        }
    }
}