<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penyewa;
use App\Helpers\WhatsAppHelper;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class SendMonthlyReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly rent reminders to tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Cron job is working');

        $penyewa = Penyewa::with('kamar:id,harga_kamar,nama_kamar')
            ->select('id', 'nama_penyewa', 'nomor_wa', 'tanggal_masuk', 'id_kamar')
            ->where('status', 'aktif')
            ->get();

        // Menggunakan waktu saat ini
        $today = Carbon::now()->startOfDay();

        foreach ($penyewa as $key) {
            // Ambil day dari tanggal_masuk
            $day = Carbon::parse($key->tanggal_masuk)->day;

            // Tanggal jatuh tempo untuk bulan ini
            $dueDate = Carbon::create($today->year, $today->month, $day, 0, 0, 0);

            // Jika tanggal jatuh tempo sudah lewat, gunakan bulan depan
            if ($today->greaterThan($dueDate)) {
                $dueDate = $dueDate->addMonth();
            }

            // Tanggal pengingat 7 hari sebelum jatuh tempo
            $reminderDate = $dueDate->copy()->subDays(7);

            // Jika hari ini adalah tanggal pengingat
            if ($today->isSameDay($reminderDate)) {
                try {
                    // Format pesan berdasarkan aplikasi format tagihan
                    $message = applikasi('format_tagihan')['value'];
                    $hargaKamar = rupiah($key->kamar->harga_kamar ?? 0);

                    // Format target untuk pengiriman WhatsApp
                    $target = "{$key->nomor_wa}|{$key->nama_penyewa}|{$hargaKamar}|{$key->kamar->nama_kamar}";

                    // Mengirim pesan WhatsApp
                    WhatsAppHelper::sendWhatsApp($target, $message);
                    Log::info('Reminder sent to ' . $key->nama_penyewa);
                } catch (\Exception $e) {
                    $this->error('Failed to send reminder to ' . $key->nama_penyewa . ': ' . $e->getMessage());
                }
            }
        }
    }
}
