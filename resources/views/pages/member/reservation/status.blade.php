@extends('layouts.app')

@section('title', 'Status Reservasi')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header with Status -->
            <div
                class="p-6 {{ $reservation->status === 'pending' ? 'bg-yellow-100 border-b-4 border-yellow-500' : ($reservation->status === 'confirmed' ? 'bg-blue-100 border-b-4 border-blue-500' : ($reservation->status === 'paid' ? 'bg-green-100 border-b-4 border-green-500' : ($reservation->status === 'rejected' ? 'bg-red-100 border-b-4 border-red-500' : 'bg-gray-100 border-b-4 border-gray-500'))) }}">
                <div class="text-center">
                    <h1
                        class="text-2xl font-bold {{ $reservation->status === 'pending' ? 'text-yellow-800' : ($reservation->status === 'confirmed' ? 'text-blue-800' : ($reservation->status === 'paid' ? 'text-green-800' : ($reservation->status === 'rejected' ? 'text-red-800' : 'text-gray-800'))) }}">
                        @switch($reservation->status)
                            @case('pending')
                                MENUNGGU KONFIRMASI...
                            @break

                            @case('confirmed')
                                PROSES PEMBAYARAN DP...
                            @break

                            @case('paid')
                                RESERVASI DIKONFIRMASI ✅
                            @break

                            @case('rejected')
                                RESERVASI DITOLAK ❌
                            @break

                            @case('cancelled')
                                RESERVASI DIBATALKAN ❌
                            @break

                            @default
                                STATUS TIDAK DIKETAHUI
                        @endswitch
                    </h1>

                    @if ($reservation->status === 'pending')
                        <p class="text-yellow-700 mt-2">Jika belum direspons dalam 1 jam, hubungi WhatsApp kami.</p>
                    @elseif($reservation->status === 'confirmed')
                        @php
                            $paymentDeadline = $reservation->created_at->addHour();
                            $timeRemaining = now()->diffInMinutes($paymentDeadline, false);
                        @endphp

                        @if ($timeRemaining > 0)
                            <p class="text-blue-700 mt-2">Pembayaran harus dilakukan sebelum batas waktu:</p>
                            <p class="text-blue-800 font-semibold">
                                Batas: {{ $paymentDeadline->translatedFormat('l, d M Y (H:i)') }}
                            </p>
                            <p class="text-blue-600 text-sm mt-1">
                                Sisa waktu: {{ $timeRemaining }} menit
                            </p>
                        @else
                            <p class="text-red-700 mt-2">Batas waktu pembayaran telah habis. Reservasi akan dibatalkan
                                otomatis.</p>
                        @endif
                    @elseif($reservation->status === 'paid')
                        <p class="text-green-700 mt-2">Terima kasih! Reservasi Anda telah dikonfirmasi.</p>
                    @elseif($reservation->status === 'cancelled')
                        <p class="text-gray-700 mt-2">Reservasi telah dibatalkan karena melewati batas waktu pembayaran.</p>
                    @endif
                </div>
            </div>

            <!-- Reservation Details -->
            <div class="p-6">
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Reservasi</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Pelanggan</label>
                            <p class="text-gray-800 font-semibold">{{ $reservation->user->name }}</p>
                            @if ($reservation->user->phone)
                                <p class="text-sm text-gray-600">{{ substr($reservation->user->phone, 0, 4) }}-xxxx-xxxx</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Tanggal Reservasi</label>
                            <p class="text-gray-800 font-semibold">
                                {{ $reservation->reservation_date->translatedFormat('l, d M Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jumlah Tamu</label>
                            <p class="text-gray-800 font-semibold">{{ $reservation->guest_count }} Orang
                                ({{ $reservation->table_count }} Meja)</p>
                        </div>

                        @if ($reservation->slotTimes->isNotEmpty())
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Slot Waktu</label>
                                <div class="text-gray-800 font-semibold">
                                    @foreach ($reservation->slotTimes as $slot)
                                        <div
                                            class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm inline-block mr-2 mb-1">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($reservation->tables->isNotEmpty())
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Nomor Meja</label>
                                <p class="text-gray-800 font-semibold">{{ implode(', ', $reservation->table_numbers) }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Biaya DP</label>
                            <p class="text-gray-800 font-semibold">Rp
                                {{ number_format($reservation->dp_amount, 0, ',', '.') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Biaya Minimal Makan</label>
                            <p class="text-gray-800 font-semibold">Rp
                                {{ number_format($reservation->minimal_charge, 0, ',', '.') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Waktu Reservasi</label>
                            <p class="text-gray-800 font-semibold">
                                {{ $reservation->created_at->translatedFormat('l, d M Y (H:i)') }}</p>
                        </div>

                        @if ($reservation->payment_time)
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Waktu Pembayaran</label>
                                <p class="text-gray-800 font-semibold">
                                    {{ $reservation->payment_time->translatedFormat('l, d M Y (H:i)') }}</p>
                            </div>
                        @endif

                        @if ($reservation->payment_method)
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Metode Pembayaran</label>
                                <p class="text-gray-800 font-semibold">{{ strtoupper($reservation->payment_method) }}</p>
                            </div>
                        @endif

                        @if ($reservation->note)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-600">Catatan</label>
                                <p class="text-gray-800">{{ $reservation->note }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Form (for confirmed reservations) -->
                @if ($reservation->status === 'confirmed')
                    @php
                        $paymentDeadline = $reservation->created_at->addHour();
                        $timeRemaining = now()->diffInMinutes($paymentDeadline, false);
                    @endphp

                    @if ($timeRemaining > 0)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-blue-800 mb-4">Pilih Metode Pembayaran</h3>
                            <div class="bg-blue-100 p-3 rounded mb-4">
                                <p class="text-blue-800 text-sm font-semibold">
                                    ⏰ Sisa waktu pembayaran: {{ $timeRemaining }} menit
                                </p>
                            </div>

                            <form method="POST" action="{{ route('member.reservations.pay', $reservation->id) }}">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <label class="relative">
                                        <input type="radio" name="payment_method" value="bca" class="sr-only peer"
                                            required>
                                        <div
                                            class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                            <div class="text-center">
                                                <div class="font-semibold text-gray-800">BCA Virtual Account</div>
                                                <div class="text-sm text-gray-600">Transfer melalui ATM/Mobile Banking</div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="relative">
                                        <input type="radio" name="payment_method" value="gopay" class="sr-only peer"
                                            required>
                                        <div
                                            class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                            <div class="text-center">
                                                <div class="font-semibold text-gray-800">GOPAY</div>
                                                <div class="text-sm text-gray-600">Bayar dengan aplikasi Gojek</div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="relative">
                                        <input type="radio" name="payment_method" value="ovo" class="sr-only peer"
                                            required>
                                        <div
                                            class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                            <div class="text-center">
                                                <div class="font-semibold text-gray-800">OVO</div>
                                                <div class="text-sm text-gray-600">Bayar dengan aplikasi OVO</div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="relative">
                                        <input type="radio" name="payment_method" value="qris" class="sr-only peer"
                                            required>
                                        <div
                                            class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                            <div class="text-center">
                                                <div class="font-semibold text-gray-800">QRIS</div>
                                                <div class="text-sm text-gray-600">Scan QR Code untuk bayar</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="text-center">
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200">
                                        Lanjut ke Pembayaran
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-red-800 mb-2">Batas Waktu Pembayaran Habis</h3>
                            <p class="text-red-700">Maaf, batas waktu pembayaran telah habis. Reservasi akan dibatalkan
                                otomatis.</p>
                        </div>
                    @endif
                @endif

                <!-- Action Buttons -->
                <div class="flex justify-between">
                    <a href="{{ route('member.reservations.index') }}"
                        class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors duration-200">
                        Kembali ke Daftar
                    </a>

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
                                    class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors duration-200">
                                    Batalkan Reservasi
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($reservation->status === 'confirmed')
        @php
            $paymentDeadline = $reservation->created_at->addHour();
            $timeRemaining = now()->diffInMinutes($paymentDeadline, false);
        @endphp

        @if ($timeRemaining > 0)
            @push('scripts')
                <script>
                    // Auto refresh page when payment deadline is reached
                    setTimeout(function() {
                        location.reload();
                    }, {{ $timeRemaining * 60 * 1000 }});

                    // Update countdown every minute
                    setInterval(function() {
                        location.reload();
                    }, 60000);
                </script>
            @endpush
        @endif
    @endif
@endsection
