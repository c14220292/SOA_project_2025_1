@extends('layouts.admin')

@section('title', 'Daftar Reservasi')

@section('content')
    <div class="min-h-screen bg-[#1a1a1a] text-white">
        <!-- Header -->
        <div class="bg-[#8B0000] py-4">
            <div class="container mx-auto px-4 flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="text-[#D4AF37] text-2xl mr-4">←</a>
                <h1 class="text-2xl font-bold text-[#D4AF37]">DAFTAR RESERVASI</h1>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Date Filter -->
            <div class="bg-[#8B0000] rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-[#D4AF37] font-bold text-lg">Tanggal</h3>
                        <p class="text-white">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d M Y') }}</p>
                    </div>
                    <form method="GET" action="{{ route('admin.reservations.index') }}" class="flex items-center gap-2">
                        <input type="date" name="date" value="{{ $selectedDate }}"
                            class="bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                        <button type="submit" class="bg-[#D4AF37] text-[#1a1a1a] px-4 py-2 rounded font-semibold">
                            Cari
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-gray-600 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-300">Total</div>
                </div>
                <div class="bg-yellow-600 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-white">{{ $stats['pending'] }}</div>
                    <div class="text-sm text-yellow-100">Pending</div>
                </div>
                <div class="bg-blue-600 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-white">{{ $stats['confirmed'] }}</div>
                    <div class="text-sm text-blue-100">Dikonfirmasi</div>
                </div>
                <div class="bg-green-600 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-white">{{ $stats['paid'] }}</div>
                    <div class="text-sm text-green-100">Dibayar</div>
                </div>
                <div class="bg-red-600 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-white">{{ $stats['rejected'] }}</div>
                    <div class="text-sm text-red-100">Ditolak</div>
                </div>
                <div class="bg-gray-500 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-white">{{ $stats['cancelled'] }}</div>
                    <div class="text-sm text-gray-100">Dibatalkan</div>
                </div>
            </div>

            <!-- Reservations by Status -->
            @foreach (['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'paid' => 'Dibayar', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'] as $status => $label)
                @if (isset($groupedReservations[$status]) && $groupedReservations[$status]->isNotEmpty())
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-[#D4AF37] mb-4">{{ $label }}
                            ({{ $groupedReservations[$status]->count() }})</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($groupedReservations[$status] as $reservation)
                                <div class="bg-[#8B0000] rounded-lg p-4">
                                    <!-- Status Badge -->
                                    <div class="flex justify-between items-center mb-3">
                                        <span
                                            class="px-2 py-1 rounded text-xs font-semibold
                                            {{ $status === 'pending'
                                                ? 'bg-yellow-600 text-yellow-100'
                                                : ($status === 'confirmed'
                                                    ? 'bg-blue-600 text-blue-100'
                                                    : ($status === 'paid'
                                                        ? 'bg-green-600 text-green-100'
                                                        : ($status === 'rejected'
                                                            ? 'bg-red-600 text-red-100'
                                                            : 'bg-gray-600 text-gray-100'))) }}">
                                            {{ $label }}
                                        </span>
                                        <span class="text-xs text-gray-300">
                                            {{ $reservation->created_at->format('H:i') }}
                                        </span>
                                    </div>

                                    <h4 class="text-[#D4AF37] font-bold text-lg mb-2">
                                        {{ $reservation->table_count }} MEJA - {{ $reservation->guest_count }} ORANG
                                    </h4>

                                    <div class="text-white text-sm space-y-1 mb-4">
                                        @foreach ($reservation->slotTimes as $slot)
                                            <p>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</p>
                                        @endforeach
                                        <p class="font-semibold">{{ $reservation->user->name }}</p>
                                        <p>{{ $reservation->user->email }}</p>
                                        @if ($reservation->user->phone)
                                            <p>{{ $reservation->user->phone }}</p>
                                        @endif
                                        @if ($reservation->tables->isNotEmpty())
                                            <p class="text-[#D4AF37] font-semibold">
                                                Meja: {{ implode(', ', $reservation->table_numbers) }}
                                            </p>
                                        @endif
                                        @if ($reservation->payment_method)
                                            <p class="text-green-300">
                                                Bayar: {{ strtoupper($reservation->payment_method) }}
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex gap-2">
                                        @if ($status === 'pending')
                                            <form method="POST"
                                                action="{{ route('admin.reservations.reject', $reservation->id) }}"
                                                class="flex-1">
                                                @csrf
                                                <button type="submit"
                                                    onclick="return confirm('Yakin ingin menolak reservasi ini?')"
                                                    class="w-full bg-red-600 text-white py-2 rounded font-semibold hover:bg-red-700">
                                                    Tolak
                                                </button>
                                            </form>
                                            <button type="button" onclick="showTableSelection({{ $reservation->id }})"
                                                class="flex-1 bg-[#D4AF37] text-[#1a1a1a] py-2 rounded font-semibold hover:bg-yellow-500">
                                                Terima Manual
                                            </button>
                                        @elseif($status === 'rejected')
                                            <form method="POST"
                                                action="{{ route('admin.reservations.reprocess', $reservation->id) }}"
                                                class="flex-1">
                                                @csrf
                                                <button type="submit"
                                                    class="w-full bg-blue-600 text-white py-2 rounded font-semibold hover:bg-blue-700">
                                                    Proses Ulang
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($reservations->isEmpty())
                <div class="bg-[#2a2a2a] rounded-lg p-8 text-center">
                    <p class="text-gray-400">Tidak ada reservasi untuk tanggal ini</p>
                </div>
            @endif

            <!-- Table Schedule Grid -->
            <div class="mt-8">
                <h2 class="text-xl font-bold text-[#D4AF37] mb-4">Jadwal Meja</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    @php
                        $timeSlots = \App\Models\SlotTime::where('date', $selectedDate)->orderBy('start_time')->get();
                    @endphp

                    @forelse($tables as $table)
                        @php
                            $occupiedSlotIds = $table->getOccupiedSlotIds($selectedDate);
                        @endphp

                        <div class="bg-[#F5E6A3] text-[#1a1a1a] rounded-lg p-3">
                            <h5 class="font-bold text-center mb-2">Meja {{ $table->number }}</h5>
                            @foreach ($timeSlots as $slot)
                                @php
                                    $isOccupied = in_array($slot->id, $occupiedSlotIds);
                                @endphp
                                <div
                                    class="text-xs text-center py-1 mb-1 rounded {{ $isOccupied ? 'bg-[#8B0000] text-white' : 'bg-[#F5E6A3] text-[#8B0000] border border-[#8B0000]' }}">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="col-span-full bg-[#2a2a2a] rounded-lg p-8 text-center">
                            <p class="text-gray-400">Belum ada meja yang terdaftar</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Table Selection Modal -->
    <div id="tableSelectionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-[#1a1a1a] rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-[#D4AF37]">TERIMA RESERVASI MANUAL</h3>
                <button onclick="hideTableSelection()" class="text-[#D4AF37] text-2xl">×</button>
            </div>

            <div id="reservationDetails" class="mb-6"></div>

            <form id="approveForm" method="POST">
                @csrf
                <div class="mb-4">
                    <h4 class="text-[#D4AF37] font-semibold mb-2">Pilih Nomor Meja:</h4>
                    <div id="tableSelection" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <!-- Tables will be loaded here -->
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="button" onclick="hideTableSelection()"
                        class="flex-1 bg-gray-600 text-white py-3 rounded font-semibold hover:bg-gray-700">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 bg-[#D4AF37] text-[#1a1a1a] py-3 rounded font-semibold hover:bg-yellow-500">
                        Terima
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function showTableSelection(reservationId) {
                const reservations = @json($reservations);
                const reservation = reservations.find(r => r.id === reservationId);
                const tables = @json($tables);

                if (!reservation) return;

                // Calculate required tables (4 seats per table)
                const requiredTables = Math.ceil(reservation.guest_count / 4);

                // Set form action
                document.getElementById('approveForm').action = `/admin/reservations/${reservationId}/approve`;

                // Show reservation details
                const detailsHtml = `
                    <div class="bg-[#8B0000] rounded-lg p-4 text-white">
                        <h4 class="text-[#D4AF37] font-bold mb-2">Detail Reservasi:</h4>
                        <p><strong>Pelanggan:</strong> ${reservation.user.name}</p>
                        <p><strong>Tanggal:</strong> ${new Date(reservation.reservation_date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        <p><strong>Tamu:</strong> ${reservation.guest_count} orang</p>
                        <p><strong>Meja dibutuhkan:</strong> minimal ${requiredTables} meja</p>
                        <p><strong>Slot waktu:</strong> ${reservation.slot_times.map(slot => {
                            const startTime = slot.start_time.includes(':') ? slot.start_time : new Date('1970-01-01T' + slot.start_time).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
                            const endTime = slot.end_time.includes(':') ? slot.end_time : new Date('1970-01-01T' + slot.end_time).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
                            return startTime + ' - ' + endTime;
                        }).join(', ')}</p>
                        <p class="text-yellow-300 mt-2"><strong>Catatan:</strong> Pilih minimal ${requiredTables} meja untuk menampung ${reservation.guest_count} orang</p>
                    </div>
                `;
                document.getElementById('reservationDetails').innerHTML = detailsHtml;

                // Show available tables
                const slotTimeIds = reservation.slot_times.map(slot => slot.id);
                let tableHtml = '';

                tables.forEach(table => {
                    // Check if table is available for the selected slots
                    const occupiedSlotIds = table.reservations
                        .filter(res => res.reservation_date === reservation.reservation_date && ['confirmed', 'paid']
                            .includes(res.status))
                        .flatMap(res => res.slot_times.map(slot => slot.id));

                    const isAvailable = !slotTimeIds.some(slotId => occupiedSlotIds.includes(slotId));

                    if (isAvailable && table.is_available) {
                        tableHtml += `
                            <label class="cursor-pointer">
                                <input type="checkbox" name="table_ids[]" value="${table.id}" class="sr-only peer">
                                <div class="bg-[#F5E6A3] text-[#1a1a1a] p-3 rounded text-center peer-checked:bg-[#D4AF37] peer-checked:ring-2 peer-checked:ring-yellow-400 transition-all">
                                    <div class="font-semibold">Meja ${table.number}</div>
                                    <div class="text-sm">${table.seat_count} Kursi</div>
                                </div>
                            </label>
                        `;
                    }
                });

                if (tableHtml === '') {
                    tableHtml =
                        '<div class="col-span-full text-center text-red-400">Tidak ada meja yang tersedia untuk slot waktu ini</div>';
                }

                document.getElementById('tableSelection').innerHTML = tableHtml;

                // Show modal
                document.getElementById('tableSelectionModal').classList.remove('hidden');
                document.getElementById('tableSelectionModal').classList.add('flex');
            }

            function hideTableSelection() {
                document.getElementById('tableSelectionModal').classList.add('hidden');
                document.getElementById('tableSelectionModal').classList.remove('flex');
            }

            // Validate table selection
            document.getElementById('approveForm').addEventListener('submit', function(e) {
                const checkedTables = this.querySelectorAll('input[name="table_ids[]"]:checked');
                const reservations = @json($reservations);
                const formAction = this.action;
                const reservationId = parseInt(formAction.split('/').slice(-2, -1)[0]);
                const reservation = reservations.find(r => r.id === reservationId);

                if (!reservation) return;

                const requiredTables = Math.ceil(reservation.guest_count / 4);

                if (checkedTables.length < requiredTables) {
                    e.preventDefault();
                    alert(`Silakan pilih minimal ${requiredTables} meja untuk ${reservation.guest_count} orang.`);
                    return false;
                }

                // Calculate total seats
                const tables = @json($tables);
                let totalSeats = 0;
                checkedTables.forEach(checkbox => {
                    const table = tables.find(t => t.id == checkbox.value);
                    if (table) totalSeats += table.seat_count;
                });

                if (totalSeats < reservation.guest_count) {
                    e.preventDefault();
                    alert(
                        `Meja yang dipilih hanya dapat menampung ${totalSeats} orang, sedangkan reservasi untuk ${reservation.guest_count} orang.`
                    );
                    return false;
                }
            });
        </script>
    @endpush
@endsection
