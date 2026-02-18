<?php

declare(strict_types=1);

namespace App\Providers;

use App\View\Composers\MenuComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        View::composer(
            ['components.navbar', 'components.footer'],
            MenuComposer::class,
        );
    }
}
