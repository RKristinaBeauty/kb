// Selectăm butonul și meniul
const menuToggle = document.querySelector('.menu-toggle');
const menu = document.querySelector('.menu');

// Adăugăm evenimentul de click
menuToggle.addEventListener('click', () => {
    menu.classList.toggle('active'); // Adaugă sau elimină clasa 'active'
});

// Închidem meniul când se face click în afara lui (opțional)
document.addEventListener('click', (e) => {
    if (!menu.contains(e.target) && !menuToggle.contains(e.target)) {
        menu.classList.remove('active');
    }
});
