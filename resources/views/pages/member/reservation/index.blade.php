@extends('layouts.app')

@section('title', 'Riwayat Reservasi')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Real-time Status Indicator -->
        <div class="mb-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div id="status-indicator" class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm text-gray-600">Data diperbarui secara real-time</span>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-xs text-gray-500">
                    Terakhir diperbarui: <span id="last-update">{{ now()->format('H:i:s') }}</span>
                </div>
                <button onclick="window.manualRefresh()" class="text-blue-600 hover:text-blue-800 text-sm">
                    🔄 Refresh Manual
                </button>
            </div>
        </div>

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-red-800 to-red-600 text-white p-6">
                <h1 class="text-2xl font-bold text-center flex items-center justify-center">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                        </path>
                    </svg>
                    RIWAYAT RESERVASI
                </h1>
                <p class="text-center text-red-200 mt-2">Kelola dan pantau semua reservasi Anda</p>
            </div>

            <div class="p-6">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="text-gray-600">
                            <span class="font-semibold text-lg">{{ $reservations->count() }}</span>
                            <span class="text-sm">Total Reservasi</span>
                        </div>
                        <div class="text-gray-400">|</div>
                        <div class="text-green-600">
                            <span class="font-semibold text-lg">{{ $reservations->where('status', 'paid')->count() }}</span>
                            <span class="text-sm">Dikonfirmasi</span>
                        </div>
                        <div class="text-gray-400">|</div>
                        <div class="text-yellow-600">
                            <span
                                class="font-semibold text-lg">{{ $reservations->whereIn('status', ['pending', 'confirmed'])->count() }}</span>
                            <span class="text-sm">Dalam Proses</span>
                        </div>
                    </div>
                    <a href="{{ route('member.reservations.create') }}"
                        class="bg-gradient-to-r from-red-800 to-red-600 text-white px-6 py-3 rounded-lg hover:from-red-700 hover:to-red-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Buat Reservasi Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Reservations List -->
        <div class="reservations-content">
            @if ($reservations->isEmpty())
                <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                    <div class="text-gray-400 mb-6">
                        <svg class="w-24 h-24 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-700 mb-3">Belum Ada Reservasi</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">Anda belum memiliki riwayat reservasi. Mulai buat
                        reservasi pertama Anda untuk menikmati pengalaman dining yang tak terlupakan!</p>
                    <a href="{{ route('member.reservations.create') }}"
                        class="bg-gradient-to-r from-red-800 to-red-600 text-white px-8 py-4 rounded-lg hover:from-red-700 hover:to-red-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1 inline-flex items-center text-lg">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Buat Reservasi Pertama
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($reservations as $reservation)
                        <div
                            class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <!-- Enhanced Status Badge -->
                            <div
                                class="relative {{ $reservation->status === 'pending'
                                    ? 'bg-gradient-to-r from-yellow-100 to-yellow-200'
                                    : ($reservation->status === 'confirmed'
                                        ? 'bg-gradient-to-r from-blue-100 to-blue-200'
                                        : ($reservation->status === 'paid'
                                            ? 'bg-gradient-to-r from-green-100 to-green-200'
                                            : ($reservation->status === 'rejected'
                                                ? 'bg-gradient-to-r from-red-100 to-red-200'
                                                : 'bg-gradient-to-r from-gray-100 to-gray-200'))) }} p-4">

                                <!-- Status Icon and Text -->
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        @switch($reservation->status)
                                            @case('pending')
                                                <div
                                                    class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-white animate-spin" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="text-sm font-bold text-yellow-800">SEDANG DIPROSES</span>
                                                    <p class="text-xs text-yellow-700">Sistem sedang memproses otomatis</p>
                                                </div>
                                            @break

                                            @case('confirmed')
                                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="text-sm font-bold text-blue-800">MENUNGGU PEMBAYARAN</span>
                                                    @php
                                                        $paymentDeadline = $reservation->created_at->addHour();
                                                        $timeRemaining = now()->diffInMinutes($paymentDeadline, false);
                                                    @endphp
                                                    @if ($timeRemaining > 0)
                                                        <p class="text-xs text-blue-700">Sisa {{ $timeRemaining }} menit</p>
                                                    @else
                                                        <p class="text-xs text-red-700">Batas waktu habis</p>
                                                    @endif
                                                </div>
                                            @break

                                            @case('paid')
                                                <div
                                                    class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="text-sm font-bold text-green-800">DIKONFIRMASI ✅</span>
                                                    <p class="text-xs text-green-700">Siap untuk dining</p>
                                                </div>
                                            @break

                                            @case('rejected')
                                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="text-sm font-bold text-red-800">DITOLAK OTOMATIS</span>
                                                    <p class="text-xs text-red-700">Meja tidak tersedia</p>
                                                </div>
                                            @break

                                            @case('cancelled')
                                                <div
                                                    class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center mr-3">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="text-sm font-bold text-gray-800">DIBATALKAN</span>
                                                    <p class="text-xs text-gray-700">Melewati batas waktu</p>
                                                </div>
                                            @break
                                        @endswitch
                                    </div>
                                    <span class="text-xs text-gray-500 bg-white px-2 py-1 rounded">
                                        {{ $reservation->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                <!-- Progress Bar for pending/confirmed -->
                                @if ($reservation->status === 'pending')
                                    <div class="mt-2 w-full bg-yellow-200 rounded-full h-1">
                                        <div class="bg-yellow-500 h-1 rounded-full animate-pulse" style="width: 60%">
                                        </div>
                                    </div>
                                @elseif ($reservation->status === 'confirmed')
                                    @php
                                        $paymentDeadline = $reservation->created_at->addHour();
                                        $timeRemaining = now()->diffInMinutes($paymentDeadline, false);
                                        $progressPercentage = $timeRemaining > 0 ? ($timeRemaining / 60) * 100 : 0;
                                    @endphp
                                    <div class="mt-2 w-full bg-blue-200 rounded-full h-1">
                                        <div class="bg-blue-500 h-1 rounded-full transition-all duration-1000"
                                            style="width: {{ $progressPercentage }}%"></div>
                                    </div>
                                @endif
                            </div>

                            <!-- Enhanced Reservation Details -->
                            <div class="p-6">
                                <!-- Main Info -->
                                <div class="mb-4">
                                    <h4 class="text-lg font-bold text-gray-800 mb-2 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        {{ $reservation->reservation_date->translatedFormat('l, d M Y') }}
                                    </h4>

                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="flex items-center mb-1">
                                                <svg class="w-4 h-4 mr-1 text-blue-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                    </path>
                                                </svg>
                                                <span class="text-gray-600">Tamu</span>
                                            </div>
                                            <span class="font-bold text-lg">{{ $reservation->guest_count }}</span>
                                            <span class="text-gray-500 text-xs">orang</span>
                                        </div>

                                        <div class="bg-gray-50 rounded-lg p-3">
                                            <div class="flex items-center mb-1">
                                                <svg class="w-4 h-4 mr-1 text-green-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                    </path>
                                                </svg>
                                                <span class="text-gray-600">Meja</span>
                                            </div>
                                            <span class="font-bold text-lg">{{ $reservation->table_count }}</span>
                                            <span class="text-gray-500 text-xs">meja</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Time Slots -->
                                @if ($reservation->slotTimes->isNotEmpty())
                                    <div class="mb-4">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-4 h-4 mr-1 text-purple-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-600">Slot Waktu:</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($reservation->slotTimes as $slot)
                                                <span
                                                    class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-medium">
                                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Table Numbers -->
                                @if ($reservation->tables->isNotEmpty())
                                    <div class="mb-4">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                </path>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-600">Nomor Meja:</span>
                                        </div>
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-2">
                                            <span class="font-bold text-green-700 text-lg">
                                                Meja {{ implode(', ', $reservation->table_numbers) }}
                                            </span>
                                            <p class="text-green-600 text-xs">Ditetapkan otomatis</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Cost Information -->
                                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-gray-600">Biaya DP:</span>
                                        <span class="font-bold text-yellow-700">Rp
                                            {{ number_format($reservation->dp_amount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Min. Makan:</span>
                                        <span class="font-bold text-yellow-700">Rp
                                            {{ number_format($reservation->minimal_charge, 0, ',', '.') }}</span>
                                    </div>
                                    @if ($reservation->payment_method)
                                        <div class="mt-2 pt-2 border-t border-yellow-200">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-green-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span class="text-sm text-green-600 font-medium">
                                                    Dibayar via {{ strtoupper($reservation->payment_method) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Button -->
                                <div class="text-center">
                                    <a href="{{ route('member.reservations.status', $reservation->id) }}"
                                        class="w-full bg-gradient-to-r from-red-800 to-red-600 text-white py-3 px-4 rounded-lg hover:from-red-700 hover:to-red-500 transition-all duration-200 inline-flex items-center justify-center font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                        @if ($reservation->status === 'pending')
                                            <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            PANTAU PROSES
                                        @elseif ($reservation->status === 'confirmed')
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                                </path>
                                            </svg>
                                            BAYAR SEKARANG
                                        @else
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            LIHAT DETAIL
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Quick Actions -->
                <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Aksi Cepat
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('member.reservations.create') }}"
                            class="bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition-colors group">
                            <div class="flex items-center">
                                <div
                                    class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-green-800">Reservasi Baru</div>
                                    <div class="text-sm text-green-600">Buat reservasi untuk tanggal lain</div>
                                </div>
                            </div>
                        </a>

                        @php
                            $activeReservations = $reservations->whereIn('status', ['pending', 'confirmed']);
                        @endphp
                        @if ($activeReservations->isNotEmpty())
                            <a href="{{ route('member.reservations.status', $activeReservations->first()->id) }}"
                                class="bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors group">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-blue-800">Cek Status Aktif</div>
                                        <div class="text-sm text-blue-600">Pantau reservasi yang sedang berjalan</div>
                                    </div>
                                </div>
                            </a>
                        @endif

                        <button onclick="window.manualRefresh()"
                            class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:bg-gray-100 transition-colors group">
                            <div class="flex items-center">
                                <div
                                    class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800">Refresh Data</div>
                                    <div class="text-sm text-gray-600">Perbarui data secara manual</div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            // Update last update time every 30 seconds
            setInterval(() => {
                document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID');
            }, 30000);

            // Auto-refresh reservations list every 15 seconds
            setInterval(async () => {
                try {
                    const response = await fetch(window.location.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        const html = await response.text();
                        const parser = new DOMParser();
                        const newDoc = parser.parseFromString(html, 'text/html');
                        const newContent = newDoc.querySelector('.reservations-content');

                        if (newContent) {
                            document.querySelector('.reservations-content').innerHTML = newContent.innerHTML;

                            // Show update indicator
                            const indicator = document.getElementById('status-indicator');
                            indicator.classList.add('animate-ping');
                            setTimeout(() => {
                                indicator.classList.remove('animate-ping');
                            }, 1000);
                        }
                    }
                } catch (error) {
                    console.error('Auto-refresh failed:', error);
                }
            }, 15000);
        </script>
    @endpush
@endsection
