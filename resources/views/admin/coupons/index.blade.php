<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl" style="font-family:Poppins"><span class="text-white">Kupon</span> <span class="text-primary-100">Promo %</span></h2></x-slot>
    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-xl mb-4 text-sm">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl mb-4 text-sm">{{ $errors->first() }}</div>@endif

            <div class="flex flex-col sm:flex-row justify-between gap-3 mb-4">
                <div class="text-sm text-gray-600">Hanya <strong>percent</strong>, untuk <strong>per_jam</strong>, tidak stack kuota member, 1x/user.</div>
                <a href="{{ route('admin.coupons.create') }}" class="btn-primary !py-2 text-sm text-center">+ Buat Kupon</a>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-6">
                <div class="card !p-4 text-center"><div class="text-2xl font-bold text-primary-600">{{ $coupons->total() }}</div><div class="text-xs text-gray-500">Total Kupon</div></div>
                <div class="card !p-4 text-center"><div class="text-2xl font-bold text-green-600">{{ $coupons->where('is_active',true)->count() }}</div><div class="text-xs text-gray-500">Aktif</div></div>
                <div class="card !p-4 text-center"><div class="text-2xl font-bold text-gray-900">{{ $coupons->sum('used_count') }}</div><div class="text-xs text-gray-500">Total Dipakai</div></div>
            </div>

            <div class="card !p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-primary-600 text-white">
                            <tr><th class="px-4 py-3 text-left">Kode</th><th class="px-3 py-3">Diskon %</th><th class="px-3 py-3">Max</th><th class="px-3 py-3">Min</th><th class="px-3 py-3">Pakai</th><th class="px-3 py-3">Expired</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($coupons as $c)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono font-bold text-primary-700">{{ $c->code }}</td>
                                <td class="px-3 py-3 text-center font-bold">{{ $c->value }}%</td>
                                <td class="px-3 py-3 text-center">{{ $c->max_discount ? 'Rp'.number_format($c->max_discount,0,',','.') : '-' }}</td>
                                <td class="px-3 py-3 text-center">{{ $c->min_amount ? 'Rp'.number_format($c->min_amount,0,',','.') : '-' }}</td>
                                <td class="px-3 py-3 text-center"><span class="{{ $c->quota && $c->used_count >= $c->quota ? 'text-red-600' : 'text-gray-900' }} font-semibold">{{ $c->used_count }}/{{ $c->quota ?? '∞' }}</span><div class="text-xs text-gray-500">{{ $c->per_user_limit }}x/user</div></td>
                                <td class="px-3 py-3 text-center text-xs">{{ $c->expired_at ? $c->expired_at->format('d/m/Y') : '-' }}<br>@if($c->isExpired())<span class="text-red-600">Expired</span>@endif</td>
                                <td class="px-3 py-3 text-center">
                                    <form method="POST" action="{{ route('admin.coupons.toggle',$c->id) }}">@csrf<span class="px-2 py-1 rounded-full text-xs font-bold {{ $c->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $c->is_active ? 'Aktif' : 'Nonaktif' }}</span><button class="ml-1 text-xs underline">Toggle</button></form>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex gap-1">
                                        <a href="{{ route('admin.coupons.edit',$c->id) }}" class="text-primary-600 font-semibold hover:underline text-xs">Edit</a>
                                        <form method="POST" action="{{ route('admin.coupons.destroy',$c->id) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Hapus</button></form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Belum ada kupon. Buat YOS10.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $coupons->links() }}</div>
            </div>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 border rounded-full text-sm">← Bookings</a>
                <a href="{{ route('admin.laporan') }}" class="px-4 py-2 border rounded-full text-sm">Laporan</a>
            </div>
        </div>
    </div>
</x-app-layout>
