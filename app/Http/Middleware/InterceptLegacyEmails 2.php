<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\EmailIntegrationService;
use Illuminate\Support\Facades\Mail;

class InterceptLegacyEmails
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Store original Mail facade
        $originalMail = app('mailer');
        
        // Override Mail::send to use new template system
        Mail::macro('legacySend', function ($view, $data = [], $callback = null) {
            $emailService = new EmailIntegrationService();
            
            // Map old view names to new template system
            return $emailService->sendLegacyEmail($view, $data, $callback);
        });
        
        return $next($request);
    }
}