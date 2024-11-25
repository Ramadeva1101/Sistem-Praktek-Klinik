<?php

namespace App\Console\Commands;

use App\Models\Kasir;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeleteOldKasirData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kasir:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus data kasir yang sudah lebih dari 24 jam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai proses penghapusan data kasir...');

        try {
            $now = Carbon::now()->setTimezone('Asia/Makassar');
            $this->info("Waktu sekarang: " . $now->format('d F Y H:i:s'));

            $oldKasir = Kasir::where('status_pembayaran', 'Sudah Dibayar')
                ->where('tanggal_pembayaran', '<=', $now->copy()->subHours(24))
                ->get();

            $count = $oldKasir->count();

            if ($count > 0) {
                foreach ($oldKasir as $kasir) {
                    $this->info("Menghapus data kasir: {$kasir->kode_pelanggan}");
                    Log::info('Menghapus data kasir:', [
                        'id' => $kasir->id,
                        'kode_pelanggan' => $kasir->kode_pelanggan,
                        'tanggal_pembayaran' => $kasir->tanggal_pembayaran
                    ]);
                    $kasir->delete();
                }

                $this->info("Berhasil menghapus {$count} data kasir");
                Log::info("Berhasil menghapus {$count} data kasir");
            } else {
                $this->info('Tidak ada data kasir yang perlu dihapus');
                Log::info('Tidak ada data kasir yang perlu dihapus');
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Error saat menghapus data kasir:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        $this->info('Proses selesai');
    }
}
