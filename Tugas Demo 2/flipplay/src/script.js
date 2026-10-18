// Seleksi elemen DOM untuk menu navigasi mobile (Modul 2 Bagian 5.1 - DOM Selection)
const mobileMenu = document.getElementById('mobile-menu');
const menuBtn = document.getElementById('menu-btn');
const iconOpen = document.getElementById('icon-open');
const iconClose = document.getElementById('icon-close');

/**
 * Membuka atau menutup menu navigasi mobile saat tombol hamburger diklik.
 * Konsep Modul 2: Bagian 5.2 (classList.toggle, setAttribute) & Bagian 6 (Event Handling 'click').
 */
menuBtn.addEventListener('click', () => {
  const isHidden = mobileMenu.classList.toggle('hidden');

  iconOpen.classList.toggle('hidden', !isHidden);
  iconClose.classList.toggle('hidden', isHidden);

  menuBtn.setAttribute('aria-expanded', String(!isHidden));
});

/**
 * Menutup menu mobile secara otomatis ketika salah satu tautan di dalamnya diklik.
 * Konsep Modul 2: Bagian 4.2 (forEach), Bagian 5.1 (querySelectorAll), & Bagian 6 (Event Handling 'click').
 */
mobileMenu.querySelectorAll('a').forEach((link) => {
  link.addEventListener('click', () => {
    mobileMenu.classList.add('hidden');
    iconOpen.classList.remove('hidden');
    iconClose.classList.add('hidden');
    menuBtn.setAttribute('aria-expanded', 'false');
  });
});

