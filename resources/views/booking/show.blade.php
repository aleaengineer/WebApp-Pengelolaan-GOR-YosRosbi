<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl text-white" style="font-family:Poppins">Detail Booking #{{ $booking->id }}</h2></x-slot>
    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <button onclick="if(history.length>1){history.back()}else{window.location.href='{{ route('booking.index') }}'}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-gray-700 bg-white border border-gray-200 px-4 py-2.5 rounded-full shadow-sm hover:bg-gray-50 hover:text-primary-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Kembali
            </button>
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-xl mb-4">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl mb-4">{{ $errors->first() }}</div>@endif
            <div class="card">
                <div class="flex justify-between">
                    <h3 class="font-bold text-lg text-gray-900">{{ $booking->lapangan->nama ?? 'Lapangan Utama' }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        @if(in_array($booking->status,['paid','confirmed','completed'])) bg-green-100 text-green-700
                        @elseif($booking->status=='cancelled') bg-gray-100 text-gray-600
                        @elseif($booking->status=='pending_verification') bg-yellow-100 text-yellow-700
                        @else bg-yellow-50 text-yellow-700 @endif
                    ">{{ strtoupper($booking->status) }}</span>
                </div>
                <div class="mt-4 space-y-2 text-sm">
                    <div><strong>Tanggal:</strong> {{ $booking->tanggal->format('d M Y') }} @if($booking->tanggal_selesai) - {{ $booking->tanggal_selesai->format('d M Y') }} @endif</div>
                    <div><strong>Jam:</strong> {{ $booking->jam_mulai }} - {{ $booking->jam_selesai }} ({{ $booking->durasi_jam }} jam)</div>
                    <div><strong>Jeda Pembersihan:</strong> {{ \Carbon\Carbon::parse($booking->jam_selesai)->addMinutes(30)->format('H:i') }} (30 menit, tidak ditagih, tidak berlaku jika dibatalkan)</div>
                    <div><strong>Kegiatan:</strong> {{ $booking->jenis_kegiatan }} • {{ $booking->tipe_sewa }}</div>
                    <div><strong>Total:</strong> @if($booking->discount_amount) <span class="line-through text-gray-400">Rp{{ number_format($booking->total_harga_before_discount,0,',','.') }}</span> <span class="text-green-600">-Rp{{ number_format($booking->discount_amount,0,',','.') }}</span> @endif <span class="font-bold text-primary-600">Rp{{ number_format($booking->total_harga,0,',','.') }}</span> @if($booking->coupon) <span class="ml-2 px-2 py-0.5 bg-primary-50 border border-primary-200 rounded-full text-xs font-mono text-primary-700">{{ $booking->coupon->code }} {{ $booking->coupon->value }}%</span>@endif</div>
                    <div><strong>Metode:</strong> {{ $booking->payment->metode ?? '-' }} • {{ $booking->payment->status ?? '-' }}</div>
                    @if($booking->catatan)<div><strong>Catatan:</strong> {{ $booking->catatan }}</div>@endif
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    @if(in_array($booking->status,['pending','pending_verification']) && $booking->payment->metode=='transfer')
                        <form method="POST" action="{{ route('booking.upload',$booking->id) }}" enctype="multipart/form-data" class="flex gap-2 items-center">
                            @csrf
                            <input type="file" name="bukti" accept="image/*" required class="text-sm">
                            <button class="bg-primary-600 text-white px-4 py-2 rounded-full text-sm font-semibold">Upload Bukti</button>
                        </form>
                    @endif
                    @if($booking->payment->bukti_transfer_path)
                        <a href="{{ asset('storage/'.$booking->payment->bukti_transfer_path) }}" target="_blank" class="text-primary-600 text-sm underline">Lihat Bukti</a>
                    @endif
                    @if(in_array($booking->status,['pending','pending_verification','paid']))
                        <form method="POST" action="{{ route('booking.cancel',$booking->id) }}" onsubmit="return confirm('Batalkan booking? Jeda 30 menit akan dibebaskan.')">
                            @csrf
                            <button class="border border-red-200 text-red-600 px-4 py-2 rounded-full text-sm font-semibold hover:bg-red-50">Batalkan Booking</button>
                        </form>
                    @endif
                </div>

                <div class="mt-6 p-4 bg-gray-50 rounded-xl text-xs text-gray-600">
                    Invoice #{{ $booking->id }} • Dibuat {{ $booking->created_at->format('d M Y H:i') }} • Jika butuh bantuan hubungi WA admin.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
