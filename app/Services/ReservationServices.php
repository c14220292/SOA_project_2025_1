<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\SlotTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                    return true;
                } else {
                    // No available tables, keep as pending for manual review
                    Log::info("Reservation {$reservation->id} kept as pending - no available tables");
                    DB::rollback();
                    return false;
                }
            } else {
                // Cannot auto-approve, keep as pending
                Log::info("Reservation {$reservation->id} kept as pending - cannot auto-approve");
                DB::rollback();
                return false;
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error processing reservation {$reservation->id}: " . $e->getMessage());
            return false;
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

    public function getReservationStats()
    {
    }
}
