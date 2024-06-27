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
        $penyewa = Penyewa::select('id', 'nama_penyewa', 'nomor_wa', 'tanggal_masuk')->where('status', 'aktif')->get();

        foreach ($penyewa as $human) {
            // Ambil day dari tanggal_masuk
            $day = Carbon::parse($human->tanggal_masuk)->day;

            // Tanggal jatuh tempo untuk bulan ini
            $dueDate = Carbon::now()->startOfMonth()->addDays($day - 1);

            // Jika tanggal jatuh tempo sudah lewat, gunakan bulan depan
            if (Carbon::now()->greaterThan($dueDate)) {
                $dueDate = $dueDate->addMonth();
            }

            // Tanggal pengingat 7 hari sebelum jatuh tempo
            $reminderDate = $dueDate->copy()->subDays(7);

            // Jika hari ini adalah tanggal pengingat
            if (Carbon::now()->isSameDay($reminderDate)) {
                try {
                    $message = "Reminder: Please pay your rent for next month.";
                    WhatsAppHelper::sendWhatsApp($human->nomor_wa, $message);
                    $this->info('Reminder sent to ' . $human->nama_penyewa);
                } catch (\Exception $e) {
                    $this->error('Failed to send reminder to ' . $human->nama_penyewa . ': ' . $e->getMessage());
                }
            }
        }
    }
}
