import Chart from 'chart.js/auto';

export function initLaporanChart(el) {
    let data = [];
    try {
        data = JSON.parse(el.dataset.laporan || '[]');
    } catch (e) {
        data = [];
    }
    if (!data.length) return;

    new Chart(el, {
        type: 'bar',
        data: {
            labels: data.map(d => d.tanggal),
            datasets: [
                {
                    type: 'bar',
                    label: 'Pendapatan (Rp)',
                    data: data.map(d => d.pendapatan),
                    backgroundColor: 'rgba(185, 28, 28, 0.75)',
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    type: 'line',
                    label: 'Jam Terpakai',
                    data: data.map(d => d.jam),
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.dataset.yAxisID === 'y'
                            ? 'Pendapatan: Rp' + ctx.parsed.y.toLocaleString('id-ID')
                            : 'Jam: ' + ctx.parsed.y,
                    },
                },
            },
            scales: {
                y: {
                    position: 'left',
                    beginAtZero: true,
                    ticks: { callback: (v) => 'Rp' + Number(v).toLocaleString('id-ID') },
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Jam' },
                },
            },
        },
    });
}
