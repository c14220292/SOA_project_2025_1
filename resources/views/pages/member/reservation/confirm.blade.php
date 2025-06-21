@extends('layouts.app')

@section('title', 'Konfirmasi Reservasi')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-red-800 text-white p-6">
                <h1 class="text-2xl font-bold text-center">KONFIRMASI RESERVASI</h1>
                <p class="text-center text-red-200 mt-2">Periksa kembali detail reservasi Anda</p>
            </div>

            <!-- Reservation Details -->
            <div class="p-6">
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Reservasi</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Tanggal Reservasi</label>
                            <p class="text-gray-800 font-semibold">
                                {{ \Carbon\Carbon::parse($reservationDate)->translatedFormat('l, d M Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jumlah Tamu</label>
                            <p class="text-gray-800 font-semibold">{{ $guestCount }} Orang</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Jumlah Meja</label>
                            <p class="text-gray-800 font-semibold">{{ $tableCount }} Meja</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600">Slot Waktu</label>
                            <div class="text-gray-800 font-semibold">
                                @foreach ($slotTimes as $slot)
                                    <div class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm inline-block mr-2 mb-1">
                                        {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cost Breakdown -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-yellow-800 mb-4">Rincian Biaya</h2>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700">Biaya DP</span>
                            <span class="font-semibold">{{ $tableCount }} Meja × {{ count($slotTimes) }} Slot × Rp
                                15.000</span>
                            <span class="font-bold text-lg">Rp {{ number_format($dpAmount, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center border-t pt-3">
                            <span class="text-gray-700">Biaya Minimal Makan</span>
                            <span class="font-semibold">{{ $tableCount }} Meja × Rp 50.000</span>
                            <span class="font-bold text-lg">Rp {{ number_format($minimalCharge, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-yellow-100 rounded">
                        <p class="text-sm text-yellow-800">
                            <strong>Catatan:</strong> Biaya DP akan dibayarkan terlebih dahulu setelah konfirmasi admin.
                            Biaya minimal makan akan dibayarkan saat di restoran.
                        </p>
                    </div>
                </div>

                <!-- Payment Deadline Warning -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-red-800 mb-2">⚠️ Penting - Batas Waktu Pembayaran:</h3>
                    <ul class="text-sm text-red-700 space-y-1">
                        <li>• Setelah admin mengkonfirmasi reservasi, Anda memiliki <strong>1 jam</strong> untuk melakukan
                            pembayaran DP</li>
                        <li>• Jika melewati batas waktu, reservasi akan <strong>dibatalkan otomatis</strong></li>
                        <li>• Pastikan Anda siap untuk melakukan pembayaran setelah konfirmasi</li>
                    </ul>
                </div>

                <!-- Confirmation Form -->
                <form method="POST" action="{{ route('member.reservations.store') }}">
                    @csrf
                    <input type="hidden" name="reservation_date" value="{{ $reservationDate }}">
                    <input type="hidden" name="guest_count" value="{{ $guestCount }}">
                    <input type="hidden" name="table_count" value="{{ $tableCount }}">
                    @foreach ($slotTimeIds as $slotId)
                        <input type="hidden" name="slot_time_ids[]" value="{{ $slotId }}">
                    @endforeach

                    <!-- Terms and Conditions -->
                    <div class="mb-6">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h3 class="font-semibold text-red-800 mb-2">Syarat dan Ketentuan:</h3>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li>• Reservasi akan dikonfirmasi oleh admin dalam 1 jam</li>
                                <li>• <strong>Pembayaran DP harus dilakukan dalam 1 jam setelah konfirmasi</strong></li>
                                <li>• Pembatalan reservasi dikenakan potongan 25% dari DP</li>
                                <li>• Keterlambatan lebih dari 30 menit akan mengakibatkan pembatalan otomatis</li>
                            </ul>
                        </div>

                        <label class="flex items-center mt-4">
                            <input type="checkbox" required
                                class="mr-2 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="text-sm text-gray-700">Saya menyetujui syarat dan ketentuan di atas</span>
                        </label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-between">
                        <button type="button" onclick="history.back()"
                            class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors duration-200">
                            Kembali
                        </button>
                        <button type="submit"
                            class="bg-red-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            Konfirmasi Reservasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
