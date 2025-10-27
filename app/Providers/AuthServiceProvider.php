<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Klant;
use App\Models\Organisatie;
use App\Models\Testzadel;
use App\Models\Sjabloon;
use App\Models\OrganisatieBranding;
use App\Policies\UserPolicy;
use App\Policies\KlantPolicy;
use App\Policies\OrganisatiePolicy;
use App\Policies\TestzadelPolicy;
use App\Policies\SjabloonPolicy;
use App\Policies\OrganisatieBrandingPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Policy mappings voor authorization
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Klant::class => KlantPolicy::class,
        Organisatie::class => OrganisatiePolicy::class,
        Testzadel::class => TestzadelPolicy::class,
        Sjabloon::class => SjabloonPolicy::class,
        OrganisatieBranding::class => OrganisatieBrandingPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // SuperAdmin bypass - heeft altijd toegang
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });
        
        // Custom Gates voor specifieke acties
        
        // Admin panel toegang
        Gate::define('access-admin-panel', function (User $user) {
            return $user->isSuperAdmin() || $user->isBeheerder();
        });
        
        // Medewerker panel toegang
        Gate::define('access-staff-panel', function (User $user) {
            return $user->isSuperAdmin() || $user->isBeheerder() || $user->isMedewerker();
        });
        
        // Database backup
        Gate::define('backup-database', function (User $user) {
            return $user->isSuperAdmin() || $user->isBeheerder();
        });
        
        // Email integratie beheer
        Gate::define('manage-email-integration', function (User $user) {
            return $user->isSuperAdmin() || $user->isBeheerder() || $user->isMedewerker();
        });
        
        // Analytics toegang
        Gate::define('view-analytics', function (User $user) {
            return $user->isSuperAdmin() || $user->isBeheerder() || $user->isMedewerker();
        });
        
        // Staff notes toegang
        Gate::define('manage-staff-notes', function (User $user) {
            return $user->isSuperAdmin() || $user->isBeheerder() || $user->isMedewerker();
        });
        
        // Prestaties beheer
        Gate::define('manage-prestaties', function (User $user) {
            return $user->isSuperAdmin() || $user->isBeheerder() || $user->isMedewerker();
        });
        
        // Commissies beheer (alleen admin)
        Gate::define('manage-commissies', function (User $user) {
            return $user->isSuperAdmin() || $user->isBeheerder();
        });
    }
}
