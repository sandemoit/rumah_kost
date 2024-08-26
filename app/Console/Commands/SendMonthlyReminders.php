<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penyewa;
use App\Helpers\WhatsAppHelper;
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

        // Mengambil penyewa yang aktif beserta harga kamar terkait
        $penyewa = Penyewa::with('kamar:id,harga_kamar')
            ->select('id', 'nama_penyewa', 'nomor_wa', 'tanggal_masuk')
            ->where('status', 'aktif')
            ->get();

        $today = Carbon::now();

        foreach ($penyewa as $human) {
            // Ambil day dari tanggal_masuk
            $day = Carbon::parse($human->tanggal_masuk)->day;

            // Tanggal jatuh tempo untuk bulan ini
            $dueDate = $today->startOfMonth()->addDays($day - 1);
            dd($dueDate);

            // Jika tanggal jatuh tempo sudah lewat, gunakan bulan depan
            if ($today->greaterThan($dueDate)) {
                $dueDate = $dueDate->addMonth();
            }

            // Tanggal pengingat 7 hari sebelum jatuh tempo
            $reminderDate = $dueDate->copy()->subDays(7);

            // Jika hari ini adalah tanggal pengingat
            if ($today->isSameDay($reminderDate)) {
                try {
                    $message = applikasi('format_tagihan')['value'];
                    $hargaKamar = $human->kamar->harga_kamar ?? 0; // Pastikan harga_kamar ada

                    $target = "{$human->nomor_wa}|{$human->nama_penyewa}|{$hargaKamar}";

                    WhatsAppHelper::sendWhatsApp($target, $message);
                    $this->info('Reminder sent to ' . $human->nama_penyewa);
                } catch (\Exception $e) {
                    $this->error('Failed to send reminder to ' . $human->nama_penyewa . ': ' . $e->getMessage());
                }
            }
        }
    }
}
