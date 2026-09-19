<x-guest-layout>
    <div class="text-center lg:text-left">
        <h1 class="text-2xl font-bold text-gray-900" style="font-family:Poppins">Atur Password Baru</h1>
        <p class="mt-2 text-sm text-gray-600">Buat password yang kuat untuk akun GOR Yos Rosbi kamu.</p>
    </div>
    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <x-input-label for="email" :value="__('Email')" class="!font-semibold" />
            <x-text-input id="email" class="block mt-2 w-full !py-3.5 bg-gray-50 border-gray-200" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" placeholder="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password" :value="__('Password Baru')" class="!font-semibold" />
            <x-text-input id="password" class="block mt-2 w-full !py-3.5 bg-gray-50 border-gray-200" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="!font-semibold" />
            <x-text-input id="password_confirmation" class="block mt-2 w-full !py-3.5 bg-gray-50 border-gray-200" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        <x-primary-button class="w-full !py-3.5">
            Reset Password
        </x-primary-button>
    </form>
</x-guest-layout>
