<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl" style="font-family:Poppins"><span class="text-white">Booking</span> <span class="text-primary-100">Saya</span></h2></x-slot>
    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <button onclick="if(history.length>1){history.back()}else{window.location.href='{{ route('home') }}'}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 px-4 py-2.5 rounded-full shadow-sm hover:bg-gray-50 hover:text-primary-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </button>
            <div class="card !p-4 sm:!p-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
                    <h3 class="font-bold text-gray-900 text-center sm:text-left">Riwayat Booking</h3>
                    <a href="{{ route('booking.create') }}" class="btn-primary !py-2.5 text-sm text-center justify-center">+ Booking Baru</a>
                </div>
                @forelse($bookings as $b)
                    <div class="border rounded-xl p-4 mb-3 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 bg-white">
                        <div class="flex-1 min-w-0 text-center sm:text-left">
                            <div class="font-semibold text-gray-900 text-sm sm:text-base">{{ $b->tanggal->format('d M Y') }} • {{ $b->jam_mulai }}-{{ $b->jam_selesai }} • {{ ucfirst($b->jenis_kegiatan) }}</div>
                            <div class="text-xs text-gray-600 mt-1">{{ $b->tipe_sewa }} • {{ $b->status }} • Rp {{ number_format($b->total_harga,0,',','.') }} • {{ $b->payment->metode ?? '-' }}</div>
                            <div class="text-xs text-primary-600 mt-1">Jeda hingga {{ \Carbon\Carbon::parse($b->jam_selesai)->addMinutes(30)->format('H:i') }} (tidak ditagih)</div>
                        </div>
                        <a href="{{ route('booking.show',$b->id) }}" class="text-primary-600 font-semibold text-sm hover:underline bg-primary-50 sm:bg-transparent px-4 py-2 rounded-full sm:px-0 sm:py-0 text-center">Detail →</a>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">Belum ada booking. <a href="{{ route('booking.create') }}" class="text-primary-600 font-semibold">Booking sekarang</a></p>
                @endforelse
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
