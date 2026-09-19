<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl" style="font-family:Poppins"><span class="text-white">Admin • Kelola</span> <span class="text-primary-100">Booking</span></h2></x-slot>
    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 rounded-full text-sm {{ !request('status') ? 'bg-primary-600 text-white' : 'bg-white border' }}">Semua</a>
                @foreach(['pending','pending_verification','paid','confirmed','cancelled'] as $s)
                <a href="?status={{ $s }}" class="px-4 py-2 rounded-full text-sm {{ request('status')==$s ? 'bg-primary-600 text-white' : 'bg-white border' }}">{{ $s }}</a>
                @endforeach
                <a href="{{ route('admin.coupons.index') }}" class="bg-primary-50 border border-primary-200 text-primary-700 px-4 py-2 rounded-full text-sm font-bold">🎟️ Kupon</a>
                <a href="{{ route('admin.laporan') }}" class="ml-auto bg-white border px-4 py-2 rounded-full text-sm">Laporan</a>
                <a href="{{ route('admin.settings') }}" class="bg-white border px-4 py-2 rounded-full text-sm">Settings</a>
                <a href="{{ route('admin.blokir.index') }}" class="bg-white border px-4 py-2 rounded-full text-sm">Blokir Jadwal</a>
            </div>
            <div class="card !p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-600 text-white">
                            <tr><th class="px-4 py-3 text-left">ID</th><th class="px-4 py-3">Tanggal & Jam</th><th class="px-4 py-3">User</th><th class="px-4 py-3">Kegiatan</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $b)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">#{{ $b->id }}</td>
                                <td class="px-4 py-3">{{ $b->tanggal->format('d/m/Y') }} {{ $b->jam_mulai }}-{{ $b->jam_selesai }}<div class="text-xs text-primary-600">+30m jeda → {{ \Carbon\Carbon::parse($b->jam_selesai)->addMinutes(30)->format('H:i') }}</div></td>
                                <td class="px-4 py-3">{{ $b->user->name }}<div class="text-xs text-gray-500">{{ $b->user->email }}</div></td>
                                <td class="px-4 py-3">{{ $b->jenis_kegiatan }} ({{ $b->tipe_sewa }})</td>
                                <td class="px-4 py-3">Rp {{ number_format($b->total_harga,0,',','.') }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-bold bg-yellow-50 text-yellow-700">{{ $b->status }}</span><div class="text-xs text-gray-500">{{ $b->payment->metode ?? '' }}</div></td>
                                <td class="px-4 py-3"><a href="{{ route('admin.bookings.show',$b->id) }}" class="text-primary-600 font-semibold hover:underline">Detail</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada booking</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $bookings->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
