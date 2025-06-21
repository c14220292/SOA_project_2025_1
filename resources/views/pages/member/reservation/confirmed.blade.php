@extends('layouts.app')

@section('title', 'Reservasi Dikonfirmasi')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Success Header -->
            <div class="bg-green-100 border-b-4 border-green-500 p-6">
                <div class="text-center">
                    <div class="text-green-600 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-green-800">RESERVASI DIKONFIRMASI ✅</h1>
                    <p class="text-green-700 mt-2">Terima kasih! Pembayaran berhasil dan reservasi Anda telah dikonfirmasi.
                    </p>
                </div>
            </div>

            <!-- Reservation Details -->
            <div class="p-6">
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Reservasi Final</h2>

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
                            <p class="text-gray-800 font-semibold">{{ $reservation->guest_count }} Orang</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jumlah Meja</label>
                            <p class="text-gray-800 font-semibold">{{ $reservation->table_count }} Meja</p>
                        </div>

                        @if ($reservation->slotTimes->isNotEmpty())
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Slot Waktu</label>
                                <div class="text-gray-800 font-semibold">
                                    @foreach ($reservation->slotTimes as $slot)
                                        <div
                                            class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm inline-block mr-2 mb-1">
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
                                <p class="font-semibold text-lg text-green-600">
                                    Meja {{ implode(', ', $reservation->table_numbers) }}
                                </p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Biaya DP (Sudah Dibayar)</label>
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
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-blue-800 mb-3">Catatan Penting:</h3>
                    <ul class="text-sm text-blue-700 space-y-2">
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            <span>Harap datang tepat waktu sesuai slot yang telah dipilih</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            <span>Keterlambatan lebih dari 30 menit akan mengakibatkan pembatalan otomatis</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            <span>Biaya minimal makan sebesar Rp
                                {{ number_format($reservation->minimal_charge, 0, ',', '.') }} akan dibayarkan di
                                restoran</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            <span>Simpan bukti reservasi ini untuk ditunjukkan saat datang ke restoran</span>
                        </li>
                    </ul>
                </div>

                <!-- Cancellation Policy -->
                @php
                    $now = now();
                    $reservationDate = $reservation->reservation_date;
                    $diffInDays = $now->diffInDays($reservationDate, false);
                @endphp

                @if ($diffInDays >= 1)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-yellow-800 mb-3">Kebijakan Pembatalan:</h3>
                        <div class="text-sm text-yellow-700 space-y-2">
                            <p>Potongan 25% dari biaya DP</p>
                        </div>

                        @if ($diffInDays >= 1)
                            <div class="mt-4">
                                <button onclick="showCancelModal()"
                                    class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition-colors">
                                    Batalkan Reservasi
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('member.reservations.index') }}"
                        class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors duration-200">
                        Kembali ke Daftar Reservasi
                    </a>

                    <button onclick="window.print()"
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-200">
                        Cetak Bukti Reservasi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Batalkan Reservasi</h3>

            <p class="text-gray-600 mb-4">Membatalkan reservasi akan memotong 25% pengembalian biaya DP.</p>

            <div class="flex justify-end space-x-3">
                <button onclick="hideCancelModal()"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">
                    Batal
                </button>
                <form method="POST" action="{{ route('member.reservations.cancel', $reservation->id) }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                        Batalkan Reservasi
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showCancelModal() {
                document.getElementById('cancelModal').classList.remove('hidden');
                document.getElementById('cancelModal').classList.add('flex');
            }

            function hideCancelModal() {
                document.getElementById('cancelModal').classList.add('hidden');
                document.getElementById('cancelModal').classList.remove('flex');
            }
        </script>
    @endpush
@endsection
