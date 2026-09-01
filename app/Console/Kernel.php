<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Limpiar notificaciones expiradas diariamente a las 3:00
        $schedule->call(function () {
            $service = app(\App\Services\NotificacionService::class);
            $eliminated = $service->limpiarExpiradas();
            if ($eliminated > 0) {
                Log::info("Notificaciones expiradas eliminadas: {$eliminated}");
            }
        })->dailyAt('03:00');

        // El 1 de agosto, previsualiza la promoción anual para que el admin la revise.
        // La promoción real se aplica con: php artisan academico:promocion-anual --curso-destino=2026-2027
        $schedule->call(function () {
            Log::info('Promoción anual: revisar y aplicar con el comando "academico:promocion-anual --curso-destino=<año-año>".');
        })->yearlyOn(8, 1, '00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}