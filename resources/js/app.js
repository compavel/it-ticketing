

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Konfirmasi untuk aksi destruktif: tambahkan data-confirm="Pesan" pada <form>.
document.addEventListener('submit', (event) => {
    const message = event.target.dataset?.confirm;

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});
