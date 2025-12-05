<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Models\Project;
use App\Models\Review;
use App\Models\Team; // Import Team Model
use App\Policies\ProjectPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\UserPolicy; // Import UserPolicy
use App\Policies\TeamPolicy; // Import TeamPolicy

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);

        Gate::before(function (User $user, string $ability) {
            if (str_contains($ability, '.')) {
                return $user->hasPermission($ability) ? true : null;
            }
            return null;
        });
    }
}
