<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireStaleBookings extends Command
{
    protected $signature = 'booking:expire-stale';

    protected $description = 'Tandai booking pending yang sudah terlewat jadwalnya sebagai expired dan kembalikan kupon';

    public function handle(): int
    {
        $bookings = Booking::with('payment')
            ->whereIn('status', ['pending', 'pending_verification'])
            ->whereDate('tanggal', '<', today()->toDateString())
            ->get();

        $expired = 0;
        foreach ($bookings as $booking) {
            // Booking yang sudah dibayar tidak boleh di-expire otomatis — biar admin yang putuskan
            if ($booking->payment && $booking->payment->status === 'paid') {
                continue;
            }
            DB::transaction(function () use ($booking) {
                $booking->restoreCouponUsage();
                $booking->update(['status' => 'expired']);
                $booking->payment()->update(['status' => 'failed']);
            });
            $expired++;
        }

        $this->info("{$expired} booking ditandai expired.");
        return self::SUCCESS;
    }
}
