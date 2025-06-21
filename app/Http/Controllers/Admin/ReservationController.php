<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));

        $reservations = Reservation::with(['user', 'slotTimes', 'tables'])
            ->where('reservation_date', $selectedDate)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $tables = Table::with(['reservations' => function ($query) use ($selectedDate) {
            $query->where('reservation_date', $selectedDate)
                ->whereIn('status', ['confirmed', 'paid'])
                ->with('slotTimes');
        }])->orderBy('number')->get();

        return view('pages.admin.reservation.index', compact('reservations', 'tables', 'selectedDate'));
    }

    public function approve(Request $request, Reservation $reservation)
    {
        // Calculate required tables based on guest count
        $requiredTables = ceil($reservation->guest_count / 4);

        $request->validate([
            'table_ids' => 'required|array|min:' . $requiredTables,
            'table_ids.*' => 'exists:tables,id',
        ], [
            'table_ids.min' => "Minimal harus memilih {$requiredTables} meja untuk {$reservation->guest_count} orang.",
        ]);

        // Validate that selected tables can accommodate all guests
        $selectedTables = Table::whereIn('id', $request->table_ids)->get();
        $totalSeats = $selectedTables->sum('seat_count');

        if ($totalSeats < $reservation->guest_count) {
            return back()->withErrors(['error' => "Meja yang dipilih hanya dapat menampung {$totalSeats} orang, sedangkan reservasi untuk {$reservation->guest_count} orang."]);
        }

        // Check if selected tables are available for all slot times
        $slotTimeIds = $reservation->slotTimes->pluck('id')->toArray();

        foreach ($selectedTables as $table) {
            if (!$table->isAvailableForDate($reservation->reservation_date, $slotTimeIds)) {
                return back()->withErrors(['error' => "Meja {$table->number} tidak tersedia untuk slot waktu yang dipilih."]);
            }
        }

        DB::beginTransaction();
        try {
            // Update reservation status
            $reservation->update(['status' => 'confirmed']);

            // Assign tables to reservation
            $reservation->tables()->sync($request->table_ids);

            DB::commit();

            return back()->with('success', 'Reservasi berhasil dikonfirmasi dan meja telah ditetapkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengkonfirmasi reservasi.']);
        }
    }

    public function reject(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->withErrors(['error' => 'Reservasi sudah diproses sebelumnya.']);
        }

        $reservation->update(['status' => 'rejected']);

        return back()->with('success', 'Reservasi berhasil ditolak.');
    }

    public function showTableSelection(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->withErrors(['error' => 'Reservasi sudah diproses sebelumnya.']);
        }

        $slotTimeIds = $reservation->slotTimes->pluck('id')->toArray();

        $availableTables = Table::where('is_available', true)
            ->get()
            ->filter(function ($table) use ($reservation, $slotTimeIds) {
                return $table->isAvailableForDate($reservation->reservation_date, $slotTimeIds);
            });

        return view('pages.admin.reservation.table-selection', compact('reservation', 'availableTables'));
    }
}
