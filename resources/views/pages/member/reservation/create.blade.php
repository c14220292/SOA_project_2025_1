@extends('layouts.app')

@section('title', 'Buat Reservasi')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-red-800 text-white p-6">
                <h1 class="text-2xl font-bold text-center">RESERVASI</h1>
                <p class="text-center text-red-200 mt-2">Masukkan Data Reservasi</p>
            </div>

            <!-- Form -->
            <div class="p-6">
                <form method="POST" action="{{ route('member.reservations.select-time') }}" class="space-y-6">
                    @csrf

                    <!-- Jumlah Orang -->
                    <div>
                        <label for="guest_count" class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Orang
                        </label>
                        <input type="number" id="guest_count" name="guest_count" min="1" max="20"
                            value="{{ old('guest_count', session('reservation_step1.guest_count')) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            required>
                        <p class="text-sm text-gray-500 mt-1">Setiap meja menampung maksimal 4 orang</p>
                    </div>

                    <!-- Pilih Tanggal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Pilih Tanggal Reservasi
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($dates as $date)
                                <label class="relative">
                                    <input type="radio" name="reservation_date" value="{{ $date['date'] }}"
                                        {{ old('reservation_date', session('reservation_step1.reservation_date')) == $date['date'] ? 'checked' : '' }}
                                        class="sr-only peer" required>
                                    <div
                                        class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition-all duration-200 peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300">
                                        <div class="text-center">
                                            <div class="font-semibold text-gray-800">{{ $date['day_name'] }}</div>
                                            <div class="text-sm text-gray-600">{{ $date['formatted'] }}</div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-sm text-gray-500 mt-2">
                            Reservasi hanya dapat dilakukan H-1 sampai H-7 dari tanggal yang dipilih
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center pt-4">
                        <button type="submit"
                            class="bg-red-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            Lanjut ke Pilih Waktu
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h3 class="font-semibold text-yellow-800 mb-2">Informasi Penting:</h3>
            <ul class="text-sm text-yellow-700 space-y-1">
                <li>• Setiap meja menampung maksimal 4 kursi</li>
                <li>• Biaya DP = Jumlah Meja × Jumlah Slot × Rp 15.000</li>
                <li>• Biaya minimal makan = Jumlah Meja × Rp 50.000</li>
                <li>• Pembatalan reservasi dikenakan potongan 25% dari DP</li>
                <li>• <strong>Batas waktu pembayaran DP: 1 jam setelah konfirmasi admin</strong></li>
            </ul>
        </div>
    </div>
@endsection
