<x-guest-layout>
    <div class="text-center lg:text-left">
        <h1 class="text-3xl font-bold text-gray-900" style="font-family:Poppins">Buat Akun<span class="text-primary-600">.</span></h1>
        <p class="mt-2 text-sm text-gray-600">Daftar untuk booking lapangan & langganan member. Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700">Masuk</a></p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" class="!font-semibold !text-gray-900" />
            <div class="relative mt-2">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <x-text-input id="name" class="block w-full !pl-11 !py-3.5 bg-gray-50 focus:bg-white border-gray-200" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Budi Santoso" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="!font-semibold !text-gray-900" />
            <div class="relative mt-2">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </span>
                <x-text-input id="email" class="block w-full !pl-11 !py-3.5 bg-gray-50 focus:bg-white border-gray-200" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="email" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="!font-semibold !text-gray-900" />
            <div class="relative mt-2" x-data="{ show: false }">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <x-text-input id="password" class="block w-full !pl-11 !py-3.5 bg-gray-50 focus:bg-white border-gray-200"
                                type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" x-bind:type="show ? 'text':'password'" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="!font-semibold !text-gray-900" />
            <x-text-input id="password_confirmation" class="block w-full !py-3.5 bg-gray-50 focus:bg-white border-gray-200 mt-2"
                            type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-50 rounded-xl p-3 border border-gray-100">
            <span class="w-6 h-6 bg-primary-100 rounded-lg flex items-center justify-center text-primary-600">✓</span>
            Dengan daftar, kamu menyetujui syarat & jeda pembersihan 30 menit otomatis.
        </div>

        <x-primary-button class="w-full !py-3.5">
            Daftar & Booking Sekarang
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </x-primary-button>

        <p class="text-center text-sm text-gray-600">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700">Masuk di sini</a>
        </p>
    </form>
</x-guest-layout>
