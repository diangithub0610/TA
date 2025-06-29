<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    // Tambahkan di app/Console/Kernel.php pada method schedule()

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Update tracking setiap 4 jam
        $schedule->command('tracking:update')
            ->everyFourHours()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tracking-cron.log'));

        // Update tracking intensif pada jam kerja (8-17, setiap 2 jam)
        $schedule->command('tracking:update')
            ->hourlyAt([8, 10, 12, 14, 16])
            ->weekdays()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/tracking-intensive.log'));

        // Cleanup log tracking yang sudah lama (setiap minggu)
        $schedule->call(function () {
            $logFile = storage_path('logs/tracking-cron.log');
            if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) { // 10MB
                // Backup log lama
                rename($logFile, $logFile . '.old');
                // Buat log baru
                touch($logFile);
            }
        })->weekly();

        // Notifikasi pengiriman yang sudah lama tidak ada update (setiap hari jam 9 pagi)
        $schedule->call(function () {
            $pengirimanStagnan = \App\Models\Pengiriman::whereNotNull('nomor_resi')
                ->whereNotIn('status_pengiriman', ['terkirim', 'gagal_kirim'])
                ->where(function ($query) {
                    $query->whereNull('terakhir_update_tracking')
                        ->orWhere('terakhir_update_tracking', '<', now()->subDays(2));
                })
                ->count();

            if ($pengirimanStagnan > 0) {
                \Illuminate\Support\Facades\Log::warning("Ada {$pengirimanStagnan} pengiriman yang tidak ada update tracking selama 2 hari");

                // Kirim notifikasi ke admin jika perlu
                // \Illuminate\Support\Facades\Notification::route('mail', 'admin@example.com')
                //     ->notify(new \App\Notifications\PengirimanStagnanNotification($pengirimanStagnan));
            }
        })->dailyAt('09:00');
    }

    // Tambahkan juga di method commands() untuk register command
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
