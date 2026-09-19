<x-guest-layout>
    <div class="text-center lg:text-left">
        <h1 class="text-2xl font-bold text-gray-900" style="font-family:Poppins">Verifikasi Email</h1>
        <p class="mt-2 text-sm text-gray-600">Terima kasih sudah daftar! Cek email kamu untuk link verifikasi. Tidak dapat email?</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-6 font-medium text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl p-3">
            Link verifikasi baru telah dikirim ke email kamu.
        </div>
    @endif

    <div class="mt-8 flex flex-col gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full !py-3.5">
                Kirim Ulang Verifikasi
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-sm font-semibold text-gray-600 hover:text-primary-600 py-3 border border-gray-200 rounded-full hover:bg-gray-50">
                Log Out
            </button>
        </form>
    </div>
</x-guest-layout>
