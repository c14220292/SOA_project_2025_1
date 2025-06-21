<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $fillable = ['number', 'seat_count', 'is_available'];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_tables');
    }

    public function isAvailableForDate($date, $slotTimeIds = [])
    {
        if (!$this->is_available) {
            return false;
        }

        // Check if table is already reserved for the given date and slot times
        $conflictingReservations = $this->reservations()
            ->where('reservation_date', $date)
            ->whereIn('status', ['confirmed', 'paid'])
            ->whereHas('slotTimes', function ($query) use ($slotTimeIds) {
                if (!empty($slotTimeIds)) {
                    $query->whereIn('slot_time_id', $slotTimeIds);
                }
            })
            ->exists();

        return !$conflictingReservations;
    }

    /**
     * Get occupied slot time IDs for a specific date
     */
    public function getOccupiedSlotIds($date)
    {
        return $this->reservations()
            ->where('reservation_date', $date)
            ->whereIn('status', ['confirmed', 'paid'])
            ->with('slotTimes')
            ->get()
            ->flatMap(function ($reservation) {
                return $reservation->slotTimes->pluck('id');
            })
            ->unique()
            ->toArray();
    }
}
