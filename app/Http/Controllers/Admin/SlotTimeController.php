<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlotTime;
use Illuminate\Http\Request;

class SlotTimeController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $slots = SlotTime::where('date', $selectedDate)->orderBy('start_time')->get();

        return view('pages.admin.slots.index', compact('slots', 'selectedDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'date' => 'required|date',
        ]);

        // Check for overlapping slots
        $overlappingSlot = SlotTime::where('date', $request->date)
            ->where(function ($query) use ($request) {
                // Case 1: New slot starts during an existing slot
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<=', $request->start_time)
                        ->where('end_time', '>', $request->start_time);
                })
                    // Case 2: New slot ends during an existing slot
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<', $request->end_time)
                            ->where('end_time', '>=', $request->end_time);
                    })
                    // Case 3: New slot completely contains an existing slot
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '>=', $request->start_time)
                            ->where('end_time', '<=', $request->end_time);
                    })
                    // Case 4: Existing slot completely contains the new slot
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                    });
            })
            ->first();

        if ($overlappingSlot) {
            $existingTime = \Carbon\Carbon::parse($overlappingSlot->start_time)->format('H:i') . ' - ' .
                \Carbon\Carbon::parse($overlappingSlot->end_time)->format('H:i');
            $newTime = $request->start_time . ' - ' . $request->end_time;

            return back()->withErrors([
                'error' => "Slot waktu {$newTime} bertabrakan dengan slot yang sudah ada ({$existingTime}). Silakan pilih waktu yang tidak bertabrakan."
            ]);
        }

        SlotTime::create($request->all());
        return back()->with('success', 'Slot waktu berhasil ditambahkan.');
    }

    public function destroy(Request $request, $id)
    {
        if ($id === 'all') {
            // Delete all slots for specific date
            $request->validate([
                'date' => 'required|date',
            ]);

            // Check if any slot has active reservations
            $slotsWithReservations = SlotTime::where('date', $request->date)
                ->whereHas('reservations', function ($query) {
                    $query->whereIn('status', ['confirmed', 'paid'])
                        ->where('reservation_date', '>=', now()->format('Y-m-d'));
                })
                ->exists();

            if ($slotsWithReservations) {
                return back()->withErrors(['error' => 'Tidak dapat menghapus slot waktu yang memiliki reservasi aktif.']);
            }

            $deletedCount = SlotTime::where('date', $request->date)->delete();

            if ($deletedCount > 0) {
                return back()->with('success', "Berhasil menghapus {$deletedCount} slot waktu.");
            } else {
                return back()->withErrors(['error' => 'Tidak ada slot waktu untuk dihapus pada tanggal tersebut.']);
            }
        } else {
            // Delete specific slot
            $request->validate([
                'slot_id' => 'required|exists:slot_times,id',
            ]);

            $slot = SlotTime::findOrFail($request->slot_id);

            // Check if slot has active reservations
            $hasActiveReservations = $slot->reservations()
                ->whereIn('status', ['confirmed', 'paid'])
                ->where('reservation_date', '>=', now()->format('Y-m-d'))
                ->exists();

            if ($hasActiveReservations) {
                return back()->withErrors(['error' => 'Tidak dapat menghapus slot waktu yang memiliki reservasi aktif.']);
            }

            $slot->delete();
            return back()->with('success', 'Slot waktu berhasil dihapus.');
        }
    }
}
