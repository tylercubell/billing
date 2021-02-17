<?php

namespace tylercubell\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class BillingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/webhook.php');
        $this->publishes([
            __DIR__ . '/../../config/billing.php' => config_path('billing.php'),
        ], 'billing');

        $billing = new \tylercubell\Billing\Billing;

        // Coupon validation method
        Validator::extend('valid_coupon', function ($attribute, $value, $parameters, $validator) use ($billing) {
            $coupons = $billing->listAllCoupons();

            foreach ($coupons['data'] as $coupon) {
                if ($coupon['id'] === $value) {
                    return true;
                }
            }

            return false;
        }, 'The coupon is invalid.');

        // Plan validation method
        Validator::extend('valid_plan', function ($attribute, $value, $parameters, $validator) use ($billing) {
            $plans = $billing->listAllPlans();

            foreach ($plans['data'] as $plan) {
                if ($plan['id'] === $value) {
                    return true;
                }
            }

            return false;
        }, 'The plan is invalid.');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('billing', function ($app) {
            return new \tylercubell\Billing\Billing;
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \tylercubell\Billing\Console\Commands\SyncCommand::class,
                \tylercubell\Billing\Console\Commands\LinkCommand::class,
                \tylercubell\Billing\Console\Commands\ClearCommand::class,
                \tylercubell\Billing\Console\Commands\BootstrapCommand::class
            ]);
        }
    }
}
