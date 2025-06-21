@extends('layouts.app')

@section('title', 'Member')

@section('content')
    <div class="pt-24 bg-[#fffbe9] min-h-screen">
        <div class="text-center py-6 bg-[#e5b645] border-b border-black">
            <h1 class="text-4xl font-extrabold text-black uppercase tracking-widest">Reservation</h1>
        </div>

        <div class="flex justify-center items-center gap-8 py-16 px-4 flex-wrap">
            <div class="bg-[#7a0c0c] border-4 border-[#d4af37] text-center p-8 shadow-xl w-72 rounded-md">
                <h2 class="text-[#d4af37] font-bold text-xl mb-6">DAFTAR<br>RESERVASI</h2>
                <a href="{{ route('member.reservations.create') }}"
                    class="inline-block bg-[#7a0c0c] border border-[#d4af37] text-[#d4af37] font-semibold py-2 px-8 rounded-full shadow-md hover:shadow-lg hover:translate-y-[-2px] transition">
                    MASUK
                </a>
            </div>

            <div class="bg-[#7a0c0c] border-4 border-[#d4af37] text-center p-8 shadow-xl w-72 rounded-md">
                <h2 class="text-[#d4af37] font-bold text-xl mb-6">LIHAT<br>RESERVASI</h2>
                <a href="{{ route('member.reservations.index') }}"
                    class="inline-block bg-[#7a0c0c] border border-[#d4af37] text-[#d4af37] font-semibold py-2 px-8 rounded-full shadow-md hover:shadow-lg hover:translate-y-[-2px] transition">
                    MASUK
                </a>
            </div>
        </div>
    </div>
@endsection
