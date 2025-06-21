<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CancelExpiredReservations extends Command
{
    protected $signature = 'reservations:cancel-expired';
    protected $description = 'Cancel reservations that have exceeded the payment deadline';

    public function handle()
    {
        $expiredReservations = Reservation::where('status', 'confirmed')
            ->where('created_at', '<=', Carbon::now()->subHour())
            ->get();

        $cancelledCount = 0;

        foreach ($expiredReservations as $reservation) {
            $reservation->update(['status' => 'cancelled']);
            $cancelledCount++;
        }

        $this->info("Cancelled {$cancelledCount} expired reservations.");

        return 0;
    }
}
