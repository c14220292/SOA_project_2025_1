<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotTime extends Model
{
    protected $fillable = ['start_time', 'end_time', 'date'];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_slot_times');
    }

    public function getFormattedTimeAttribute()
    {
        return \Carbon\Carbon::parse($this->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($this->end_time)->format('H:i');
    }

    public function getAvailableTablesCount($date)
    {
        $totalTables = \App\Models\Table::where('is_available', true)->count();

        $reservedTables = $this->reservations()
            ->where('reservation_date', $date)
            ->whereIn('status', ['confirmed', 'paid'])
            ->withCount('tables')
            ->sum('table_count') ?? 0;

        return max(0, $totalTables - $reservedTables);
    }
}
