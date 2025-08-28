<?php

namespace App\Console;

use App\Console\Commands\GenerateDailyQrCode;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        GenerateDailyQrCode::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Schedule to run daily at 5:00 AM server time
        $schedule->command('attendance:generate-daily-qrcode')->dailyAt('5:00')->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
