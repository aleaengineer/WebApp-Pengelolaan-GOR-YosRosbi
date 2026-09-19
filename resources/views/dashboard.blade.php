<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-white leading-tight" style="font-family:Poppins">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Greeting + Member Status -->
            <div class="card !p-5 sm:!p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <div class="text-xl sm:text-2xl font-bold text-gray-900" style="font-family:Poppins">Halo, {{ auth()->user()->name }}! <span class="text-primary-600 text-sm font-mono bg-primary-50 px-2 py-1 rounded-full">{{ auth()->user()->role }}</span></div>
                        <p class="text-sm text-gray-600 mt-1">GOR Yos Rosbi • 08:00-00:00 • Jeda 30 menit otomatis</p>
                    </div>
                    @if($paket)
                        <div class="bg-gradient-to-br from-primary-600 to-primary-700 text-white rounded-2xl p-4 sm:p-5 min-w-[280px] shadow-lg">
                            <div class="flex justify-between items-start">
                                <div><div class="text-xs opacity-90">Paket Member</div><div class="font-bold text-lg">{{ $paket->nama }}</div><div class="text-xs opacity-80">{{ $paket->kuota_jam }} jam / {{ $paket->durasi_hari }} hari</div></div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statusMember==='active' ? 'bg-green-400 text-white' : 'bg-red-400 text-white' }}">{{ $statusMember==='active' ? 'Aktif' : 'Expired' }}</span>
                            </div>
                            <div class="mt-3">
                                <div class="flex justify-between text-xs mb-1"><span>Sisa Kuota</span><span class="font-bold">{{ max(0,$sisaKuota) }} / {{ $paket->kuota_jam }} jam</span></div>
                                <div class="w-full bg-white/20 rounded-full h-2"><div class="bg-white h-2 rounded-full transition" style="width: {{ $paket->kuota_jam ? max(0, ($sisaKuota/$paket->kuota_jam)*100) : 0 }}%"></div></div>
                                <div class="text-xs mt-2 opacity-80">Expired: {{ auth()->user()->member_expired_at ? auth()->user()->member_expired_at->format('d M Y') : '-' }} • {{ auth()->user()->member_expired_at ? auth()->user()->member_expired_at->diffForHumans() : '' }}</div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white border-2 border-dashed border-primary-200 rounded-2xl p-4 sm:p-5 min-w-[280px] text-center">
                            <div class="text-sm font-bold text-gray-900">Belum Member</div>
                            <div class="text-xs text-gray-500">Hemat hingga 30% + kuota gratis</div>
                            <a href="{{ route('home') }}#harga" class="mt-3 inline-block bg-primary-600 text-white text-xs font-bold px-4 py-2 rounded-full hover:bg-primary-700">Lihat Paket Member</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistik -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-6">
                <div class="card !p-4 text-center">
                    <div class="text-2xl font-black text-gray-900">{{ $totalBooking }}</div>
                    <div class="text-xs text-gray-500 font-medium">Total Booking</div>
                </div>
                <div class="card !p-4 text-center">
                    <div class="text-2xl font-black text-primary-600">{{ $totalJam }}<span class="text-sm font-normal"> jam</span></div>
                    <div class="text-xs text-gray-500 font-medium">Jam Terpakai</div>
                    <div class="text-[11px] text-gray-400">+{{ $totalJam*0.5 }} jam jeda</div>
                </div>
                <div class="card !p-4 text-center">
                    <div class="text-lg sm:text-xl font-black text-gray-900">Rp{{ number_format($totalPengeluaran,0,',','.') }}</div>
                    <div class="text-xs text-gray-500 font-medium">Total Bayar</div>
                </div>
                <div class="card !p-4 text-center bg-green-50 border-green-200">
                    <div class="text-lg sm:text-xl font-black text-green-600">-Rp{{ number_format($totalDiskon,0,',','.') }}</div>
                    <div class="text-xs text-gray-600 font-medium">Total Diskon</div>
                    <div class="text-[11px] text-gray-500">Kupon percent</div>
                </div>
            </div>

            <!-- Upcoming + Quick Actions -->
            <div class="grid lg:grid-cols-3 gap-4 sm:gap-6 mt-4 sm:mt-6">
                <div class="lg:col-span-2">
                    @if($upcoming)
                        <div class="card !p-5 border-l-4 border-primary-600">
                            <div class="flex justify-between items-start gap-3">
                                <div>
                                    <div class="text-xs font-bold text-primary-600 tracking-widest">JADWAL TERDEKAT</div>
                                    <div class="mt-1 font-bold text-gray-900 text-lg">{{ $upcoming->tanggal->format('d M Y') }} • {{ $upcoming->jam_mulai }}-{{ $upcoming->jam_selesai }}</div>
                                    <div class="text-sm text-gray-600">{{ ucfirst($upcoming->jenis_kegiatan) }} • {{ $upcoming->tipe_sewa }} • <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $upcoming->status==='paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $upcoming->status }}</span></div>
                                    <div class="text-xs text-primary-600 mt-1">Jeda hingga {{ \Carbon\Carbon::parse($upcoming->jam_selesai)->addMinutes(30)->format('H:i') }}</div>
                                </div>
                                <div class="hidden sm:block text-right"><div class="text-xs text-gray-400">Dalam</div><div class="font-bold text-primary-600">{{ \Carbon\Carbon::parse($upcoming->tanggal->format('Y-m-d').' '.$upcoming->jam_mulai)->diffForHumans() }}</div></div>
                            </div>
                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('booking.show',$upcoming->id) }}" class="flex-1 sm:flex-none bg-primary-600 text-white text-sm font-bold px-5 py-2.5 rounded-full text-center hover:bg-primary-700">Lihat Detail</a>
                                <a href="{{ route('jadwal') }}" class="px-5 py-2.5 border border-gray-200 rounded-full text-sm font-semibold hover:bg-gray-50 text-center">Jadwal</a>
                            </div>
                        </div>
                    @else
                        <div class="card !p-6 text-center">
                            <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto text-xl">📅</div>
                            <div class="mt-3 font-bold text-gray-900">Tidak ada jadwal mendatang</div>
                            <div class="text-sm text-gray-500">Booking sekarang, cek ketersediaan realtime</div>
                            <a href="{{ route('booking.create') }}" class="mt-4 inline-block btn-primary !py-2.5 text-sm">+ Booking Baru</a>
                        </div>
                    @endif

                    <div class="grid sm:grid-cols-3 gap-3 sm:gap-4 mt-4">
                        <a href="{{ route('booking.create') }}" class="card !p-4 hover:border-primary-300 hover:shadow-lg transition group text-center sm:text-left">
                            <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center mx-auto sm:mx-0 group-hover:bg-primary-600 group-hover:text-white transition">+</div>
                            <div class="mt-2 font-bold text-gray-900 text-sm">Booking Baru</div>
                            <div class="text-xs text-gray-500">Cek jeda 30 otomatis</div>
                        </a>
                        <a href="{{ route('booking.index') }}" class="card !p-4 hover:border-primary-300 hover:shadow-lg transition group text-center sm:text-left">
                            <div class="w-10 h-10 bg-gray-50 rounded-xl flex items-center justify-center mx-auto sm:mx-0">📋</div>
                            <div class="mt-2 font-bold text-gray-900 text-sm">Riwayat</div>
                            <div class="text-xs text-gray-500">{{ $totalBooking }} booking</div>
                        </a>
                        <a href="{{ route('jadwal') }}" class="card !p-4 hover:border-primary-300 hover:shadow-lg transition group text-center sm:text-left">
                            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mx-auto sm:mx-0">🗓️</div>
                            <div class="mt-2 font-bold text-gray-900 text-sm">Jadwal Hari Ini</div>
                            <div class="text-xs text-gray-500">Realtime</div>
                        </a>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Promo -->
                    <div class="card !p-0 overflow-hidden border-2 border-dashed border-primary-200">
                        <div class="bg-gradient-to-r from-primary-600 to-primary-700 p-4 text-white">
                            <div class="text-xs font-bold tracking-widest opacity-90">🎟️ PROMO AKTIF</div>
                            <div class="font-black text-lg">Hemat Per Jam</div>
                        </div>
                        <div class="p-4 space-y-3">
                            @forelse($promos as $promo)
                                <div class="flex justify-between items-center bg-gray-50 rounded-xl p-3">
                                    <div><div class="font-mono font-black text-primary-700 tracking-widest">{{ $promo->code }}</div><div class="text-xs text-gray-600">{{ $promo->value }}% off • max Rp{{ number_format($promo->max_discount ?? 0,0,',','.') }} • 1x/user</div></div>
                                    <button onclick="navigator.clipboard.writeText('{{ $promo->code }}'); alert('Kode {{ $promo->code }} disalin!')" class="bg-white border border-primary-200 text-primary-700 px-3 py-1.5 rounded-full text-xs font-bold hover:bg-primary-50">Salin</button>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500 text-center py-2">Belum ada promo. Coba <code class="bg-gray-100 px-2 py-1 rounded">YOS10</code></div>
                            @endforelse
                            <a href="{{ route('booking.create') }}" class="block text-center text-xs font-bold text-primary-600 hover:underline">Pakai sekarang →</a>
                        </div>
                    </div>

                    <!-- Recent -->
                    <div class="card !p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="font-bold text-gray-900 text-sm">Riwayat Terbaru</h4>
                            <a href="{{ route('booking.index') }}" class="text-xs font-bold text-primary-600 hover:underline">Lihat Semua →</a>
                        </div>
                        @forelse($recent as $b)
                            <div class="flex justify-between items-center py-2.5 border-b border-gray-100 last:border-0 gap-2">
                                <div class="min-w-0"><div class="text-sm font-semibold text-gray-900 truncate">{{ $b->tanggal->format('d/m') }} {{ $b->jam_mulai }}-{{ $b->jam_selesai }}</div><div class="text-xs text-gray-500">{{ $b->jenis_kegiatan }} • {{ $b->status }} @if($b->discount_amount)<span class="text-green-600">-{{ $b->discount_amount }}</span>@endif</div></div>
                                <a href="{{ route('booking.show',$b->id) }}" class="text-xs font-bold text-primary-600 shrink-0">Detail ›</a>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500 text-center py-4">Belum ada riwayat</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
