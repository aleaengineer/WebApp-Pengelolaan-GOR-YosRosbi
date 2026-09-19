<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl text-white" style="font-family:Poppins">{{ isset($coupon) ? 'Edit' : 'Buat' }} Kupon</h2></x-slot>
    <div class="py-6 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ isset($coupon) ? route('admin.coupons.update',$coupon->id) : route('admin.coupons.store') }}" class="card space-y-4">
                @csrf @if(isset($coupon)) @method('PUT') @endif
                @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-sm">{{ $errors->first() }}</div>@endif

                <div class="grid sm:grid-cols-2 gap-4">
                    <div><label class="text-sm font-semibold">Kode (UPPER) *</label><input type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" placeholder="YOS10" required class="mt-1 block w-full border-gray-300 rounded-xl font-mono uppercase tracking-widest"></div>
                    <div><label class="text-sm font-semibold">Nama *</label><input type="text" name="name" value="{{ old('name', $coupon->name ?? '') }}" placeholder="Diskon 10% Per Jam" required class="mt-1 block w-full border-gray-300 rounded-xl"></div>
                    <div><label class="text-sm font-semibold">Diskon % (1-100) *</label><input type="number" name="value" value="{{ old('value', $coupon->value ?? 10) }}" min="1" max="100" required class="mt-1 block w-full border-gray-300 rounded-xl"></div>
                    <div><label class="text-sm font-semibold">Max Diskon (Rp)</label><input type="number" name="max_discount" value="{{ old('max_discount', $coupon->max_discount ?? '') }}" placeholder="15000" class="mt-1 block w-full border-gray-300 rounded-xl"></div>
                    <div><label class="text-sm font-semibold">Min Belanja (Rp)</label><input type="number" name="min_amount" value="{{ old('min_amount', $coupon->min_amount ?? 0) }}" class="mt-1 block w-full border-gray-300 rounded-xl"></div>
                    <div><label class="text-sm font-semibold">Kuota Total</label><input type="number" name="quota" value="{{ old('quota', $coupon->quota ?? 100) }}" placeholder="100" class="mt-1 block w-full border-gray-300 rounded-xl"></div>
                    <div><label class="text-sm font-semibold">Per User Limit *</label><input type="number" name="per_user_limit" value="{{ old('per_user_limit', $coupon->per_user_limit ?? 1) }}" min="1" required class="mt-1 block w-full border-gray-300 rounded-xl"></div>
                    <div><label class="text-sm font-semibold">Expired At</label><input type="datetime-local" name="expired_at" value="{{ old('expired_at', isset($coupon->expired_at) ? $coupon->expired_at->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full border-gray-300 rounded-xl"></div>
                </div>

                <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }} class="rounded text-primary-600"><span class="text-sm font-semibold">Aktif</span><span class="ml-auto text-xs text-gray-500">Hanya per_jam, percent, tidak stack member</span></div>

                <div class="flex gap-2">
                    <button class="btn-primary">{{ isset($coupon) ? 'Update' : 'Buat' }} Kupon</button>
                    <a href="{{ route('admin.coupons.index') }}" class="px-6 py-3 border rounded-full font-semibold text-sm">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
