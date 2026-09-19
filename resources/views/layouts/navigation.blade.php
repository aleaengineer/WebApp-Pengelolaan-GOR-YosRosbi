<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div class="w-9 h-9 bg-primary-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">YR</div>
                        <span class="font-bold text-gray-900 text-lg tracking-tight">GOR <span class="text-primary-600">YOS ROSBI</span></span>
                    </a>
                </div>
                <div class="hidden space-x-1 sm:-my-px sm:ms-8 sm:flex items-center">
                    <a href="{{ route('home') }}" class="px-3 py-2 rounded-full text-sm font-medium {{ request()->routeIs('home') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:text-primary-600 hover:bg-gray-50' }}">Beranda</a>
                    <a href="{{ route('jadwal') }}" class="px-3 py-2 rounded-full text-sm font-medium {{ request()->routeIs('jadwal') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:text-primary-600 hover:bg-gray-50' }}">Jadwal</a>
                    <a href="{{ route('home') }}#harga" class="px-3 py-2 rounded-full text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">Harga</a>
                    @auth
                    <a href="{{ route('booking.index') }}" class="px-3 py-2 rounded-full text-sm font-medium {{ request()->routeIs('booking.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-700 hover:text-primary-600 hover:bg-gray-50' }}">Booking Saya</a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.bookings.index') }}" class="px-3 py-2 rounded-full text-sm font-medium bg-primary-600 text-white hover:bg-primary-700">Admin</a>
                    @endif
                    @endauth
                </div>
            </div>
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                @guest
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-primary-600 px-4 py-2">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary text-sm !px-6 !py-2">Daftar</a>
                @else
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-full text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>
                            <span class="ml-1 text-xs px-2 py-0.5 rounded-full {{ Auth::user()->isAdmin() ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-600'}}">{{ Auth::user()->role }}</span>
                            <svg class="fill-current h-4 w-4 ml-1" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('dashboard')">{{ __('Dashboard') }}</x-dropdown-link>
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @endguest
            </div>
            <div class="flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-gray-500 hover:text-primary-600 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-gray-100 shadow-lg">
        <div class="px-4 py-4 space-y-1.5">
            <a href="{{ route('home') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium {{ request()->routeIs('home') ? 'bg-primary-600 text-white shadow' : 'text-gray-700 bg-gray-50 hover:bg-gray-100' }}"><span>Beranda</span><span>›</span></a>
            <a href="{{ route('jadwal') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium {{ request()->routeIs('jadwal') ? 'bg-primary-600 text-white shadow' : 'text-gray-700 bg-gray-50 hover:bg-gray-100' }}"><span>Jadwal</span><span>›</span></a>
            <a href="{{ route('home') }}#harga" class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100"><span>Harga</span><span>›</span></a>
            @auth
            <a href="{{ route('booking.index') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium {{ request()->routeIs('booking.*') ? 'bg-primary-600 text-white shadow' : 'text-gray-700 bg-gray-50 hover:bg-gray-100' }}"><span>Booking Saya</span><span>›</span></a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.bookings.index') }}" class="flex items-center justify-center px-4 py-3 rounded-xl text-sm font-bold bg-primary-600 text-white shadow">Admin Panel</a>
            @endif
            <div class="pt-4 mt-4 border-t border-gray-100 flex items-center justify-between px-2">
                <div><div class="text-sm font-bold text-gray-900">{{ Auth::user()->name }}</div><div class="text-xs text-gray-500">{{ Auth::user()->email }}</div></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-sm font-semibold text-primary-600 px-3 py-1.5 border border-primary-200 rounded-full">Keluar</button></form>
            </div>
            @else
            <div class="grid grid-cols-2 gap-3 pt-2">
                <a href="{{ route('login') }}" class="flex items-center justify-center px-4 py-3 rounded-xl text-sm font-semibold text-gray-700 bg-gray-50 border border-gray-200 hover:bg-gray-100">Masuk</a>
                <a href="{{ route('register') }}" class="flex items-center justify-center px-4 py-3 rounded-xl text-sm font-bold bg-primary-600 text-white shadow hover:bg-primary-700">Daftar</a>
            </div>
            @endauth
        </div>
    </div>
</nav>
