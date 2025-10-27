<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\DashboardWidget;
use App\Policies\DashboardWidgetPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\NewsItem::class => \App\Policies\NewsItemPolicy::class,
        DashboardWidget::class => DashboardWidgetPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', function ($user) {
            \Log::info('Gate admin check', ['user' => $user]);
            return $user && $user->role === 'admin';
        });
    }
}
