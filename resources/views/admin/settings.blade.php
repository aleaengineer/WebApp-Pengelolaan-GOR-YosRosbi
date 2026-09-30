<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl" style="font-family:Poppins"><span class="text-white">Pengaturan</span> <span class="text-primary-100">GOR</span></h2></x-slot>
    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-xl mb-4">{{ session('success') }}</div>@endif
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="card space-y-4">
                @csrf
                <h3 class="font-bold text-primary-700 text-sm tracking-widest">HARGA PAKET MEMBER</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach($pakets as $paket)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="font-bold text-gray-900 text-sm">{{ $paket->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $paket->kuota_jam }} jam / {{ $paket->durasi_hari }} hari • {{ $paket->deskripsi }}</div>
                            <label class="block mt-2 text-sm font-semibold">Harga (Rp)</label>
                            <input type="number" name="paket_harga_{{$paket->id}}" value="{{ $paket->harga }}" class="mt-1 block w-full border-gray-300 rounded-lg">
                        </div>
                    @endforeach
                </div>
                <h3 class="font-bold text-primary-700 text-sm tracking-widest">UMUM</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div><label class="text-sm font-semibold">Jam Operasional Mulai</label><input type="time" name="jam_operasional_mulai" value="{{ $settings['jam_operasional_mulai'] ?? '08:00' }}" class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div><label class="text-sm font-semibold">Jam Operasional Selesai</label><input type="time" name="jam_operasional_selesai" value="{{ $settings['jam_operasional_selesai'] ?? '00:00' }}" class="mt-1 block w-full border-gray-300 rounded-lg"><div class="text-xs text-gray-500">00:00 = tengah malam</div></div>
                    <div><label class="text-sm font-semibold">Buffer Pembersihan (menit)</label><input type="number" name="buffer_menit" value="{{ $settings['buffer_menit'] ?? 30 }}" class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div><label class="text-sm font-semibold">Buffer Harian (menit)</label><input type="number" name="buffer_harian_menit" value="{{ $settings['buffer_harian_menit'] ?? 30 }}" class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div><label class="text-sm font-semibold">Harga Per Jam</label><input type="number" name="harga_per_jam" value="{{ $settings['harga_per_jam'] ?? 50000 }}" class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div><label class="text-sm font-semibold">Harga Harian</label><input type="number" name="harga_harian" value="{{ $settings['harga_harian'] ?? 1500000 }}" class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div class="sm:col-span-2"><label class="text-sm font-semibold">Rekening</label><input type="text" name="rekening" value="{{ $settings['rekening'] ?? 'BCA 1234567890 a.n. GOR Yos Rosbi' }}" class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold">QRIS</label>
                        @if($settings['qris'] ?? false)
                            <div class="flex items-center gap-4 mt-2">
                                <img src="{{ str_starts_with($settings['qris'], 'http') ? $settings['qris'] : asset('storage/'.$settings['qris']) }}" alt="QRIS saat ini" class="w-28 h-28 object-contain bg-white border border-gray-200 rounded-lg p-1">
                                <label class="text-xs text-red-600 flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" name="hapus_qris" value="1" class="rounded border-gray-300 text-red-600"> Hapus QRIS ini
                                </label>
                            </div>
                        @endif
                        <input type="file" name="qris_image" accept=".jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:px-4 file:py-2 file:rounded-full file:border-0 file:bg-primary-50 file:text-primary-700 file:font-semibold hover:file:bg-primary-100">
                        <div class="text-xs text-gray-500 mt-1">Gambar QRIS (jpg/png/webp, maks 2MB) yang ditampilkan di form booking & detail booking metode QRIS/Midtrans. Kosongkan jika belum ada.</div>
                    </div>
                    <div class="sm:col-span-2"><label class="text-sm font-semibold">Kontak WA</label><input type="text" name="kontak_wa" value="{{ $settings['kontak_wa'] ?? '62812xxxxxxx' }}" class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div class="sm:col-span-2"><label class="text-sm font-semibold">Nama GOR</label><input type="text" name="nama_gor" value="{{ $settings['nama_gor'] ?? 'GOR Yos Rosbi' }}" class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button class="btn-primary">Simpan Pengaturan</button>
                    <a href="{{ route('admin.bookings.index') }}" class="px-6 py-3 border border-gray-300 rounded-full text-sm font-semibold text-center bg-white hover:bg-gray-50 transition">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>