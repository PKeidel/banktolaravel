<?php

namespace PKeidel\BankToLaravel\Providers;

use Illuminate\Support\ServiceProvider;

class BankToLaravelServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $this->loadViewsFrom(__DIR__.'/../views', 'banktolaravel');

        if ($this->app->runningInConsole()) {
            $this->commands([
	            \PKeidel\BankToLaravel\Commands\ImportEntries::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../migrations' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
    }
}
