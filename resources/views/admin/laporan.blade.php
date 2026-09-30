<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl" style="font-family:Poppins"><span class="text-white">Laporan</span> <span class="text-primary-100">Pendapatan</span></h2></x-slot>
    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <button onclick="if(history.length>1){history.back()}else{window.location.href='{{ route('admin.bookings.index') }}'}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 px-4 py-2.5 rounded-full shadow-sm hover:bg-gray-50 hover:text-primary-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </button>
            <form method="GET" class="card flex flex-wrap gap-3 items-end">
                <div><label class="text-xs font-semibold">Dari</label><input type="date" name="dari" value="{{ request('dari') }}" class="mt-1 border-gray-300 rounded-lg"></div>
                <div><label class="text-xs font-semibold">Sampai</label><input type="date" name="sampai" value="{{ request('sampai') }}" class="mt-1 border-gray-300 rounded-lg"></div>
                <div><label class="text-xs font-semibold">Jenis</label><select name="jenis" class="mt-1 border-gray-300 rounded-lg"><option value="">Semua</option><option value="badminton" {{ request('jenis')=='badminton'?'selected':'' }}>Badminton</option><option value="voly" {{ request('jenis')=='voly'?'selected':'' }}>Voly</option><option value="basket" {{ request('jenis')=='basket'?'selected':'' }}>Basket</option><option value="event_lain" {{ request('jenis')=='event_lain'?'selected':'' }}>Event</option></select></div>
                <button class="bg-primary-600 text-white px-6 py-2 rounded-full font-semibold">Filter</button>
                <a href="{{ route('admin.laporan') }}" class="px-6 py-2 rounded-full font-semibold bg-white border border-gray-300 text-gray-600 hover:bg-gray-100">Reset</a>
            </form>
            <div class="grid sm:grid-cols-4 gap-4 mt-4">
                <div class="card"><div class="text-sm text-gray-500">Total Pendapatan</div><div class="text-2xl font-bold text-primary-600">Rp {{ number_format($total,0,',','.') }}</div></div>
                <div class="card"><div class="text-sm text-gray-500">Total Diskon</div><div class="text-2xl font-bold text-green-600">Rp {{ number_format($bookings->sum('discount_amount'),0,',','.') }}</div></div>
                <div class="card"><div class="text-sm text-gray-500">Total Jam Sewa</div><div class="text-2xl font-bold text-gray-900">{{ $totalJam }} jam</div><div class="text-xs text-gray-500">+ {{ $bookings->count()*0.5 }} jam jeda</div></div>
                <div class="card"><div class="text-sm text-gray-500">Jumlah Booking</div><div class="text-2xl font-bold text-gray-900">{{ $bookings->count() }}</div></div>
            </div>
            <div class="card mt-4">
                <div class="text-sm font-semibold text-gray-700 mb-2">Grafik Pendapatan & Jam Sewa</div>
                <div style="height: 320px; position: relative;">
                    <canvas id="laporanChart" data-laporan='@json($perTanggal)'></canvas>
                </div>
            </div>
            <div class="card !p-0 overflow-hidden mt-4">
                <table class="w-full text-sm">
                    <thead class="bg-primary-600 text-white"><tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3">Jam</th><th class="px-4 py-3">Kegiatan</th><th class="px-4 py-3">Harga Awal</th><th class="px-4 py-3">Diskon</th><th class="px-4 py-3">Bayar</th><th class="px-4 py-3">Kupon</th><th class="px-4 py-3">Status</th></tr></thead>
                    <tbody>
                        @forelse($bookings as $b)
                        <tr class="border-b"><td class="px-4 py-2">{{ $b->tanggal->format('d/m/Y') }}</td><td class="px-4 py-2">{{ $b->jam_mulai }}-{{ $b->jam_selesai }}</td><td class="px-4 py-2">{{ $b->jenis_kegiatan }}</td><td class="px-4 py-2">{{ $b->total_harga_before_discount ? 'Rp'.number_format($b->total_harga_before_discount,0,',','.') : '-' }}</td><td class="px-4 py-2 text-green-600">{{ $b->discount_amount ? '-Rp'.number_format($b->discount_amount,0,',','.') : '-' }}</td><td class="px-4 py-2 font-bold">Rp{{ number_format($b->total_harga,0,',','.') }}</td><td class="px-4 py-2 font-mono text-xs">{{ $b->coupon_id ? \App\Models\Coupon::find($b->coupon_id)->code ?? '-' : '-' }}</td><td class="px-4 py-2">{{ $b->status }}</td></tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
