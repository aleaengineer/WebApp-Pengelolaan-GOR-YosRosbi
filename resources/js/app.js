import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Grafik laporan admin: chunk chart.js hanya dimuat jika halaman punya #laporanChart
if (document.getElementById('laporanChart')) {
    import('./laporan-chart').then(({ initLaporanChart }) => initLaporanChart(document.getElementById('laporanChart')));
}

// PWA Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(()=>console.log('SW registered')).catch(()=>{});
    });
}

// PWA Install prompt handler (global)
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    const banner = document.getElementById('pwa-banner');
    if (banner) banner.classList.remove('hidden');
    const btn = document.getElementById('pwa-install-btn');
    if (btn) {
        btn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
                if (banner) banner.classList.add('hidden');
            }
        });
    }
});
