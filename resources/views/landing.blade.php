<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GOR Yos Rosbi - Lapangan Serbaguna Badminton Voly Basket & Event</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#DC2626">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 overflow-x-hidden">
    @include('layouts.navigation')

    <!-- Hero - mobile simetris -->
    <section class="relative overflow-hidden bg-white">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-900 via-primary-700 to-primary-600"></div>
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1600&q=80')] bg-cover bg-center opacity-20 mix-blend-overlay"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-10 items-center">
                <div class="text-white text-center lg:text-left">
                    <div class="inline-flex items-center justify-center gap-2 bg-white/15 backdrop-blur px-3 py-1.5 rounded-full text-[11px] sm:text-xs font-semibold tracking-wide mx-auto lg:mx-0">🏐 BADMINTON • VOLY • BASKET • EVENT INDOOR</div>
                    <h1 class="mt-4 text-[30px] sm:text-4xl lg:text-5xl font-bold leading-tight" style="font-family:Poppins">GOR <span class="text-white">YOS ROSBI</span><br><span class="text-primary-100 text-xl sm:text-2xl font-semibold">Lapangan Serbaguna Kebanggaan</span></h1>
                    <p class="mt-4 text-primary-50 text-[15px] sm:text-lg leading-relaxed max-w-xl mx-auto lg:mx-0 px-2 sm:px-0">1 Lapangan multifungsi terbaik. Booking per jam atau harian event, jeda 30 menit otomatis, pembayaran transfer / QRIS / cash, paket member hemat.</p>
                    <div class="mt-4 inline-flex items-center gap-2 bg-yellow-300 text-gray-900 px-4 py-2 rounded-full text-sm font-bold shadow">🎟️ Promo: <code class="bg-white px-2 py-0.5 rounded">YOS10</code> 10% off per jam!</div>
                    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                        <a href="{{ route('booking.create') }}" class="w-full sm:w-auto bg-white text-primary-600 font-bold px-8 py-3.5 rounded-full shadow-xl hover:bg-gray-50 transition text-center justify-center inline-flex items-center">Booking Sekarang →</a>
                        <a href="{{ route('jadwal') }}" class="w-full sm:w-auto border-2 border-white text-white font-semibold px-8 py-3.5 rounded-full hover:bg-white/10 transition text-center justify-center inline-flex items-center">Cek Jadwal Hari Ini</a>
                    </div>
                    <div class="mt-6 flex flex-wrap items-center justify-center lg:justify-start gap-3 sm:gap-6 text-sm text-primary-100">
                        <span class="inline-flex items-center gap-1.5">🕗 08:00 - 00:00</span>
                        <span class="inline-flex items-center gap-1.5">🧹 Jeda 30 menit</span>
                        <span class="inline-flex items-center gap-1.5">💳 3 Metode Bayar</span>
                    </div>
                </div>
                <div class="relative w-full">
                    <div class="bg-white rounded-3xl shadow-2xl p-5 sm:p-6 border border-gray-100">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-4">
                            <h3 class="font-bold text-gray-900 text-center sm:text-left">Ketersediaan Hari Ini</h3>
                            <span class="text-xs bg-primary-50 text-primary-700 px-3 py-1.5 rounded-full font-semibold text-center mx-auto sm:mx-0">{{ $tanggal }}</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-2">
                            @foreach($fixedSlots as $slot)
                                @php
                                    $isBooked = $bookings->contains(function($b) use ($slot){ return $b->jam_mulai == $slot['start'] . ':00' || $b->jam_mulai == $slot['start']; });
                                    $isBlokir = $blokirs->contains(function($b) use ($slot){ return $b->jam_mulai <= $slot['start'].':00' && $b->jam_selesai > $slot['start'].':00'; });
                                @endphp
                                <div class="rounded-xl p-2.5 sm:p-3 text-center text-sm font-semibold border {{ $isBooked ? 'bg-red-50 border-red-200 text-red-700' : ($isBlokir ? 'bg-gray-100 border-gray-200 text-gray-500' : 'bg-green-50 border-green-200 text-green-700') }}">
                                    {{ $slot['start'] }}-{{ $slot['end'] }}
                                    <div class="text-[11px] sm:text-xs font-normal mt-0.5">{{ $isBooked ? 'Terbooking' : ($isBlokir ? 'Blokir' : 'Tersedia') }}</div>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-xs text-gray-500 text-center sm:text-left">* Slot fix: 60 menit sewa + 30 menit jeda. Bisa custom jam, sistem cek otomatis.</p>
                        <a href="{{ route('jadwal') }}" class="mt-4 block text-center text-sm font-semibold text-primary-600 hover:text-primary-700 bg-primary-50 sm:bg-transparent py-2.5 sm:py-0 rounded-xl">Lihat kalender lengkap →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Info Strip - simetris mobile -->
    <section class="bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
            <div class="card flex gap-4 !p-4 sm:!p-5 items-start"><div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center text-xl shrink-0">🏟️</div><div class="min-w-0"><h4 class="font-bold text-gray-900 text-[15px]">1 Lapangan Multifungsi</h4><p class="text-sm text-gray-600 leading-relaxed">Badminton, Voly, Basket, Event indoor - lantai & net siap setting</p></div></div>
            <div class="card flex gap-4 !p-4 sm:!p-5 items-start"><div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center text-xl shrink-0">🕗</div><div class="min-w-0"><h4 class="font-bold text-gray-900 text-[15px]">08:00 - 00:00</h4><p class="text-sm text-gray-600 leading-relaxed">16 jam operasional, jeda pembersihan 30 menit tiap transisi</p></div></div>
            <div class="card flex gap-4 !p-4 sm:!p-5 items-start"><div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center text-xl shrink-0">💳</div><div class="min-w-0"><h4 class="font-bold text-gray-900 text-[15px]">Bayar Fleksibel</h4><p class="text-sm text-gray-600 leading-relaxed">Transfer, Midtrans QRIS/VA, atau Cash di tempat</p></div></div>
        </div>
    </section>

    <!-- Harga - simetris mobile -->
    <section id="harga" class="py-10 sm:py-14 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8 px-4">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900" style="font-family:Poppins">Harga <span class="text-primary-600">Transparan</span></h2>
                <p class="text-gray-600 mt-2 text-sm sm:text-base">Per jam untuk olahraga, harian untuk event besar. Member lebih hemat.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
                <div class="card border-t-4 border-primary-600">
                    <div class="text-sm font-semibold text-primary-600">SEWA PER JAM</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">Rp {{ number_format($hargaPerJam->harga ?? 50000,0,',','.') }}<span class="text-sm font-normal text-gray-500">/jam</span></div>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                        <li>✓ Pilih cabang: Badminton / Voly / Basket</li>
                        <li>✓ Durasi minimal 1 jam</li>
                        <li>✓ Jeda 30 menit tidak ditagih</li>
                        <li>✓ Harga member: Rp {{ number_format($hargaPerJam->harga_member ?? 40000,0,',','.') }}/jam</li>
                    </ul>
                    <a href="{{ route('booking.create') }}?tipe=per_jam" class="btn-primary block text-center mt-6 !py-2.5">Booking Per Jam</a>
                </div>
                <div class="card border-t-4 border-gray-900 bg-gray-900 text-white !border-gray-900">
                    <div class="text-sm font-semibold text-primary-300">SEWA HARIAN EVENT</div>
                    <div class="mt-2 text-3xl font-bold">Rp {{ number_format($hargaHarian->harga ?? 1500000,0,',','.') }}<span class="text-sm font-normal text-gray-400">/hari</span></div>
                    <ul class="mt-4 space-y-2 text-sm text-gray-300">
                        <li>✓ Full day 08:00-00:00</li>
                        <li>✓ Untuk turnamen, hajatan, gathering</li>
                        <li>✓ Jeda 30 menit ke hari berikutnya</li>
                        <li>✓ Include persiapan lapangan</li>
                    </ul>
                    <a href="{{ route('booking.create') }}?tipe=harian" class="block text-center mt-6 bg-white text-gray-900 font-bold rounded-full py-2.5 hover:bg-gray-100">Booking Harian</a>
                </div>
                <div class="card border-t-4 border-primary-600">
                    <div class="text-sm font-semibold text-primary-600">PAKET MEMBER</div>
                    <div class="space-y-3 mt-4">
                        @forelse($pakets as $paket)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                            <div><div class="font-semibold text-gray-900 text-sm">{{ $paket->nama }}</div><div class="text-xs text-gray-500">{{ $paket->kuota_jam }} jam / {{ $paket->durasi_hari }} hari</div></div>
                            <div class="font-bold text-primary-600 text-sm">Rp {{ number_format($paket->harga,0,',','.') }}</div>
                        </div>
                        @empty
                        <div class="p-3 bg-gray-50 rounded-xl text-sm text-gray-600">Paket Hemat 10x - 10 jam / 30 hari - Rp 350.000<br>Paket Bulanan Unlimited - 30 hari - Rp 800.000</div>
                        @endforelse
                    </div>
                    <a href="{{ route('register') }}" class="btn-outline-primary block text-center mt-6 !py-2.5">Jadi Member</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Jeda - mobile simetris -->
    <section class="bg-white py-10 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h3 class="font-bold text-gray-900 text-lg sm:text-xl">Kenapa Ada Jeda 30 Menit?</h3>
            <p class="text-gray-600 mt-3 text-sm leading-relaxed px-2 sm:px-0">Setiap transisi booking (apapun caburnya) sistem otomatis kunci 30 menit untuk pembersihan & setting ulang. Contoh: <span class="font-semibold text-primary-600">08:00-09:00 Badminton</span> → <span class="bg-primary-50 text-primary-700 px-2 py-1 rounded-lg inline-block my-1">09:00-09:30 Jeda</span> → <span class="font-semibold text-primary-600">09:30-10:30 Voly</span>. Tidak ditagih, tidak berlaku jika dibatalkan.</p>
            <div class="mt-6 flex flex-col sm:flex-row justify-center gap-2 sm:gap-2 items-stretch sm:items-center max-w-md mx-auto">
                <span class="px-4 py-2.5 bg-green-50 border border-green-200 rounded-full text-sm font-semibold text-green-700 text-center">08:00-09:00 Sewa</span>
                <span class="hidden sm:inline text-gray-400">→</span>
                <span class="px-4 py-2.5 bg-primary-50 border border-primary-200 rounded-full text-sm font-semibold text-primary-700 text-center">09:00-09:30 Jeda</span>
                <span class="hidden sm:inline text-gray-400">→</span>
                <span class="px-4 py-2.5 bg-green-50 border border-green-200 rounded-full text-sm font-semibold text-green-700 text-center">09:30-10:30 Sewa</span>
            </div>
        </div>
    </section>

    <!-- CTA - mobile simetris -->
    <section class="bg-primary-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 flex flex-col sm:flex-row justify-between items-center gap-5 text-center sm:text-left">
            <div class="text-white"><h3 class="font-bold text-lg sm:text-xl">Siap main di GOR Yos Rosbi?</h3><p class="text-primary-100 text-sm mt-1">Booking online 24 jam, konfirmasi cepat.</p></div>
            <a href="{{ route('booking.create') }}" class="w-full sm:w-auto bg-white text-primary-600 font-bold px-8 py-3.5 rounded-full shadow text-center justify-center inline-flex items-center">Booking Sekarang</a>
        </div>
    </section>

    <footer class="bg-primary-900 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between gap-4 text-center sm:text-left">
            <div><div class="font-bold">GOR YOS ROSBI</div><div class="text-sm text-primary-200 mt-1">Jl. Contoh No.123 • WA: 08xx-xxxx-xxxx • 08:00-00:00</div></div>
            <div class="text-sm text-primary-300 sm:text-right">© 2026 GOR Yos Rosbi. Merah Putih • PWA Ready • Jeda 30 menit.</div>
        </div>
    </footer>

    <!-- PWA Install Banner -->
    <div id="pwa-banner" class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 p-4 hidden">
        <div class="flex gap-3">
            <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center text-white font-bold">YR</div>
            <div class="flex-1"><div class="font-bold text-gray-900 text-sm">Install GOR Yos Rosbi</div><div class="text-xs text-gray-600">Akses lebih cepat dari HP, offline support.</div></div>
            <button onclick="document.getElementById('pwa-banner').classList.add('hidden')" class="text-gray-400">✕</button>
        </div>
        <div class="mt-3 flex gap-2">
            <button id="pwa-install-btn" class="flex-1 bg-primary-600 text-white text-sm font-semibold rounded-full py-2">Install</button>
            <button onclick="document.getElementById('pwa-banner').classList.add('hidden')" class="px-4 text-sm text-gray-600">Nanti</button>
        </div>
    </div>
    <script>
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e)=>{
            e.preventDefault(); deferredPrompt=e;
            document.getElementById('pwa-banner').classList.remove('hidden');
        });
        document.getElementById('pwa-install-btn')?.addEventListener('click', async ()=>{
            if(deferredPrompt){ deferredPrompt.prompt(); const r=await deferredPrompt.userChoice; deferredPrompt=null; document.getElementById('pwa-banner').classList.add('hidden');}
        });
        if('serviceWorker' in navigator){ window.addEventListener('load', ()=>{ navigator.serviceWorker.register('/sw.js').catch(()=>{}); });}
    </script>
</body>
</html>
