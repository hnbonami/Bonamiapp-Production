<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * This stack is run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // ...existing middleware...
        \App\Http\Middleware\ReplaceReportPlaceholders::class,
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // ...existing middleware...
    ];

    /**
     * The application's middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // ...existing middleware...
            \App\Http\Middleware\ApplyOrganisatieBranding::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * @var array
     */
    protected $middlewareAliases = [
        // ...existing code...
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'feature' => \App\Http\Middleware\CheckFeatureAccess::class,
    ];
}