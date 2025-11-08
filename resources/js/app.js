import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine globally available
window.Alpine = Alpine;

// Start Alpine when the page loads
window.addEventListener('alpine:init', () => {
    console.log('Alpine.js is initializing...');
});

// Start Alpine
Alpine.start();
console.log('Alpine.js initialized');

