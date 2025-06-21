<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Table;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index(Request $request)
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));

        // Get all reservations for the selected date (not just pending)
        $reservations = Reservation::with(['user', 'slotTimes', 'tables'])
            ->where('reservation_date', $selectedDate)
            ->latest()
            ->get();

        // Group reservations by status
        $groupedReservations = $reservations->groupBy('status');

        $tables = Table::with(['reservations' => function ($query) use ($selectedDate) {
            $query->where('reservation_date', $selectedDate)
                ->whereIn('status', ['confirmed', 'paid'])
                ->with('slotTimes');
        }])->orderBy('number')->get();

        // Get reservation statistics
        $stats = $this->reservationService->getReservationStats($selectedDate);

        return view('pages.admin.reservation.index', compact(
            'reservations',
            'groupedReservations',
            'tables',
            'selectedDate',
            'stats'
        ));
    }

    /**
     * Manual approval (for edge cases)
     */
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

            return back()->with('success', 'Reservasi berhasil dikonfirmasi secara manual dan meja telah ditetapkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengkonfirmasi reservasi.']);
        }
    }

    /**
     * Manual rejection
     */
    public function reject(Reservation $reservation)
    {
        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['error' => 'Reservasi sudah diproses sebelumnya.']);
        }

        $reservation->update(['status' => 'rejected']);

        return back()->with('success', 'Reservasi berhasil ditolak.');
    }

    /**
     * Show table selection for manual assignment
     */
    public function showTableSelection(Reservation $reservation)
    {
        if (!in_array($reservation->status, ['pending', 'rejected'])) {
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

    /**
     * Reprocess reservation (for rejected ones)
     */
    public function reprocess(Reservation $reservation)
    {
        if ($reservation->status !== 'rejected') {
            return back()->withErrors(['error' => 'Hanya reservasi yang ditolak yang dapat diproses ulang.']);
        }

        // Reset status to pending and try auto-processing again
        $reservation->update(['status' => 'pending']);

        $result = $this->reservationService->processReservationAutomatically($reservation);

        if ($result['success']) {
            return back()->with('success', 'Reservasi berhasil diproses ulang dan dikonfirmasi otomatis.');
        } else {
            return back()->with('info', 'Reservasi tetap tidak dapat dikonfirmasi otomatis. ' . $result['message']);
        }
    }
}
