@extends('layouts.app')

@section('title', 'Riwayat Reservasi')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="bg-red-800 text-white p-6">
                <h1 class="text-2xl font-bold text-center">RIWAYAT RESERVASI</h1>
                <p class="text-center text-red-200 mt-2">Kelola reservasi Anda</p>
            </div>

            <div class="p-6">
                <div class="flex justify-between items-center">
                    <p class="text-gray-600">Total Reservasi: {{ $reservations->count() }}</p>
                    <a href="{{ route('member.reservations.create') }}"
                        class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                        Buat Reservasi Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Reservations List -->
        @if ($reservations->isEmpty())
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Reservasi</h3>
                <p class="text-gray-500 mb-4">Anda belum memiliki riwayat reservasi</p>
                <a href="{{ route('member.reservations.create') }}"
                    class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    Buat Reservasi Pertama
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($reservations as $reservation)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <!-- Status Badge -->
                        <div
                            class="p-4 {{ $reservation->status === 'pending' ? 'bg-yellow-100' : ($reservation->status === 'confirmed' ? 'bg-blue-100' : ($reservation->status === 'paid' ? 'bg-green-100' : ($reservation->status === 'rejected' ? 'bg-red-100' : 'bg-gray-100'))) }}">
                            <div class="flex justify-between items-center">
                                <span
                                    class="text-sm font-medium {{ $reservation->status === 'pending' ? 'text-yellow-800' : ($reservation->status === 'confirmed' ? 'text-blue-800' : ($reservation->status === 'paid' ? 'text-green-800' : ($reservation->status === 'rejected' ? 'text-red-800' : 'text-gray-800'))) }}">
                                    @switch($reservation->status)
                                        @case('pending')
                                            Menunggu Konfirmasi
                                        @break

                                        @case('confirmed')
                                            Menunggu Pembayaran
                                        @break

                                        @case('paid')
                                            Reservasi Dikonfirmasi
                                        @break

                                        @case('rejected')
                                            Reservasi Ditolak
                                        @break

                                        @case('cancelled')
                                            Reservasi Dibatalkan
                                        @break

                                        @default
                                            Status Tidak Diketahui
                                    @endswitch
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $reservation->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>

                        <!-- Reservation Details -->
                        <div class="p-4">
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Tanggal:</span>
                                    <span
                                        class="text-sm font-medium">{{ $reservation->reservation_date->translatedFormat('d M Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Tamu:</span>
                                    <span class="text-sm font-medium">{{ $reservation->guest_count }} orang</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Meja:</span>
                                    <span class="text-sm font-medium">{{ $reservation->table_count }} meja</span>
                                </div>
                                @if ($reservation->slotTimes->isNotEmpty())
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Waktu:</span>
                                        <span class="text-sm font-medium">{{ $reservation->formatted_slot_times }}</span>
                                    </div>
                                @endif
                                @if ($reservation->tables->isNotEmpty())
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">No. Meja:</span>
                                        <span
                                            class="text-sm font-medium">{{ implode(', ', $reservation->table_numbers) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Biaya DP:</span>
                                    <span class="text-sm font-medium">Rp
                                        {{ number_format($reservation->dp_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="text-center">
                                <a href="{{ route('member.reservations.status', $reservation->id) }}"
                                    class="w-full bg-red-800 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors inline-block text-center">
                                    LIHAT DETAIL
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
