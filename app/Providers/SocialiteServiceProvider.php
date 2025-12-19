<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;

class SocialiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $socialite = $this->app->make(Factory::class);

        $socialite->extend(
            'discord',
            function ($app) use ($socialite) {
                $config = $app['config']['services.discord'];
                return $socialite->buildProvider(
                    \SocialiteProviders\Discord\Provider::class,
                    $config
                );
            }
        );
    }
}

