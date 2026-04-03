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
        Passport::tokensExpireIn(CarbonInterval::second(100)); //access token
        Passport::refreshTokensExpireIn(CarbonInterval::second(200)); //refresh token
        Passport::personalAccessTokensExpireIn(CarbonInterval::second(300)); //personal access token
        Passport::enablePasswordGrant();
    }
}
