<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Passport::tokensExpireIn(CarbonInterval::minute(60)); //access token
        Passport::refreshTokensExpireIn(CarbonInterval::month(1)); //refresh token
        Passport::personalAccessTokensExpireIn(CarbonInterval::month(1)); //personal access token
        Passport::enablePasswordGrant();
    }
}
