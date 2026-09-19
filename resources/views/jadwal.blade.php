<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl leading-tight" style="font-family:Poppins"><span class="text-white">Jadwal</span> <span class="text-primary-100">Ketersediaan</span></h2>
    </x-slot>
    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <button onclick="if(history.length>1){history.back()}else{window.location.href='{{ route('home') }}'}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 px-4 py-2.5 rounded-full shadow-sm hover:bg-gray-50 hover:text-primary-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </button>
            <div class="card !p-4 sm:!p-6">
                <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <div class="flex-1 sm:flex-none"><label class="text-xs font-semibold text-gray-600">Tanggal</label><input type="date" name="tanggal" value="{{ $tanggal }}" class="mt-1 block w-full sm:w-auto border-gray-300 rounded-xl shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2.5"></div>
                    <div class="flex gap-2 sm:gap-3">
                        <button class="flex-1 sm:flex-none bg-primary-600 text-white px-6 py-2.5 rounded-full font-semibold hover:bg-primary-700 text-center justify-center">Lihat</button>
                        <a href="{{ route('booking.create') }}?tanggal={{ $tanggal }}" class="flex-1 sm:flex-none btn-primary !py-2.5 text-center justify-center inline-flex items-center">Booking</a>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="flex flex-wrap gap-2 text-xs mb-4 justify-center sm:justify-start">
                        <span class="px-3 py-1.5 bg-green-50 border border-green-200 rounded-full text-green-700 font-medium">Tersedia</span>
                        <span class="px-3 py-1.5 bg-red-50 border border-red-200 rounded-full text-red-700 font-medium">Terbooking</span>
                        <span class="px-3 py-1.5 bg-gray-100 border border-gray-200 rounded-full text-gray-600 font-medium">Jeda 30 menit</span>
                        <span class="px-3 py-1.5 bg-yellow-50 border border-yellow-200 rounded-full text-yellow-700 font-medium">Blokir Admin</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3">
                        @foreach($fixedSlots as $slot)
                            @php
                                $booked = $bookings->first(function($b) use ($slot){ return $b->jam_mulai == $slot['start'] . ':00' || $b->jam_mulai == $slot['start']; });
                                $isBlokir = $blokirs->first(function($b) use ($slot){ return $b->jam_mulai <= $slot['start'].':00' && $b->jam_selesai > $slot['start'].':00'; });
                            @endphp
                            <div class="border rounded-xl p-4 flex flex-col sm:flex-row gap-3 sm:justify-between sm:items-center {{ $booked ? 'bg-red-50 border-red-200' : ($isBlokir ? 'bg-yellow-50 border-yellow-200' : 'bg-green-50 border-green-200') }}">
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-gray-900 text-center sm:text-left">{{ $slot['start'] }} - {{ $slot['end'] }}</div>
                                    <div class="text-xs text-gray-600 text-center sm:text-left">
                                        @if($booked) {{ $booked->jenis_kegiatan }} • {{ $booked->status }}
                                        @elseif($isBlokir) Blokir: {{ $isBlokir->alasan }}
                                        @else Slot Rekomendasi (60m + 30m jeda)
                                        @endif
                                    </div>
                                    @if(!$booked && !$isBlokir)
                                        <div class="text-xs text-primary-600 mt-1 text-center sm:text-left">→ {{ \Carbon\Carbon::parse($slot['end'])->addMinutes(30)->format('H:i') }} jeda</div>
                                    @endif
                                </div>
                                @if(!$booked && !$isBlokir)
                                    <a href="{{ route('booking.create') }}?tanggal={{ $tanggal }}&jam_mulai={{ $slot['start'] }}&jam_selesai={{ $slot['end'] }}" class="bg-primary-600 text-white px-5 py-2.5 rounded-full text-xs font-bold hover:bg-primary-700 text-center w-full sm:w-auto">Booking</a>
                                @else
                                    <span class="text-xs font-bold {{ $booked ? 'text-red-700' : 'text-yellow-700' }} text-center sm:text-right bg-white/60 sm:bg-transparent px-3 py-1.5 rounded-full sm:px-0 sm:py-0 w-full sm:w-auto">{{ $booked ? 'Penuh' : 'Blokir' }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 p-4 bg-primary-50 border border-primary-200 rounded-xl text-sm text-primary-800 text-center sm:text-left leading-relaxed">
                        <strong>Info Jeda 30 menit:</strong> Jika booking 08:00-09:00, maka 09:00-09:30 otomatis terblokir untuk pembersihan. Custom jam tetap dicek sistem. Jeda tidak berlaku untuk booking yang dibatalkan.
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="font-bold text-gray-900 mb-3 text-center sm:text-left">Booking Hari Ini ({{ $tanggal }})</h4>
                    @forelse($bookings as $b)
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 py-3 border-b text-sm">
                            <span class="text-center sm:text-left">{{ $b->jam_mulai }}-{{ $b->jam_selesai }} • {{ $b->jenis_kegiatan }} • <span class="badge-pending">{{ $b->status }}</span></span>
                            <span class="text-gray-500 text-xs sm:text-sm text-center sm:text-right">+30 menit jeda hingga {{ \Carbon\Carbon::parse($b->jam_selesai)->addMinutes(30)->format('H:i') }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center sm:text-left py-4 bg-gray-50 rounded-xl">Belum ada booking aktif hari ini. Semua slot tersedia!</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
