<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl" style="font-family:Poppins"><span class="text-white">Blokir</span> <span class="text-primary-100">Jadwal</span></h2></x-slot>
    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card mb-6">
                <h3 class="font-bold mb-3">Tambah Blokir (Maintenance)</h3>
                <form method="POST" action="{{ route('admin.blokir.store') }}" class="grid sm:grid-cols-2 gap-3">
                    @csrf
                    <input type="hidden" name="lapangan_id" value="{{ \App\Models\Lapangan::first()->id ?? 1 }}">
                    <div><label class="text-xs font-semibold">Tanggal</label><input type="date" name="tanggal" required class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div><label class="text-xs font-semibold">Alasan</label><input type="text" name="alasan" required placeholder="Maintenance lantai" class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div><label class="text-xs font-semibold">Jam Mulai</label><input type="time" name="jam_mulai" required class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div><label class="text-xs font-semibold">Jam Selesai</label><input type="time" name="jam_selesai" required class="mt-1 block w-full border-gray-300 rounded-lg"></div>
                    <div class="sm:col-span-2"><button class="bg-primary-600 text-white px-6 py-2 rounded-full font-semibold">Tambah Blokir</button></div>
                </form>
            </div>
            <div class="card !p-0 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-primary-600 text-white"><tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3">Jam</th><th class="px-4 py-3">Alasan</th><th class="px-4 py-3">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($blokirs as $b)
                        <tr class="border-b"><td class="px-4 py-3">{{ $b->tanggal->format('d/m/Y') }}</td><td class="px-4 py-3">{{ $b->jam_mulai }}-{{ $b->jam_selesai }}</td><td class="px-4 py-3">{{ $b->alasan }}</td><td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.blokir.destroy',$b->id) }}" onsubmit="return confirm('Hapus blokir?')">@csrf @method('DELETE')<button class="text-red-600 text-xs font-bold">Hapus</button></form>
                        </td></tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada blokir</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">{{ $blokirs->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
