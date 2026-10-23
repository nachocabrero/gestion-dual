<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Registrar STR_SORT_KEY en conexiones SQLite para soporte de ordenación insensible a acentos
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Database\Events\ConnectionEstablished::class, function ($event) {
            if ($event->connection->getDriverName() === 'sqlite') {
                try {
                    $event->connection->getPdo()->sqliteCreateFunction('STR_SORT_KEY', function ($val) {
                        return \App\Support\StrSort::normalize($val);
                    });
                } catch (\Throwable $e) {
                    // Ignorar si no soporta sqliteCreateFunction
                }
            }
        });
    }
}
