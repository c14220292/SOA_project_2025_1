@extends('layouts.admin')

@section('title', 'Manajemen Slot Waktu')

@section('content')
    <div class="min-h-screen bg-[#1a1a1a] text-white">
        <!-- Header -->
        <div class="bg-[#8B0000] py-4">
            <div class="container mx-auto px-4 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-[#D4AF37] text-2xl mr-4">←</a>
                    <h1 class="text-2xl font-bold text-[#D4AF37]">MANAJEMEN SLOT WAKTU</h1>
                </div>
                <div class="text-[#D4AF37] font-semibold">
                    {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Controls -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex gap-2">
                    <button onclick="showAddSlotModal()"
                        class="bg-[#D4AF37] text-[#1a1a1a] px-4 py-2 rounded font-semibold">
                        Tambah Slot Waktu
                    </button>
                    <button onclick="showDeleteSlotModal()" class="bg-red-600 text-white px-4 py-2 rounded font-semibold">
                        Hapus Slot Waktu
                    </button>
                    <button onclick="showDeleteAllSlotsModal()"
                        class="bg-red-800 text-white px-4 py-2 rounded font-semibold">
                        Hapus All Slot Waktu
                    </button>
                </div>
                <form method="GET" action="{{ route('admin.slots.index') }}" class="flex items-center gap-2">
                    <input type="date" name="date" value="{{ $selectedDate }}"
                        class="bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                    <button type="submit" class="bg-[#D4AF37] text-[#1a1a1a] px-4 py-2 rounded font-semibold">
                        Cari
                    </button>
                </form>
            </div>

            <!-- Slots Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @forelse($slots as $slot)
                    <div class="bg-[#D4AF37] text-[#1a1a1a] rounded-lg p-4 text-center">
                        <div class="font-bold text-lg">
                            {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}
                        </div>
                        <div class="text-sm">
                            {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                        </div>
                        <div class="text-xs mt-1">
                            {{ \Carbon\Carbon::parse($slot->date)->translatedFormat('d M') }}
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-[#2a2a2a] rounded-lg p-8 text-center">
                        <p class="text-gray-400">Tidak ada slot waktu untuk tanggal ini</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add Slot Modal -->
    <div id="addSlotModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-[#8B0000] rounded-lg p-6 w-96 mx-4">
            <h3 class="text-xl font-bold text-[#D4AF37] mb-4 text-center">TAMBAH SLOT WAKTU</h3>
            <form method="POST" action="{{ route('admin.slots.store') }}">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate }}">
                <div class="mb-4">
                    <label class="block text-[#D4AF37] font-semibold mb-2">Masukkan Waktu Mulai</label>
                    <input type="time" name="start_time" required
                        class="w-full bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                </div>
                <div class="mb-6">
                    <label class="block text-[#D4AF37] font-semibold mb-2">Masukkan Waktu Selesai</label>
                    <input type="time" name="end_time" required
                        class="w-full bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                </div>
                <div class="flex gap-4">
                    <button type="button" onclick="hideAddSlotModal()"
                        class="flex-1 bg-gray-600 text-white py-2 rounded font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-[#D4AF37] text-[#1a1a1a] py-2 rounded font-semibold">
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Slot Modal -->
    <div id="deleteSlotModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-[#8B0000] rounded-lg p-6 w-96 mx-4">
            <h3 class="text-xl font-bold text-[#D4AF37] mb-4 text-center">HAPUS SLOT WAKTU</h3>
            <form method="POST" action="{{ route('admin.slots.destroy', 'placeholder') }}">
                @csrf
                @method('DELETE')
                <div class="mb-6">
                    <label class="block text-[#D4AF37] font-semibold mb-2">Pilih Slot Waktu</label>
                    <select name="slot_id" required
                        class="w-full bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                        <option value="">Pilih Slot</option>
                        @foreach ($slots as $slot)
                            <option value="{{ $slot->id }}">
                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-4">
                    <button type="button" onclick="hideDeleteSlotModal()"
                        class="flex-1 bg-gray-600 text-white py-2 rounded font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-red-600 text-white py-2 rounded font-semibold">
                        Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete All Slots Modal -->
    <div id="deleteAllSlotsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-[#8B0000] rounded-lg p-6 w-96 mx-4">
            <h3 class="text-xl font-bold text-[#D4AF37] mb-4 text-center">HAPUS ALL SLOT WAKTU</h3>
            <div class="mb-6">
                <p class="text-white text-center">
                    Apakah Anda yakin ingin menghapus semua slot waktu pada tanggal
                    <strong
                        class="text-[#D4AF37]">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d M Y') }}</strong>?
                </p>
            </div>
            <div class="flex gap-4">
                <button type="button" onclick="hideDeleteAllSlotsModal()"
                    class="flex-1 bg-gray-600 text-white py-2 rounded font-semibold">
                    Batal
                </button>
                <button onclick="deleteAllSlots()" class="flex-1 bg-red-600 text-white py-2 rounded font-semibold">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showAddSlotModal() {
                document.getElementById('addSlotModal').classList.remove('hidden');
                document.getElementById('addSlotModal').classList.add('flex');
            }

            function hideAddSlotModal() {
                document.getElementById('addSlotModal').classList.add('hidden');
                document.getElementById('addSlotModal').classList.remove('flex');
            }

            function showDeleteSlotModal() {
                document.getElementById('deleteSlotModal').classList.remove('hidden');
                document.getElementById('deleteSlotModal').classList.add('flex');
            }

            function hideDeleteSlotModal() {
                document.getElementById('deleteSlotModal').classList.add('hidden');
                document.getElementById('deleteSlotModal').classList.remove('flex');
            }

            function showDeleteAllSlotsModal() {
                document.getElementById('deleteAllSlotsModal').classList.remove('hidden');
                document.getElementById('deleteAllSlotsModal').classList.add('flex');
            }

            function hideDeleteAllSlotsModal() {
                document.getElementById('deleteAllSlotsModal').classList.add('hidden');
                document.getElementById('deleteAllSlotsModal').classList.remove('flex');
            }

            function deleteAllSlots() {
                const selectedDate = '{{ $selectedDate }}';

                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('admin.slots.destroy', 'all') }}';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                const dateField = document.createElement('input');
                dateField.type = 'hidden';
                dateField.name = 'date';
                dateField.value = selectedDate;

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                form.appendChild(dateField);

                document.body.appendChild(form);
                form.submit();
            }
        </script>
    @endpush
@endsection
