import 'bootstrap/dist/js/bootstrap.bundle.min';
import Chart from 'chart.js/auto';
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('zoneChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Active Zones', 'Inactive Zones', 'Secondary Zones'],
                datasets: [{ data: [120, 15, 8], backgroundColor: ['#3b82f6', '#ef4444', '#10b981'] }]
            }
        });
    }
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('d-none');
            document.querySelector('.sidebar').classList.toggle('d-block');
        });
    }
});
