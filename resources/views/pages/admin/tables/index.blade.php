@extends('layouts.admin')

@section('title', 'Manajemen Meja')

@section('content')
    <div class="min-h-screen bg-[#1a1a1a] text-white">
        <!-- Header -->
        <div class="bg-[#8B0000] py-4">
            <div class="container mx-auto px-4 flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-[#D4AF37] text-2xl mr-4">←</a>
                    <h1 class="text-2xl font-bold text-[#D4AF37]">MANAJEMEN MEJA</h1>
                </div>
                <div class="flex gap-2">
                    <button onclick="showAddTableModal()" class="bg-[#D4AF37] text-[#1a1a1a] px-4 py-2 rounded font-semibold">
                        Tambah Meja
                    </button>
                    <button onclick="showEditTableModal()" class="bg-orange-500 text-white px-4 py-2 rounded font-semibold">
                        Edit Meja
                    </button>
                    <button onclick="showDeleteTableModal()" class="bg-red-600 text-white px-4 py-2 rounded font-semibold">
                        Hapus Meja
                    </button>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Tables Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @forelse($tables as $table)
                    <div class="bg-[#F5E6A3] text-[#1a1a1a] rounded-lg p-4 text-center">
                        <h3 class="font-bold text-lg">Meja Nomor {{ $table->number }}</h3>
                        <p class="text-sm mb-2">
                            <span class="font-semibold {{ $table->is_available ? 'text-green-600' : 'text-red-600' }}">
                                {{ $table->is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                            </span>
                        </p>
                        <p class="text-sm">{{ $table->seat_count }} Kursi</p>
                    </div>
                @empty
                    <div class="col-span-full bg-[#2a2a2a] rounded-lg p-8 text-center">
                        <p class="text-gray-400">Belum ada meja yang terdaftar</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add Table Modal -->
    <div id="addTableModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-[#8B0000] rounded-lg p-6 w-96 mx-4">
            <h3 class="text-xl font-bold text-[#D4AF37] mb-4 text-center">TAMBAH MEJA</h3>
            <form method="POST" action="{{ route('admin.tables.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-[#D4AF37] font-semibold mb-2">Pilih Nomor Meja</label>
                    <input type="number" name="number" min="1" required
                        class="w-full bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                </div>
                <div class="mb-6">
                    <label class="block text-[#D4AF37] font-semibold mb-2">Jumlah Kursi</label>
                    <input type="number" name="seat_count" min="1" required
                        class="w-full bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                </div>
                <div class="flex gap-4">
                    <button type="button" onclick="hideAddTableModal()"
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

    <!-- Edit Table Modal -->
    <div id="editTableModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-[#8B0000] rounded-lg p-6 w-96 mx-4">
            <h3 class="text-xl font-bold text-[#D4AF37] mb-4 text-center">EDIT MEJA</h3>
            <form id="editTableForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-[#D4AF37] font-semibold mb-2">Pilih Nomor Meja</label>
                    <select name="table_id" id="editTableSelect" required
                        class="w-full bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                        <option value="">Pilih Meja</option>
                        @foreach ($tables as $table)
                            <option value="{{ $table->id }}" data-seats="{{ $table->seat_count }}"
                                data-status="{{ $table->is_available }}">
                                Meja {{ $table->number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-[#D4AF37] font-semibold mb-2">Jumlah Kursi</label>
                    <input type="number" name="seat_count" id="editSeatCount" min="1" required
                        class="w-full bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                </div>
                <div class="mb-6">
                    <label class="block text-[#D4AF37] font-semibold mb-2">Status</label>
                    <select name="is_available" id="editStatus" required
                        class="w-full bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                        <option value="1">Tersedia</option>
                        <option value="0">Tidak Tersedia</option>
                    </select>
                </div>
                <div class="flex gap-4">
                    <button type="button" onclick="hideEditTableModal()"
                        class="flex-1 bg-gray-600 text-white py-2 rounded font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 bg-[#D4AF37] text-[#1a1a1a] py-2 rounded font-semibold">
                        Edit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Table Modal -->
    <div id="deleteTableModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-[#8B0000] rounded-lg p-6 w-96 mx-4">
            <h3 class="text-xl font-bold text-[#D4AF37] mb-4 text-center">HAPUS MEJA</h3>
            <form id="deleteTableForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="mb-6">
                    <label class="block text-[#D4AF37] font-semibold mb-2">Pilih Nomor Meja</label>
                    <select name="table_id" required
                        class="w-full bg-[#2a2a2a] text-white border border-[#D4AF37] rounded px-3 py-2">
                        <option value="">Pilih Meja</option>
                        @foreach ($tables as $table)
                            <option value="{{ $table->id }}">Meja {{ $table->number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-4">
                    <button type="button" onclick="hideDeleteTableModal()"
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

    @push('scripts')
        <script>
            function showAddTableModal() {
                document.getElementById('addTableModal').classList.remove('hidden');
                document.getElementById('addTableModal').classList.add('flex');
            }

            function hideAddTableModal() {
                document.getElementById('addTableModal').classList.add('hidden');
                document.getElementById('addTableModal').classList.remove('flex');
            }

            function showEditTableModal() {
                document.getElementById('editTableModal').classList.remove('hidden');
                document.getElementById('editTableModal').classList.add('flex');
            }

            function hideEditTableModal() {
                document.getElementById('editTableModal').classList.add('hidden');
                document.getElementById('editTableModal').classList.remove('flex');
            }

            function showDeleteTableModal() {
                document.getElementById('deleteTableModal').classList.remove('hidden');
                document.getElementById('deleteTableModal').classList.add('flex');
            }

            function hideDeleteTableModal() {
                document.getElementById('deleteTableModal').classList.add('hidden');
                document.getElementById('deleteTableModal').classList.remove('flex');
            }

            // Handle edit table selection
            document.getElementById('editTableSelect').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    document.getElementById('editSeatCount').value = selectedOption.dataset.seats;
                    document.getElementById('editStatus').value = selectedOption.dataset.status;
                    document.getElementById('editTableForm').action = `/admin/tables/${selectedOption.value}`;
                }
            });

            // Handle delete table selection
            document.querySelector('#deleteTableForm select[name="table_id"]').addEventListener('change', function() {
                if (this.value) {
                    document.getElementById('deleteTableForm').action = `/admin/tables/${this.value}`;
                }
            });
        </script>
    @endpush
@endsection
