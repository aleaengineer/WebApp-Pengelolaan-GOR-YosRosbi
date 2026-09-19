<x-guest-layout>
    <div class="text-center lg:text-left">
        <h1 class="text-2xl font-bold text-gray-900" style="font-family:Poppins">Konfirmasi Password</h1>
        <p class="mt-2 text-sm text-gray-600">Area aman — silakan konfirmasi password kamu untuk melanjutkan.</p>
    </div>
    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <x-input-label for="password" :value="__('Password')" class="!font-semibold" />
            <x-text-input id="password" class="block mt-2 w-full !py-3.5 bg-gray-50 border-gray-200" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <x-primary-button class="w-full !py-3.5">
            Konfirmasi
        </x-primary-button>
    </form>
</x-guest-layout>
