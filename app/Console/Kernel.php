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