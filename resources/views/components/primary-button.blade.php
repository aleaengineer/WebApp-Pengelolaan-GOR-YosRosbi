<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-3 bg-primary-600 border border-transparent rounded-full font-semibold text-sm text-white hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-200 shadow-lg shadow-primary-500/25']) }}>
    {{ $slot }}
</button>
