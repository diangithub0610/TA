<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TrackingService;

class UpdateTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:update {--force : Force update even if recently updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update tracking status untuk semua pengiriman aktif';

    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        parent::__construct();
        $this->trackingService = $trackingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai update tracking...');

        $startTime = now();

        try {
            $result = $this->trackingService->updateSemuaTracking();

            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);

            $this->info("Update tracking selesai!");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Pengiriman', $result['total']],
                    ['Berhasil Update', $result['berhasil']],
                    ['Gagal Update', $result['gagal']],
                    ['Durasi', $duration . ' detik'],
                    ['Waktu Selesai', $endTime->format('Y-m-d H:i:s')]
                ]
            );

            if ($result['gagal'] > 0) {
                $this->warn("Ada {$result['gagal']} pengiriman yang gagal diupdate. Silakan cek log untuk detail.");
            }
        } catch (\Exception $e) {
            $this->error('Error saat update tracking: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
