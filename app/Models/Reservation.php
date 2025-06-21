<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reservation_date',
        'guest_count',
        'table_count',
        'dp_amount',
        'minimal_charge',
        'status',
        'payment_time',
        'payment_method',
        'note',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'payment_time' => 'datetime',
        'dp_amount' => 'decimal:2',
        'minimal_charge' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slotTimes()
    {
        return $this->belongsToMany(SlotTime::class, 'reservation_slot_times');
    }

    public function tables()
    {
        return $this->belongsToMany(Table::class, 'reservation_tables');
    }

    public function getFormattedSlotTimesAttribute()
    {
        return $this->slotTimes->map(function ($slot) {
            return $slot->start_time . ' - ' . $slot->end_time;
        })->implode(', ');
    }

    public function getTableNumbersAttribute()
    {
        return $this->tables->pluck('number')->toArray();
    }

    public function calculateDpAmount()
    {
        $slotCount = $this->slotTimes()->count();
        return $this->table_count * $slotCount * 15000;
    }

    public function calculateMinimalCharge()
    {
        return $this->table_count * 50000;
    }
}
