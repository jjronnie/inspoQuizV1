import './bootstrap'
import './custom'

import Alpine from 'alpinejs'
import { createIcons, icons } from 'lucide'

import './custom.js';



window.Alpine = Alpine
Alpine.start()

document.addEventListener('DOMContentLoaded', () => {
  createIcons({ icons })
})


document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('dashboard-widgets');
    if (!container) return;

    const storageKey = 'dashboardOrder';

    // Initialize Sortable
    new Sortable(container, {
        animation: 150,
        handle: '.flex.items-center.justify-between',
        ghostClass: 'bg-gray-100',
        onEnd: function () {
            const order = Array.from(container.children).map(el => el.dataset.id);
            localStorage.setItem(storageKey, JSON.stringify(order));
        }
    });

    // Restore saved order
    const savedOrder = JSON.parse(localStorage.getItem(storageKey));
    if (savedOrder) {
        savedOrder.forEach(id => {
            const el = document.querySelector(`[data-id="${id}"]`);
            if (el) container.appendChild(el);
        });
    }
});









