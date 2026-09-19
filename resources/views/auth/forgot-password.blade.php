<x-guest-layout>
    <div class="text-center lg:text-left">
        <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto lg:mx-0 mb-4">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7h3a5 5 0 015 5 5 5 0 01-5 5h-3m-6 0a5 5 0 01-5-5 5 5 0 015-5h3m-3 8l3-3m0 0l3-3m-3 3V4"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900" style="font-family:Poppins">Lupa Password?</h1>
        <p class="mt-2 text-sm text-gray-600">Tidak masalah. Masukkan email kamu, kami akan kirim link reset password.</p>
    </div>

    <x-auth-session-status class="mb-4 mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Email')" class="!font-semibold !text-gray-900" />
            <div class="relative mt-2">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </span>
                <x-text-input id="email" class="block w-full !pl-11 !py-3.5 bg-gray-50 focus:bg-white border-gray-200" type="email" name="email" :value="old('email')" required autofocus placeholder="email" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full !py-3.5">
            Kirim Link Reset
        </x-primary-button>

        <a href="{{ route('login') }}" class="block text-center text-sm font-semibold text-gray-600 hover:text-primary-600">← Kembali ke Login</a>
    </form>
</x-guest-layout>
