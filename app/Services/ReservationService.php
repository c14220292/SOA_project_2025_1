<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\SlotTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservationService
{
    /**
     * Process reservation automatically
     */
    public function processReservationAutomatically(Reservation $reservation)
    {
        DB::beginTransaction();
        try {
            // Check if reservation can be auto-approved
            if ($this->canAutoApprove($reservation)) {
                // Find and assign tables automatically
                $assignedTables = $this->findAndAssignTables($reservation);

                if (!empty($assignedTables)) {
                    // Update reservation status to confirmed
                    $reservation->update(['status' => 'confirmed']);

                    // Assign tables to reservation
                    $reservation->tables()->sync($assignedTables);

                    Log::info("Reservation {$reservation->id} auto-approved and tables assigned", [
                        'reservation_id' => $reservation->id,
                        'assigned_tables' => $assignedTables
                    ]);

                    DB::commit();
                    return [
                        'success' => true,
                        'message' => 'Reservasi berhasil dikonfirmasi otomatis dan meja telah ditetapkan.'
                    ];
                } else {
                    // No available tables, keep as pending for manual review
                    Log::info("Reservation {$reservation->id} kept as pending - no available tables");
                    DB::rollback();
                    return [
                        'success' => false,
                        'message' => 'Tidak ada meja yang tersedia. Reservasi akan ditinjau secara manual oleh admin.'
                    ];
                }
            } else {
                // Cannot auto-approve, reject automatically
                $reservation->update(['status' => 'rejected']);
                Log::info("Reservation {$reservation->id} auto-rejected - cannot auto-approve");
                DB::commit();
                return [
                    'success' => false,
                    'message' => 'Reservasi ditolak otomatis karena tidak ada meja yang tersedia untuk slot waktu yang dipilih.'
                ];
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error processing reservation {$reservation->id}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses reservasi. Silakan coba lagi.'
            ];
        }
    }

    /**
     * Check if reservation can be auto-approved
     */
    private function canAutoApprove(Reservation $reservation): bool
    {
        // Calculate required tables
        $requiredTables = ceil($reservation->guest_count / 4);

        // Get slot time IDs
        $slotTimeIds = $reservation->slotTimes->pluck('id')->toArray();

        // Check if enough tables are available
        $availableTables = $this->getAvailableTablesForSlots(
            $reservation->reservation_date,
            $slotTimeIds
        );

        return count($availableTables) >= $requiredTables;
    }

    /**
     * Find and assign tables automatically
     */
    private function findAndAssignTables(Reservation $reservation): array
    {
        $requiredTables = ceil($reservation->guest_count / 4);
        $slotTimeIds = $reservation->slotTimes->pluck('id')->toArray();

        // Get available tables
        $availableTables = $this->getAvailableTablesForSlots(
            $reservation->reservation_date,
            $slotTimeIds
        );

        if (count($availableTables) < $requiredTables) {
            return [];
        }

        // Sort tables by seat count (prefer tables that can accommodate more guests)
        $availableTables = $availableTables->sortByDesc('seat_count');

        $assignedTables = [];
        $totalSeats = 0;

        foreach ($availableTables as $table) {
            if (count($assignedTables) < $requiredTables) {
                $assignedTables[] = $table->id;
                $totalSeats += $table->seat_count;

                // If we have enough seats, we can stop
                if ($totalSeats >= $reservation->guest_count) {
                    break;
                }
            }
        }

        // Ensure we have enough tables and seats
        if (count($assignedTables) >= $requiredTables && $totalSeats >= $reservation->guest_count) {
            return $assignedTables;
        }

        return [];
    }

    /**
     * Get available tables for specific slots
     */
    private function getAvailableTablesForSlots($date, $slotTimeIds)
    {
        return Table::where('is_available', true)
            ->whereDoesntHave('reservations', function ($query) use ($date, $slotTimeIds) {
                $query->where('reservation_date', $date)
                    ->whereIn('status', ['confirmed', 'paid'])
                    ->whereHas('slotTimes', function ($slotQuery) use ($slotTimeIds) {
                        $slotQuery->whereIn('slot_time_id', $slotTimeIds);
                    });
            })
            ->get();
    }

    /**
     * Check table availability for a specific date and slots
     */
    public function checkTableAvailability($date, $slotTimeIds, $requiredTables): array
    {
        $availableTables = $this->getAvailableTablesForSlots($date, $slotTimeIds);

        return [
            'available_count' => $availableTables->count(),
            'required_count' => $requiredTables,
            'can_accommodate' => $availableTables->count() >= $requiredTables,
            'available_tables' => $availableTables
        ];
    }

    /**
     * Get reservation statistics for a specific date
     */
    public function getReservationStats($date = null): array
    {
        if (!$date) {
            $date = now()->format('Y-m-d');
        }

        $query = Reservation::where('reservation_date', $date)->get();

        return [
            'total' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'confirmed' => $query->where('status', 'confirmed')->count(),
            'paid' => $query->where('status', 'paid')->count(),
            'rejected' => $query->where('status', 'rejected')->count(),
            'cancelled' => $query->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Cancel expired reservations
     */
    public function cancelExpiredReservations(): int
    {
        $expiredReservations = Reservation::where('status', 'confirmed')
            ->where('created_at', '<=', Carbon::now()->subHour())
            ->get();

        $cancelledCount = 0;

        foreach ($expiredReservations as $reservation) {
            $reservation->update(['status' => 'cancelled']);
            $cancelledCount++;

            Log::info("Reservation {$reservation->id} cancelled due to payment timeout");
        }

        return $cancelledCount;
    }

    /**
     * Check if a specific reservation is expired
     */
    public function isReservationExpired(Reservation $reservation): bool
    {
        if ($reservation->status !== 'confirmed') {
            return false;
        }

        return $reservation->created_at->addHour()->isPast();
    }

    /**
     * Get available slot times for a specific date
     */
    public function getAvailableSlotTimes($date, $requiredTables): array
    {
        $slotTimes = SlotTime::where('date', $date)
            ->orderBy('start_time')
            ->get();

        $availableSlots = [];

        foreach ($slotTimes as $slot) {
            $availability = $this->checkTableAvailability(
                $date,
                [$slot->id],
                $requiredTables
            );

            if ($availability['can_accommodate']) {
                $availableSlots[] = [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'formatted_time' => $slot->formatted_time,
                    'available_tables' => $availability['available_count']
                ];
            }
        }

        return $availableSlots;
    }

    /**
     * Validate reservation data
     */
    public function validateReservationData(array $data): array
    {
        $errors = [];

        // Check if date is valid (H-1 to H-7)
        $reservationDate = Carbon::parse($data['reservation_date']);
        $minDate = now()->addDay();
        $maxDate = now()->addDays(7);

        if ($reservationDate->lt($minDate) || $reservationDate->gt($maxDate)) {
            $errors[] = 'Tanggal reservasi harus antara H-1 sampai H-7 dari hari ini.';
        }

        // Check if guest count is valid
        if ($data['guest_count'] < 1 || $data['guest_count'] > 80) { // Max 20 tables * 4 seats
            $errors[] = 'Jumlah tamu harus antara 1-80 orang.';
        }

        // Check if slot times exist and are valid
        if (empty($data['slot_time_ids'])) {
            $errors[] = 'Minimal pilih satu slot waktu.';
        } else {
            $validSlots = SlotTime::whereIn('id', $data['slot_time_ids'])
                ->where('date', $data['reservation_date'])
                ->count();

            if ($validSlots !== count($data['slot_time_ids'])) {
                $errors[] = 'Slot waktu yang dipilih tidak valid.';
            }
        }

        return $errors;
    }

    /**
     * Calculate reservation costs
     */
    public function calculateReservationCosts($tableCount, $slotCount): array
    {
        $dpAmount = $tableCount * $slotCount * 15000;
        $minimalCharge = $tableCount * 50000;

        return [
            'dp_amount' => $dpAmount,
            'minimal_charge' => $minimalCharge,
            'total_estimated' => $dpAmount + $minimalCharge
        ];
    }

    /**
     * Get table occupancy for a specific date
     */
    public function getTableOccupancy($date): array
    {
        $tables = Table::with(['reservations' => function ($query) use ($date) {
            $query->where('reservation_date', $date)
                ->whereIn('status', ['confirmed', 'paid'])
                ->with('slotTimes');
        }])->orderBy('number')->get();

        $slotTimes = SlotTime::where('date', $date)->orderBy('start_time')->get();

        $occupancy = [];

        foreach ($tables as $table) {
            $occupiedSlots = [];

            foreach ($table->reservations as $reservation) {
                foreach ($reservation->slotTimes as $slot) {
                    $occupiedSlots[] = $slot->id;
                }
            }

            $occupancy[$table->id] = [
                'table' => $table,
                'occupied_slot_ids' => array_unique($occupiedSlots),
                'availability_percentage' => $slotTimes->count() > 0
                    ? (($slotTimes->count() - count(array_unique($occupiedSlots))) / $slotTimes->count()) * 100
                    : 100
            ];
        }

        return $occupancy;
    }

    /**
     * Get reservation summary for admin dashboard
     */
    public function getReservationSummary($startDate = null, $endDate = null): array
    {
        if (!$startDate) {
            $startDate = now()->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = now()->addDays(7)->format('Y-m-d');
        }

        $reservations = Reservation::whereBetween('reservation_date', [$startDate, $endDate])
            ->with(['user', 'slotTimes', 'tables'])
            ->get();

        $summary = [
            'total_reservations' => $reservations->count(),
            'total_revenue' => $reservations->where('status', 'paid')->sum('dp_amount'),
            'pending_revenue' => $reservations->where('status', 'confirmed')->sum('dp_amount'),
            'status_breakdown' => $reservations->groupBy('status')->map->count(),
            'daily_breakdown' => $reservations->groupBy(function ($reservation) {
                return $reservation->reservation_date->format('Y-m-d');
            })->map->count(),
            'popular_slots' => $reservations->flatMap(function ($reservation) {
                return $reservation->slotTimes;
            })->groupBy('id')->map->count()->sortDesc()->take(5)
        ];

        return $summary;
    }
}
