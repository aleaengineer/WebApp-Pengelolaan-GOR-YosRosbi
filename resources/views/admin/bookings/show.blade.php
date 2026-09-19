<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl text-white" style="font-family:Poppins">Detail Booking #{{ $booking->id }}</h2></x-slot>
    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-xl mb-4">{{ session('success') }}</div>@endif
            <div class="card">
                <div class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><strong>User:</strong> {{ $booking->user->name }} ({{ $booking->user->email }} / {{ $booking->user->phone }})</div>
                    <div><strong>Status:</strong> <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">{{ $booking->status }}</span></div>
                    <div><strong>Tanggal:</strong> {{ $booking->tanggal->format('d M Y') }} {{ $booking->jam_mulai }}-{{ $booking->jam_selesai }}</div>
                    <div><strong>Jeda:</strong> hingga {{ \Carbon\Carbon::parse($booking->jam_selesai)->addMinutes(30)->format('H:i') }} (30m)</div>
                    <div><strong>Kegiatan:</strong> {{ $booking->jenis_kegiatan }} • {{ $booking->tipe_sewa }}</div>
                    <div><strong>Total:</strong> Rp {{ number_format($booking->total_harga,0,',','.') }}</div>
                    <div><strong>Metode:</strong> {{ $booking->payment->metode ?? '-' }} • {{ $booking->payment->status ?? '-' }}</div>
                    <div><strong>Catatan:</strong> {{ $booking->catatan ?? '-' }}</div>
                </div>
                @if($booking->payment && $booking->payment->bukti_transfer_path)
                    <div class="mt-4"><strong>Bukti Transfer:</strong> <a href="{{ asset('storage/'.$booking->payment->bukti_transfer_path) }}" target="_blank" class="text-primary-600 underline">Lihat Gambar</a><br><img src="{{ asset('storage/'.$booking->payment->bukti_transfer_path) }}" class="mt-2 max-w-xs rounded-xl border"></div>
                @endif
                <form method="POST" action="{{ route('admin.bookings.status',$booking->id) }}" class="mt-6 flex gap-2">
                    @csrf
                    <select name="status" class="border-gray-300 rounded-lg text-sm">
                        <option value="paid">Paid</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled (bebaskan jeda)</option>
                    </select>
                    <button class="bg-primary-600 text-white px-6 py-2 rounded-full text-sm font-semibold">Update Status</button>
                    <a href="{{ route('admin.bookings.index') }}" class="px-6 py-2 border rounded-full text-sm">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
