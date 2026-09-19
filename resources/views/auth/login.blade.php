<x-guest-layout>
    <div class="text-center lg:text-left">
        <h1 class="text-3xl font-bold text-gray-900" style="font-family:Poppins">Selamat Datang<span class="text-primary-600">.</span></h1>
        <p class="mt-2 text-sm text-gray-600">Masuk untuk booking lapangan. Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700 underline decoration-primary-200 underline-offset-4">Daftar gratis</a></p>
    </div>

    <x-auth-session-status class="mb-4 mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="!text-gray-900 !font-semibold" />
            <div class="relative mt-2">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                </span>
                <x-text-input id="email" class="block w-full !pl-11 !py-3.5 bg-gray-50 focus:bg-white border-gray-200" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="email" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="!text-gray-900 !font-semibold" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-primary-600 hover:text-primary-700" href="{{ route('password.request') }}">Lupa password?</a>
                @endif
            </div>
            <div class="relative mt-2" x-data="{ show: false }">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <x-text-input id="password" class="block w-full !pl-11 !pr-11 !py-3.5 bg-gray-50 focus:bg-white border-gray-200"
                                type="password"
                                name="password"
                                required autocomplete="current-password" placeholder="••••••••"
                                x-bind:type="show ? 'text' : 'password'" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="flex items-center gap-2 cursor-pointer group">
                <input id="remember_me" type="checkbox" class="rounded-lg border-gray-300 text-primary-600 focus:ring-primary-500 w-4 h-4" name="remember">
                <span class="text-sm text-gray-700 group-hover:text-gray-900">Ingat saya</span>
            </label>
            <span class="text-xs text-gray-400">08:00-00:00</span>
        </div>

        <x-primary-button class="w-full !py-3.5 text-base">
            Masuk ke GOR Yos Rosbi
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </x-primary-button>

        <div class="relative flex items-center gap-4 py-2">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-xs text-gray-400 font-medium">ATAU</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <p class="text-center text-sm text-gray-600">
            Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700">Daftar sekarang — gratis</a>
        </p>
    </form>

    <!-- Demo Credentials - Merah Putih Card -->
    <div class="mt-8 rounded-2xl border border-primary-100 bg-gradient-to-br from-primary-50 to-white p-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="w-7 h-7 bg-primary-600 rounded-lg flex items-center justify-center text-white text-xs">⚡</span>
            <span class="text-sm font-bold text-gray-900">Akun Demo (klik untuk isi)</span>
            <span class="ml-auto text-[10px] bg-green-100 text-green-700 px-2 py-1 rounded-full font-bold">SIAP PAKAI</span>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <button type="button" onclick="fillDemo('admin@goryosrosbi.test','password')" class="group border border-gray-200 bg-white hover:border-primary-300 hover:bg-primary-50 rounded-xl p-3 text-left transition">
                <div class="text-xs font-bold text-gray-900 group-hover:text-primary-700">Admin</div>
                <div class="text-[11px] text-gray-500">Kelola booking</div>
                <div class="mt-1 text-[11px] font-mono text-gray-600">admin@...</div>
            </button>
            <button type="button" onclick="fillDemo('operator@goryosrosbi.test','password')" class="group border border-gray-200 bg-white hover:border-primary-300 hover:bg-primary-50 rounded-xl p-3 text-left transition">
                <div class="text-xs font-bold text-gray-900 group-hover:text-primary-700">Operator</div>
                <div class="text-[11px] text-gray-500">Check-in</div>
                <div class="mt-1 text-[11px] font-mono text-gray-600">operator@...</div>
            </button>
            <button type="button" onclick="fillDemo('customer@test.com','password')" class="group border-2 border-primary-200 bg-white hover:border-primary-400 hover:bg-primary-50 rounded-xl p-3 text-left transition">
                <div class="text-xs font-bold text-primary-700">Customer</div>
                <div class="text-[11px] text-gray-500">Booking</div>
                <div class="mt-1 text-[11px] font-mono text-gray-600">customer@...</div>
            </button>
        </div>
        <div class="mt-3 flex items-center gap-2 text-[11px] text-gray-500">
            <span>Password semua:</span> <code class="bg-gray-900 text-white px-2 py-1 rounded-lg font-mono">password</code>
            <span class="ml-auto">Jeda 30mnt • 08:00-00:00</span>
        </div>
    </div>

    <script>
        function fillDemo(email, pwd){
            document.getElementById('email').value = email;
            document.getElementById('password').value = pwd;
            document.getElementById('email').focus();
        }
    </script>
</x-guest-layout>
