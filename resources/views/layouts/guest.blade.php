<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'GOR Yos Rosbi') }} - Masuk</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#DC2626">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 overflow-x-hidden">
        <div class="min-h-screen flex">
            <!-- Left - Branding Merah Putih -->
            <div class="hidden lg:flex lg:w-[52%] bg-gradient-to-br from-primary-900 via-primary-700 to-primary-600 relative overflow-hidden flex-col justify-between p-10 text-white">
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                </div>
                <div class="absolute -top-24 -right-24 w-[520px] h-[520px] bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-32 -left-32 w-[600px] h-[600px] bg-black/10 rounded-full blur-3xl"></div>

                <div class="relative">
                    <a href="/" class="inline-flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-primary-600 font-bold text-lg shadow-lg">YR</div>
                        <span class="font-bold text-xl tracking-tight" style="font-family:Poppins">GOR <span class="text-white">YOS ROSBI</span></span>
                        <span class="ml-2 text-[10px] tracking-[0.2em] bg-white/20 px-2 py-1 rounded-full font-semibold">OFFICIAL</span>
                    </a>
                </div>

                <div class="relative">
                    <div class="inline-flex items-center gap-2 bg-white/15 backdrop-blur px-3 py-1.5 rounded-full text-xs font-semibold">
                        <span class="w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
                        Booking Online 08:00 - 00:00 • Jeda 30 menit
                    </div>
                    <h1 class="mt-6 text-4xl font-bold leading-tight" style="font-family:Poppins">Lapangan<br>Serbaguna<br><span class="text-primary-100">Kebanggaan</span></h1>
                    <p class="mt-4 text-primary-50/90 leading-relaxed max-w-md">Badminton • Voly • Basket • Event Indoor. Satu lapangan, semua kebutuhan. Booking cepat, pembayaran fleksibel, PWA installable.</p>

                    <div class="mt-8 grid grid-cols-3 gap-3 max-w-md">
                        <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                            <div class="text-2xl font-bold">16<span class="text-sm font-normal text-primary-100"> jam</span></div>
                            <div class="text-xs text-primary-100">08:00-00:00</div>
                        </div>
                        <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/10">
                            <div class="text-2xl font-bold">30<span class="text-sm font-normal text-primary-100"> mnt</span></div>
                            <div class="text-xs text-primary-100">Jeda bersih</div>
                        </div>
                        <div class="bg-white rounded-2xl p-4 text-primary-700">
                            <div class="text-2xl font-bold">3</div>
                            <div class="text-xs text-primary-600">Metode bayar</div>
                        </div>
                    </div>

                    <div class="mt-6 bg-white rounded-2xl p-4 shadow-xl max-w-md">
                        <div class="flex items-center gap-3">
                            <img src="https://i.pravatar.cc/100?img=15" class="w-10 h-10 rounded-full">
                            <div>
                                <div class="text-sm font-bold text-gray-900">Budi, Member Bulanan</div>
                                <div class="text-xs text-gray-500">Badminton • 3x seminggu</div>
                            </div>
                            <span class="ml-auto text-yellow-400">★★★★★</span>
                        </div>
                        <p class="mt-3 text-sm text-gray-600 italic">“Bookingnya gampang, jeda 30 menit bikin lapangan selalu bersih. Member hemat banget!”</p>
                    </div>
                </div>

                <div class="relative text-xs text-primary-200 flex items-center gap-4">
                    <span>© 2026 GOR Yos Rosbi</span>
                    <span class="w-1 h-1 bg-white/40 rounded-full"></span>
                    <span>Merah Putih • PWA Ready</span>
                </div>
            </div>

            <!-- Right - Form -->
            <div class="flex-1 flex flex-col justify-center px-4 sm:px-8 lg:px-16 bg-white relative py-6 sm:py-8">
                <!-- Mobile header - simetris -->
                <div class="lg:hidden flex items-center justify-between gap-4 py-5 border-b border-gray-100 mb-6 -mx-4 px-4 sm:mx-0 sm:px-0">
                    <a href="/" class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 bg-primary-600 rounded-xl flex items-center justify-center text-white font-bold shrink-0">YR</div>
                        <span class="font-bold text-gray-900 text-[15px] tracking-tight truncate">GOR <span class="text-primary-600">YOS ROSBI</span></span>
                    </a>
                    <a href="/" class="shrink-0 inline-flex items-center gap-1 text-sm font-semibold text-primary-600 bg-primary-50 px-3.5 py-2 rounded-full hover:bg-primary-100 transition">← Beranda</a>
                </div>

                <div class="hidden lg:flex justify-end mb-6">
                    <a href="/" class="text-sm font-medium text-gray-500 hover:text-primary-600 flex items-center gap-1">← Kembali ke Beranda</a>
                </div>

                <div class="w-full max-w-md mx-auto">
                    {{ $slot }}
                </div>

                <div class="flex items-center justify-center gap-4 sm:gap-6 mt-8 text-xs text-gray-400 px-4">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 bg-green-500 rounded-full shrink-0"></span> Server Online</span>
                    <span class="hidden sm:inline">•</span>
                    <span>PWA Installable</span>
                    <span>•</span>
                    <span>08:00-00:00</span>
                </div>
            </div>
        </div>
    </body>
</html>
