@extends('layouts.app')

@section('title', 'Status Reservasi')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Real-time Status Indicator -->
        <div class="mb-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div id="status-indicator" class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm text-gray-600">Status diperbarui secara real-time</span>
            </div>
            <div class="text-xs text-gray-500">
                Terakhir diperbarui: <span id="last-update">{{ now()->format('H:i:s') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Enhanced Header with Status -->
            <div
                class="p-6 {{ $reservation->status === 'pending'
                    ? 'bg-gradient-to-r from-yellow-100 to-yellow-200 border-b-4 border-yellow-500'
                    : ($reservation->status === 'confirmed'
                        ? 'bg-gradient-to-r from-blue-100 to-blue-200 border-b-4 border-blue-500'
                        : ($reservation->status === 'paid'
                            ? 'bg-gradient-to-r from-green-100 to-green-200 border-b-4 border-green-500'
                            : ($reservation->status === 'rejected'
                                ? 'bg-gradient-to-r from-red-100 to-red-200 border-b-4 border-red-500'
                                : 'bg-gradient-to-r from-gray-100 to-gray-200 border-b-4 border-gray-500'))) }}">

                <div class="text-center">
                    <!-- Status Icon and Title -->
                    <div class="mb-4">
                        @switch($reservation->status)
                            @case('pending')
                                <div class="w-16 h-16 mx-auto bg-yellow-500 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-white animate-spin" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                </div>
                                <h1 class="text-2xl font-bold text-yellow-800">SEDANG DIPROSES OTOMATIS</h1>
                                <div class="mt-3 p-3 bg-yellow-50 rounded-lg">
                                    <p class="text-yellow-700 font-medium">🤖 Sistem sedang memproses reservasi Anda secara otomatis
                                    </p>
                                    <p class="text-yellow-600 text-sm mt-1">Proses ini biasanya memakan waktu 30-60 detik</p>
                                    <div class="mt-2 w-full bg-yellow-200 rounded-full h-2">
                                        <div class="bg-yellow-500 h-2 rounded-full animate-pulse" style="width: 70%"></div>
                                    </div>
                                </div>
                            @break

                            @case('confirmed')
                                <div class="w-16 h-16 mx-auto bg-blue-500 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h1 class="text-2xl font-bold text-blue-800">RESERVASI DIKONFIRMASI</h1>
                                @php
                                    $paymentDeadline = $reservation->created_at->addHour();
                                    $timeRemaining = now()->diffInMinutes($paymentDeadline, false);
                                @endphp

                                @if ($timeRemaining > 0)
                                    <div class="mt-3 p-4 bg-blue-50 rounded-lg">
                                        <p class="text-blue-700 font-medium">✅ Meja telah ditetapkan secara otomatis!</p>
                                        <div class="mt-2 p-3 bg-white rounded border-l-4 border-blue-500">
                                            <p class="text-blue-800 font-semibold text-lg">
                                                ⏰ Batas pembayaran: {{ $paymentDeadline->translatedFormat('H:i, d M Y') }}
                                            </p>
                                            <div class="flex items-center mt-2">
                                                <span class="text-blue-600 font-medium">Sisa waktu: </span>
                                                <span id="countdown-timer"
                                                    class="ml-2 text-blue-800 font-bold text-lg">{{ $timeRemaining }} menit</span>
                                            </div>
                                            <div class="mt-2 w-full bg-blue-200 rounded-full h-3">
                                                <div id="countdown-bar"
                                                    class="bg-blue-500 h-3 rounded-full transition-all duration-1000"
                                                    style="width: {{ ($timeRemaining / 60) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-3 p-4 bg-red-50 rounded-lg border border-red-200">
                                        <p class="text-red-700 font-medium">⏰ Batas waktu pembayaran telah habis</p>
                                        <p class="text-red-600 text-sm">Reservasi akan dibatalkan otomatis dalam beberapa saat</p>
                                    </div>
                                @endif
                            @break

                            @case('paid')
                                <div class="w-16 h-16 mx-auto bg-green-500 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                        </path>
                                    </svg>
                                </div>
                                <h1 class="text-2xl font-bold text-green-800">RESERVASI DIKONFIRMASI ✅</h1>
                                <div class="mt-3 p-4 bg-green-50 rounded-lg">
                                    <p class="text-green-700 font-medium">🎉 Terima kasih! Reservasi Anda telah dikonfirmasi dan
                                        dibayar</p>
                                    <p class="text-green-600 text-sm mt-1">Silakan datang sesuai jadwal yang telah ditentukan</p>
                                </div>
                            @break

                            @case('rejected')
                                <div class="w-16 h-16 mx-auto bg-red-500 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <h1 class="text-2xl font-bold text-red-800">RESERVASI DITOLAK OTOMATIS ❌</h1>
                                <div class="mt-3 p-4 bg-red-50 rounded-lg">
                                    <p class="text-red-700 font-medium">😔 Maaf, tidak ada meja yang tersedia untuk slot waktu yang
                                        dipilih</p>
                                    <p class="text-red-600 text-sm mt-1">Silakan coba dengan tanggal atau waktu yang berbeda</p>
                                </div>
                            @break

                            @case('cancelled')
                                <div class="w-16 h-16 mx-auto bg-gray-500 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728">
                                        </path>
                                    </svg>
                                </div>
                                <h1 class="text-2xl font-bold text-gray-800">RESERVASI DIBATALKAN ❌</h1>
                                <div class="mt-3 p-4 bg-gray-50 rounded-lg">
                                    <p class="text-gray-700 font-medium">Reservasi telah dibatalkan karena melewati batas waktu
                                        pembayaran</p>
                                </div>
                            @break

                        @endswitch
                    </div>
                </div>
            </div>

            <!-- Enhanced Reservation Details -->
            <div class="p-6">
                <!-- Progress Steps -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div
                            class="flex flex-col items-center {{ in_array($reservation->status, ['pending', 'confirmed', 'paid']) ? 'text-green-600' : 'text-gray-400' }}">
                            <div
                                class="w-8 h-8 rounded-full {{ in_array($reservation->status, ['pending', 'confirmed', 'paid']) ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white font-bold text-sm">
                                1</div>
                            <span class="text-xs mt-1 font-medium">Reservasi Dibuat</span>
                        </div>
                        <div
                            class="flex-1 h-1 mx-2 {{ in_array($reservation->status, ['confirmed', 'paid']) ? 'bg-green-500' : 'bg-gray-300' }}">
                        </div>
                        <div
                            class="flex flex-col items-center {{ in_array($reservation->status, ['confirmed', 'paid']) ? 'text-green-600' : ($reservation->status === 'pending' ? 'text-yellow-600' : 'text-gray-400') }}">
                            <div
                                class="w-8 h-8 rounded-full {{ in_array($reservation->status, ['confirmed', 'paid']) ? 'bg-green-500' : ($reservation->status === 'pending' ? 'bg-yellow-500' : 'bg-gray-300') }} flex items-center justify-center text-white font-bold text-sm">
                                2</div>
                            <span class="text-xs mt-1 font-medium">Dikonfirmasi</span>
                        </div>
                        <div
                            class="flex-1 h-1 mx-2 {{ $reservation->status === 'paid' ? 'bg-green-500' : 'bg-gray-300' }}">
                        </div>
                        <div
                            class="flex flex-col items-center {{ $reservation->status === 'paid' ? 'text-green-600' : 'text-gray-400' }}">
                            <div
                                class="w-8 h-8 rounded-full {{ $reservation->status === 'paid' ? 'bg-green-500' : 'bg-gray-300' }} flex items-center justify-center text-white font-bold text-sm">
                                3</div>
                            <span class="text-xs mt-1 font-medium">Dibayar</span>
                        </div>
                    </div>
                </div>

                <!-- Detailed Information Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Customer Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Informasi Pelanggan
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nama:</span>
                                <span class="font-medium">{{ $reservation->user->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium text-sm">{{ $reservation->user->email }}</span>
                            </div>
                            @if ($reservation->user->phone)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Telepon:</span>
                                    <span
                                        class="font-medium">{{ substr($reservation->user->phone, 0, 4) }}-xxxx-xxxx</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            Detail Reservasi
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tanggal:</span>
                                <span
                                    class="font-medium">{{ $reservation->reservation_date->translatedFormat('l, d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Jumlah Tamu:</span>
                                <span class="font-medium">{{ $reservation->guest_count }} orang</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Jumlah Meja:</span>
                                <span class="font-medium">{{ $reservation->table_count }} meja</span>
                            </div>
                        </div>
                    </div>

                    <!-- Time Slots -->
                    @if ($reservation->slotTimes->isNotEmpty())
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Slot Waktu
                            </h3>
                            <div class="space-y-2">
                                @foreach ($reservation->slotTimes as $slot)
                                    <div
                                        class="bg-purple-100 text-purple-800 px-3 py-2 rounded-lg text-center font-medium">
                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Table Assignment -->
                    @if ($reservation->tables->isNotEmpty())
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <h3 class="font-semibold text-green-800 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                Meja yang Ditetapkan
                            </h3>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-700 mb-2">
                                    Meja {{ implode(', ', $reservation->table_numbers) }}
                                </div>
                                <p class="text-green-600 text-sm">Ditetapkan secara otomatis oleh sistem</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Cost Breakdown -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-yellow-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                            </path>
                        </svg>
                        Rincian Biaya
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-700">Biaya DP (Down Payment)</span>
                                <span class="font-bold text-lg text-yellow-700">Rp
                                    {{ number_format($reservation->dp_amount, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-gray-600">{{ $reservation->table_count }} meja ×
                                {{ $reservation->slotTimes->count() }} slot × Rp 15.000</p>
                            @if ($reservation->status === 'paid')
                                <div class="mt-2 text-green-600 text-sm font-medium">✅ Sudah dibayar</div>
                            @elseif ($reservation->status === 'confirmed')
                                <div class="mt-2 text-blue-600 text-sm font-medium">⏳ Menunggu pembayaran</div>
                            @endif
                        </div>

                        <div class="bg-white rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-700">Biaya Minimal Makan</span>
                                <span class="font-bold text-lg text-yellow-700">Rp
                                    {{ number_format($reservation->minimal_charge, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-gray-600">{{ $reservation->table_count }} meja × Rp 50.000</p>
                            <div class="mt-2 text-orange-600 text-sm font-medium">💳 Dibayar di restoran</div>
                        </div>
                    </div>
                </div>

                <!-- Auto Processing Info -->
                @if ($reservation->status === 'confirmed' && $reservation->tables->isNotEmpty())
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Proses Otomatis Berhasil!
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-3 bg-white rounded-lg">
                                <div class="text-green-600 text-2xl mb-1">✅</div>
                                <div class="text-sm font-medium text-green-800">Reservasi Dikonfirmasi</div>
                                <div class="text-xs text-green-600">Secara otomatis</div>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg">
                                <div class="text-green-600 text-2xl mb-1">🏠</div>
                                <div class="text-sm font-medium text-green-800">Meja Ditetapkan</div>
                                <div class="text-xs text-green-600">Meja {{ implode(', ', $reservation->table_numbers) }}
                                </div>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg">
                                <div class="text-blue-600 text-2xl mb-1">💳</div>
                                <div class="text-sm font-medium text-blue-800">Siap Dibayar</div>
                                <div class="text-xs text-blue-600">Pilih metode pembayaran</div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($reservation->status === 'rejected')
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-red-800 mb-3 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                            Mengapa Reservasi Ditolak?
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <span class="text-red-500 mr-2 mt-1">•</span>
                                <span class="text-red-700">Tidak ada meja yang tersedia untuk slot waktu yang
                                    dipilih</span>
                            </div>
                            <div class="flex items-start">
                                <span class="text-red-500 mr-2 mt-1">•</span>
                                <span class="text-red-700">Semua meja sudah dipesan untuk tanggal dan waktu tersebut</span>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <h4 class="font-medium text-blue-800 mb-2">💡 Saran untuk reservasi berikutnya:</h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• Pilih tanggal yang berbeda (H-1 sampai H-7)</li>
                                <li>• Pilih slot waktu yang berbeda</li>
                                <li>• Kurangi jumlah tamu jika memungkinkan</li>
                                <li>• Buat reservasi lebih awal untuk mendapatkan slot terbaik</li>
                            </ul>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('member.reservations.create') }}"
                                class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Buat Reservasi Baru
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Payment Form (for confirmed reservations) -->
                @if ($reservation->status === 'confirmed')
                    @php
                        $paymentDeadline = $reservation->created_at->addHour();
                        $timeRemaining = now()->diffInMinutes($paymentDeadline, false);
                    @endphp

                    @if ($timeRemaining > 0)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                    </path>
                                </svg>
                                Pilih Metode Pembayaran
                            </h3>

                            <form method="POST" action="{{ route('member.reservations.pay', $reservation->id) }}">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <label class="relative group cursor-pointer">
                                        <input type="radio" name="payment_method" value="bca" class="sr-only peer"
                                            required>
                                        <div
                                            class="p-4 border-2 border-gray-200 rounded-lg transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 group-hover:shadow-md">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-12 h-8 bg-blue-600 rounded flex items-center justify-center mr-3">
                                                    <span class="text-white font-bold text-xs">BCA</span>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-800">BCA Virtual Account</div>
                                                    <div class="text-sm text-gray-600">Transfer melalui ATM/Mobile Banking
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="relative group cursor-pointer">
                                        <input type="radio" name="payment_method" value="gopay" class="sr-only peer"
                                            required>
                                        <div
                                            class="p-4 border-2 border-gray-200 rounded-lg transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 group-hover:shadow-md">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-12 h-8 bg-green-500 rounded flex items-center justify-center mr-3">
                                                    <span class="text-white font-bold text-xs">GP</span>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-800">GOPAY</div>
                                                    <div class="text-sm text-gray-600">Bayar dengan aplikasi Gojek</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="relative group cursor-pointer">
                                        <input type="radio" name="payment_method" value="ovo" class="sr-only peer"
                                            required>
                                        <div
                                            class="p-4 border-2 border-gray-200 rounded-lg transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 group-hover:shadow-md">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-12 h-8 bg-purple-600 rounded flex items-center justify-center mr-3">
                                                    <span class="text-white font-bold text-xs">OVO</span>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-800">OVO</div>
                                                    <div class="text-sm text-gray-600">Bayar dengan aplikasi OVO</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="relative group cursor-pointer">
                                        <input type="radio" name="payment_method" value="qris" class="sr-only peer"
                                            required>
                                        <div
                                            class="p-4 border-2 border-gray-200 rounded-lg transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 group-hover:shadow-md">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-12 h-8 bg-red-500 rounded flex items-center justify-center mr-3">
                                                    <span class="text-white font-bold text-xs">QR</span>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-800">QRIS</div>
                                                    <div class="text-sm text-gray-600">Scan QR Code untuk bayar</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="text-center">
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200 text-lg inline-flex items-center">
                                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                            </path>
                                        </svg>
                                        Bayar Sekarang - Rp {{ number_format($reservation->dp_amount, 0, ',', '.') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-red-800 mb-2 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Batas Waktu Pembayaran Habis
                            </h3>
                            <p class="text-red-700">Maaf, batas waktu pembayaran telah habis. Reservasi akan dibatalkan
                                otomatis dalam beberapa saat.</p>
                        </div>
                    @endif
                @endif

                <!-- Important Notes -->
                @if (in_array($reservation->status, ['confirmed', 'paid']))
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Catatan Penting
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <div class="flex items-start">
                                    <span class="text-blue-500 mr-2 mt-1">•</span>
                                    <span class="text-blue-700 text-sm">Harap datang tepat waktu sesuai slot yang telah
                                        dipilih</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="text-blue-500 mr-2 mt-1">•</span>
                                    <span class="text-blue-700 text-sm">Keterlambatan lebih dari 30 menit akan
                                        mengakibatkan pembatalan otomatis</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-start">
                                    <span class="text-blue-500 mr-2 mt-1">•</span>
                                    <span class="text-blue-700 text-sm">Biaya minimal makan sebesar Rp
                                        {{ number_format($reservation->minimal_charge, 0, ',', '.') }} akan dibayarkan di
                                        restoran</span>
                                </div>
                                <div class="flex items-start">
                                    <span class="text-blue-500 mr-2 mt-1">•</span>
                                    <span class="text-blue-700 text-sm">Simpan bukti reservasi ini untuk ditunjukkan saat
                                        datang ke restoran</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-between gap-4">
                    <a href="{{ route('member.reservations.index') }}"
                        class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors duration-200 text-center inline-flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Daftar
                    </a>

                    <div class="flex gap-2">
                        @if (in_array($reservation->status, ['pending', 'confirmed']))
                            @php
                                $canCancel = true;
                                if ($reservation->status === 'confirmed') {
                                    $paymentDeadline = $reservation->created_at->addHour();
                                    $timeRemaining = now()->diffInMinutes($paymentDeadline, false);
                                    $canCancel = $timeRemaining > 0;
                                }
                            @endphp

                            @if ($canCancel)
                                <form method="POST" action="{{ route('member.reservations.cancel', $reservation->id) }}"
                                    class="inline">
                                    @csrf
                                    <button type="submit"
                                        onclick="return confirm('Apakah Anda yakin ingin membatalkan reservasi ini?')"
                                        class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors duration-200 inline-flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Batalkan Reservasi
                                    </button>
                                </form>
                            @endif
                        @endif

                        @if ($reservation->status === 'paid')
                            <button onclick="window.print()"
                                class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200 inline-flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                    </path>
                                </svg>
                                Cetak Bukti
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($reservation->status === 'pending')
        @push('scripts')
            <script>
                // Auto refresh for pending reservations with enhanced feedback
                let refreshCount = 0;
                const maxRefreshes = 20; // Stop after 20 refreshes (1 minute)

                const refreshFunction = () => {
                    refreshCount++;

                    if (refreshCount >= maxRefreshes) {
                        window.showNotification('Proses memakan waktu lebih lama dari biasanya. Silakan refresh manual.',
                            'warning');
                        return;
                    }

                    // Update last update time
                    document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID');

                    // Check for status changes
                    fetch(window.location.href, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            // Check if status has changed by looking for different status indicators
                            const currentStatus = document.querySelector(
                                '.bg-yellow-100, .bg-blue-100, .bg-green-100, .bg-red-100');
                            const parser = new DOMParser();
                            const newDoc = parser.parseFromString(html, 'text/html');
                            const newStatus = newDoc.querySelector(
                                '.bg-yellow-100, .bg-blue-100, .bg-green-100, .bg-red-100');

                            if (currentStatus && newStatus && currentStatus.className !== newStatus.className) {
                                window.showNotification('Status reservasi telah diperbarui!', 'success');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1500);
                            }
                        })
                        .catch(error => {
                            console.error('Status check failed:', error);
                        });
                };

                // Start polling
                const pollInterval = setInterval(refreshFunction, 3000);

                // Stop polling after max time
                setTimeout(() => {
                    clearInterval(pollInterval);
                }, maxRefreshes * 3000);
            </script>
        @endpush
    @elseif ($reservation->status === 'confirmed')
        @php
            $paymentDeadline = $reservation->created_at->addHour();
            $timeRemaining = now()->diffInMinutes($paymentDeadline, false);
        @endphp

        @if ($timeRemaining > 0)
            @push('scripts')
                <script>
                    // Countdown timer for payment deadline
                    let timeRemaining = {{ $timeRemaining }};

                    const updateCountdown = () => {
                        const timerElement = document.getElementById('countdown-timer');
                        const barElement = document.getElementById('countdown-bar');

                        if (timerElement && barElement) {
                            timerElement.textContent = timeRemaining + ' menit';
                            const percentage = (timeRemaining / 60) * 100;
                            barElement.style.width = percentage + '%';

                            // Change color as time runs out
                            if (timeRemaining <= 10) {
                                barElement.classList.remove('bg-blue-500');
                                barElement.classList.add('bg-red-500');
                                timerElement.classList.add('text-red-600', 'animate-pulse');
                            } else if (timeRemaining <= 30) {
                                barElement.classList.remove('bg-blue-500');
                                barElement.classList.add('bg-yellow-500');
                            }
                        }

                        timeRemaining--;

                        if (timeRemaining < 0) {
                            window.showNotification('Batas waktu pembayaran habis. Halaman akan diperbarui.', 'warning');
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                            return;
                        }

                        // Show warnings
                        if (timeRemaining === 10) {
                            window.showNotification('⚠️ Sisa waktu pembayaran 10 menit!', 'warning', 10000);
                        } else if (timeRemaining === 5) {
                            window.showNotification('🚨 Sisa waktu pembayaran 5 menit!', 'error', 15000);
                        }
                    };

                    // Update countdown every minute
                    const countdownInterval = setInterval(updateCountdown, 60000);

                    // Initial update
                    updateCountdown();

                    // Update last update time
                    setInterval(() => {
                        document.getElementById('last-update').textContent = new Date().toLocaleTimeString('id-ID');
                    }, 30000);
                </script>
            @endpush
        @endif
    @endif
@endsection
