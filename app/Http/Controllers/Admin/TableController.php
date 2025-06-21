<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::orderBy('number')->get();
        return view('pages.admin.tables.index', compact('tables'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|integer|unique:tables,number',
            'seat_count' => 'required|integer|min:1',
        ]);

        Table::create([
            'number' => $request->number,
            'seat_count' => $request->seat_count,
            'is_available' => true,
        ]);

        return back()->with('success', 'Meja berhasil ditambahkan.');
    }

    public function update(Request $request, Table $table)
    {
        $request->validate([
            'seat_count' => 'required|integer|min:1',
            'is_available' => 'required|boolean',
        ]);

        $table->update([
            'seat_count' => $request->seat_count,
            'is_available' => $request->is_available,
        ]);

        return back()->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(Table $table)
    {
        // Check if table has active reservations
        $hasActiveReservations = $table->reservations()
            ->whereIn('status', ['confirmed', 'paid'])
            ->where('reservation_date', '>=', now()->format('Y-m-d'))
            ->exists();

        if ($hasActiveReservations) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus meja yang memiliki reservasi aktif.']);
        }

        $table->delete();
        return back()->with('success', 'Meja berhasil dihapus.');
    }
}
