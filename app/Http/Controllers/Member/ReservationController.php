<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\SlotTime;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Daftar riwayat reservasi member.
     */
    public function index()
    {
        // Auto-cancel expired reservations before showing the list
        $this->cancelExpiredReservations();

        $reservations = Reservation::where('user_id', 1)
            ->with(['slotTimes', 'tables'])
            ->latest()
            ->get();

        return view('pages.member.reservation.index', compact('reservations'));
    }

    /**
     * Halaman form membuat reservasi (step awal).
     */
    public function create()
    {
        // Batas hari: H-1 hingga H-7
        $minDate = now()->addDay()->format('Y-m-d');
        $maxDate = now()->addDays(7)->format('Y-m-d');

        // Generate available dates
        $dates = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = now()->addDays($i);
            $dates[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->translatedFormat('l'),
                'formatted' => $date->translatedFormat('d M Y')
            ];
        }

        return view('pages.member.reservation.create', compact('minDate', 'maxDate', 'dates'));
    }

    /**
     * Pilih waktu reservasi (step setelah pilih tanggal).
     */
    public function selectTime(Request $request)
    {
        $request->validate([
            'reservation_date' => 'required|date|after:today|before_or_equal:' . now()->addDays(7)->format('Y-m-d'),
            'guest_count' => 'required|integer|min:1',
        ]);

        $reservationDate = $request->reservation_date;
        $guestCount = $request->guest_count;

        // Store in session for back navigation
        session([
            'reservation_step1' => [
                'reservation_date' => $reservationDate,
                'guest_count' => $guestCount
            ]
        ]);

        // Calculate required tables (4 seats per table)
        $tableCount = ceil($guestCount / 4);

        // Get available slot times for the selected date
        $slotTimes = SlotTime::where('date', $reservationDate)
            ->orderBy('start_time')
            ->get();

        // Calculate available tables for each slot
        $availableSlots = [];
        foreach ($slotTimes as $slot) {
            $availableTables = $slot->getAvailableTablesCount($reservationDate);
            if ($availableTables >= $tableCount) {
                $availableSlots[] = [
                    'id' => $slot->id,
                    'time' => $slot->formatted_time,
                    'available_tables' => $availableTables
                ];
            }
        }

        return view('pages.member.reservation.select_time', [
            'reservationDate' => $reservationDate,
            'guestCount' => $guestCount,
            'tableCount' => $tableCount,
            'availableSlots' => $availableSlots,
        ]);
    }

    /**
     * Konfirmasi reservasi (preview sebelum simpan).
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'reservation_date' => 'required|date',
            'guest_count' => 'required|integer|min:1',
            'table_count' => 'required|integer|min:1',
            'slot_time_ids' => 'required|array|min:1',
            'slot_time_ids.*' => 'exists:slot_times,id',
        ]);

        // Store in session for back navigation
        session([
            'reservation_step2' => [
                'reservation_date' => $request->reservation_date,
                'guest_count' => $request->guest_count,
                'table_count' => $request->table_count,
                'slot_time_ids' => $request->slot_time_ids,
                'note' => $request->note
            ]
        ]);

        $slotTimes = SlotTime::whereIn('id', $request->slot_time_ids)->get();
        $tableCount = $request->table_count;
        $slotCount = count($request->slot_time_ids);

        $dpAmount = $tableCount * $slotCount * 15000;
        $minimalCharge = $tableCount * 50000;

        return view('pages.member.reservation.confirm', [
            'reservationDate' => $request->reservation_date,
            'guestCount' => $request->guest_count,
            'tableCount' => $tableCount,
            'slotTimes' => $slotTimes,
            'slotTimeIds' => $request->slot_time_ids,
            'dpAmount' => $dpAmount,
            'minimalCharge' => $minimalCharge,
        ]);
    }

    /**
     * Simpan reservasi ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reservation_date' => 'required|date',
            'guest_count' => 'required|integer|min:1',
            'table_count' => 'required|integer|min:1',
            'slot_time_ids' => 'required|array|min:1',
            'slot_time_ids.*' => 'exists:slot_times,id',
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $slotCount = count($request->slot_time_ids);
            $dpAmount = $request->table_count * $slotCount * 15000;
            $minimalCharge = $request->table_count * 50000;

            $reservation = Reservation::create([
                'user_id' => 1,
                'reservation_date' => $request->reservation_date,
                'guest_count' => $request->guest_count,
                'table_count' => $request->table_count,
                'dp_amount' => $dpAmount,
                'minimal_charge' => $minimalCharge,
                'status' => 'pending',
                'note' => $request->note,
            ]);

            // Attach slot times
            $reservation->slotTimes()->sync($request->slot_time_ids);

            // Clear session data after successful creation
            session()->forget(['reservation_step1', 'reservation_step2']);

            DB::commit();

            return redirect()->route('member.reservations.status', $reservation->id)
                ->with('success', 'Reservasi berhasil dibuat dan menunggu konfirmasi admin.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('member.reservations.index')->withErrors(['error' => 'Terjadi kesalahan saat membuat reservasi.']);
        }
    }

    /**
     * Cek status reservasi.
     */
    public function status(Reservation $reservation)
    {
        // Check if user owns this reservation
        if ($reservation->user_id !== 1) {
            abort(403);
        }

        // Auto-cancel if expired
        $this->checkAndCancelExpiredReservation($reservation);

        // Refresh reservation data
        $reservation->refresh();

        return view('pages.member.reservation.status', compact('reservation'));
    }

    /**
     * Setelah berhasil menyimpan reservasi.
     */
    public function confirmed(Reservation $reservation)
    {
        if ($reservation->user_id !== 1) {
            abort(403);
        }

        return view('pages.member.reservation.confirmed', compact('reservation'));
    }

    /**
     * Batalkan reservasi
     */
    public function cancel(Reservation $reservation)
    {
        if ($reservation->user_id !== 1) {
            abort(403);
        }

        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['error' => 'Reservasi tidak dapat dibatalkan.']);
        }

        $reservation->update(['status' => 'cancelled']);

        return redirect()->route('member.reservations.index')
            ->with('success', 'Reservasi berhasil dibatalkan.');
    }

    /**
     * Proses pembayaran
     */
    public function pay(Request $request, Reservation $reservation)
    {
        $request->validate([
            'payment_method' => 'required|in:bca,gopay,ovo,qris',
        ]);

        if ($reservation->user_id !== 1) {
            abort(403);
        }

        // Check if reservation is expired
        if ($this->checkAndCancelExpiredReservation($reservation)) {
            return back()->withErrors(['error' => 'Reservasi telah melewati batas waktu pembayaran dan dibatalkan otomatis.']);
        }

        if ($reservation->status !== 'confirmed') {
            return back()->withErrors(['error' => 'Reservasi belum dikonfirmasi admin.']);
        }

        // Simulate payment process
        $reservation->update([
            'status' => 'paid',
            'payment_method' => $request->payment_method,
            'payment_time' => now(),
        ]);

        return redirect()->route('member.reservations.confirmed', $reservation->id)
            ->with('success', 'Pembayaran berhasil! Reservasi Anda telah dikonfirmasi.');
    }

    /**
     * Back to select time with preserved data
     */
    public function backToSelectTime()
    {
        $step1Data = session('reservation_step1');

        if (!$step1Data) {
            return redirect()->route('member.reservations.create');
        }

        return redirect()->route('member.reservations.select-time')
            ->with('reservation_date', $step1Data['reservation_date'])
            ->with('guest_count', $step1Data['guest_count']);
    }

    /**
     * Back to confirm with preserved data
     */
    public function backToConfirm()
    {
        $step2Data = session('reservation_step2');

        if (!$step2Data) {
            return redirect()->route('member.reservations.create');
        }

        return redirect()->route('member.reservations.confirm')
            ->with($step2Data);
    }

    /**
     * Auto-cancel expired reservations
     */
    private function cancelExpiredReservations()
    {
        Reservation::where('status', 'confirmed')
            ->where('created_at', '<=', Carbon::now()->subHour())
            ->update(['status' => 'cancelled']);
    }

    /**
     * Check and cancel specific expired reservation
     */
    private function checkAndCancelExpiredReservation(Reservation $reservation)
    {
        if ($reservation->status === 'confirmed' && $reservation->created_at->addHour()->isPast()) {
            $reservation->update(['status' => 'cancelled']);
            return true;
        }
        return false;
    }
}
