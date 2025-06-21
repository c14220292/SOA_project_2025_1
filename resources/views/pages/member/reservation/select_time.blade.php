@extends('layouts.app')

@section('title', 'Pilih Waktu Reservasi')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-red-800 text-white p-6">
                <h1 class="text-2xl font-bold text-center">PILIH SLOT WAKTU</h1>
                <div class="text-center text-red-200 mt-2">
                    <p>{{ \Carbon\Carbon::parse($reservationDate)->translatedFormat('l, d M Y') }}</p>
                    <p>{{ $guestCount }} Orang ({{ $tableCount }} Meja)</p>
                </div>
            </div>

            <!-- Form -->
            <div class="p-6">
                @if (empty($availableSlots))
                    <div class="text-center py-8">
                        <div class="text-gray-500 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak Ada Slot Waktu Tersedia</h3>
                        <p class="text-gray-500 mb-4">Maaf, tidak ada slot waktu yang tersedia untuk tanggal dan jumlah meja
                            yang dipilih.</p>
                        <a href="{{ route('member.reservations.create') }}"
                            class="bg-red-800 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
                            Pilih Tanggal Lain
                        </a>
                    </div>
                @else
                    <form method="POST" action="{{ route('member.reservations.confirm') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="reservation_date" value="{{ $reservationDate }}">
                        <input type="hidden" name="guest_count" value="{{ $guestCount }}">
                        <input type="hidden" name="table_count" value="{{ $tableCount }}">

                        <!-- Available Slots -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Pilih Slot Waktu (Anda dapat memilih lebih dari satu slot)
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach ($availableSlots as $slot)
                                    <label class="relative">
                                        <input type="checkbox" name="slot_time_ids[]" value="{{ $slot['id'] }}"
                                            {{ in_array($slot['id'], old('slot_time_ids', session('reservation_step2.slot_time_ids', []))) ? 'checked' : '' }}
                                            class="sr-only peer">
                                        <div
                                            class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition-all duration-200 peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300">
                                            <div class="text-center">
                                                <div class="font-semibold text-gray-800">{{ $slot['time'] }}</div>
                                                <div class="text-sm text-green-600">{{ $slot['available_tables'] }} meja
                                                    tersedia</div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label for="note" class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan Tambahan (Opsional)
                            </label>
                            <textarea id="note" name="note" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="Masukkan catatan khusus untuk reservasi Anda...">{{ old('note', session('reservation_step2.note')) }}</textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-between pt-4">
                            <a href="{{ route('member.reservations.create') }}"
                                class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors duration-200">
                                Kembali
                            </a>
                            <button type="submit"
                                class="bg-red-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-red-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Lanjut ke Konfirmasi
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- Calculation Info -->
        @if (!empty($availableSlots))
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-semibold text-blue-800 mb-2">Perhitungan Biaya:</h3>
                <div class="text-sm text-blue-700 space-y-1">
                    <p>• Biaya DP = {{ $tableCount }} Meja × Jumlah Slot × Rp 15.000</p>
                    <p>• Biaya Minimal Makan = {{ $tableCount }} Meja × Rp 50.000 = Rp
                        {{ number_format($tableCount * 50000, 0, ',', '.') }}</p>
                    <p class="text-xs text-blue-600 mt-2">*Biaya DP akan dihitung berdasarkan jumlah slot yang dipilih</p>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                const submitBtn = form?.querySelector('button[type="submit"]');

                if (form && submitBtn) {
                    form.addEventListener('submit', function(e) {
                        const checkedSlots = form.querySelectorAll('input[name="slot_time_ids[]"]:checked');
                        if (checkedSlots.length === 0) {
                            e.preventDefault();
                            alert('Silakan pilih minimal satu slot waktu.');
                            return false;
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
