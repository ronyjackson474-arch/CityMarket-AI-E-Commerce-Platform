import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import './effects.js';
import Chart from 'chart.js/auto';

Alpine.plugin(collapse);

window.Alpine = Alpine;
window.Chart = Chart;

// Generic initializer for any <canvas data-chart="..."> fed by a JSON
// script tag sibling (id: `${canvasId}-data`).
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        const dataEl = document.getElementById(`${canvas.id}-data`);
        if (! dataEl) return;

        let cfg;
        try { cfg = JSON.parse(dataEl.textContent); } catch { return; }

        const defaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: cfg.legend ?? true, labels: { boxWidth: 12, font: { size: 11 } } },
                tooltip: { padding: 10, cornerRadius: 8 },
            },
            scales: cfg.type === 'doughnut' || cfg.type === 'pie' ? {} : {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } },
            },
        };

        new Chart(canvas, { ...cfg, options: { ...defaults, ...(cfg.options || {}) } });
    });
});

Alpine.start();
