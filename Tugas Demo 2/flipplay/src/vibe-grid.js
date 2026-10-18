// Endpoint utama Jikan API (mengambil daftar anime terpopuler)
const API_URL = 'https://api.jikan.moe/v4/top/anime';

// Aktifkan baris di bawah ini (dan komentari API_URL di atas) saat demo untuk menguji error HTTP 404:
// const API_URL = 'https://api.jikan.moe/v4/animex';

// Seleksi elemen DOM (Modul 2 Bagian 5.1 - DOM Selection)
const loadingEl = document.getElementById('loading');
const errorEl = document.getElementById('error');
const errorText = document.getElementById('error-text');
const retryBtn = document.getElementById('retry-btn');
const emptyEl = document.getElementById('empty-state');
const resultCountEl = document.getElementById('result-count');
const gridEl = document.getElementById('anime-grid');
const searchInput = document.getElementById('search-input');
const auraFiltersEl = document.getElementById('aura-filters');

// State aplikasi (Modul 2 Bagian 2.1 - let vs const)
let animeData = [];      // data asli dari API, diisi sekali
let keyword = '';        // kata kunci dari input search real-time
let activeAura = 'all';  // id Aura yang sedang aktif dari tombol Aura

/**
 * Mengecek apakah sebuah anime memiliki salah satu genre yang dicari.
 * Konsep Modul 2: Bagian 4.2 (Array Method some & includes).
 */
function hasGenre(anime, targetGenres) {
  const genres = anime.genres || [];
  return genres.some((genre) => targetGenres.includes(genre.name));
}

// Definisi kategori Aura sebagai Array of Objects (Modul 2 Bagian 4.1 - Array & Object)
const AURAS = [
  { id: 'all',        label: 'Semua',       match: () => true },
  { id: 'energy',     label: 'High Energy', match: (anime) => hasGenre(anime, ['Action', 'Sports', 'Adventure']) },
  { id: 'melancholy', label: 'Melancholy',  match: (anime) => hasGenre(anime, ['Drama', 'Romance']) },
  { id: 'binge',      label: 'Binge Mode',  match: (anime) => (anime.episodes ?? 0) > 24 },
];

/**
 * Membersihkan karakter spesial HTML pada teks untuk mencegah celah XSS saat memakai innerHTML.
 * Konsep Modul 2: Bagian 9.1 (Keamanan innerHTML & Pencegahan XSS).
 */
function escapeHTML(str) {
  return String(str ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

/**
 * Mengubah satu objek anime dari Jikan API menjadi string HTML card.
 * Konsep Modul 2: Bagian 3.3 (Template Literal), Bagian 4.2 (Array Methods: map, slice, join), & Nullish Coalescing (??).
 */
function createCard(anime) {
  const title = escapeHTML(anime.title || 'Tanpa judul');
  const imageUrl = anime.images?.jpg?.image_url || '';
  const scoreDisplay = anime.score ?? '-';
  const episodesDisplay = anime.episodes ?? '?';
  const typeDisplay = escapeHTML(anime.type ?? 'TV');
  const yearDisplay = anime.year ?? '-';

  const genreNames = (anime.genres || [])
    .map((genre) => escapeHTML(genre.name))
    .slice(0, 3);
  const genresDisplay = genreNames.length > 0 ? genreNames.join(', ') : 'Tanpa genre';

  return `
    <article data-id="${anime.mal_id}" class="bg-secondary rounded-lg shadow overflow-hidden flex flex-col border border-gray-800 hover:border-primary transition">
      <div class="relative">
        <img src="${imageUrl}" alt="${title}" class="w-full h-64 object-cover" loading="lazy">
        <span class="absolute top-2 right-2 bg-dark/80 text-xs font-semibold px-2 py-1 rounded text-white">
          ${typeDisplay} · ${yearDisplay}
        </span>
      </div>
      <div class="p-4 flex flex-col flex-1 justify-between">
        <div>
          <h3 class="font-bold text-white line-clamp-2 mb-1" title="${title}">${title}</h3>
          <p class="text-sm text-gray-300">⭐ ${scoreDisplay} · ${episodesDisplay} eps</p>
        </div>
        <p class="text-xs text-primary font-medium mt-3">${genresDisplay}</p>
      </div>
    </article>
  `;
}

/**
 * Menampilkan daftar anime ke dalam wadah grid dan mengatur visibilitas empty state serta info jumlah hasil.
 * Konsep Modul 2: Bagian 4.2 (Array map & join) dan Bagian 5.2 (DOM Manipulation: innerHTML, textContent, classList).
 */
function renderCards(list) {
  resultCountEl.textContent = `Menampilkan ${list.length} dari ${animeData.length} anime`;

  if (list.length === 0) {
    gridEl.innerHTML = '';
    emptyEl.classList.remove('hidden');
    return;
  }

  emptyEl.classList.add('hidden');
  gridEl.innerHTML = list.map((anime) => createCard(anime)).join('');
}

/**
 * Menyaring animeData dengan mengombinasikan filter keyword (Unified Search) dan filter Aura (Aura-Based Discovery).
 * Konsep Modul 2: Bagian 4.2 (Array Methods: find & chaining filter).
 */
function applyFilters() {
  const currentAura = AURAS.find((aura) => aura.id === activeAura) || AURAS[0];

  const filtered = animeData
    .filter((anime) => {
      const title = (anime.title || '').toLowerCase();
      return title.includes(keyword);
    })
    .filter((anime) => currentAura.match(anime));

  renderCards(filtered);
}

/**
 * Memperbarui tampilan class CSS pada tombol Aura agar tombol yang sedang aktif terlihat menonjol.
 * Konsep Modul 2: Bagian 5.2 (DOM Manipulation: querySelectorAll, dataset, classList.toggle).
 */
function updateAuraButtonStyles() {
  const buttons = auraFiltersEl.querySelectorAll('button');
  buttons.forEach((btn) => {
    const isActive = btn.dataset.aura === activeAura;
    btn.classList.toggle('bg-primary', isActive);
    btn.classList.toggle('border-primary', isActive);
    btn.classList.toggle('bg-secondary', !isActive);
    btn.classList.toggle('border-gray-600', !isActive);
    btn.setAttribute('aria-pressed', String(isActive));
  });
}

/**
 * Me-render daftar tombol Aura ke dalam DOM dan memasang event listener 'click' pada setiap tombol.
 * Konsep Modul 2: Bagian 3.3 (Template Literal), Bagian 4.2 (map, join, forEach), & Bagian 6 (Event Handling 'click').
 */
function renderAuraButtons() {
  auraFiltersEl.innerHTML = AURAS.map(
    (aura) => `
      <button
        type="button"
        data-aura="${aura.id}"
        class="px-4 py-2 rounded-lg text-sm font-semibold text-white border transition cursor-pointer hover:border-primary"
      >
        ${aura.label}
      </button>
    `
  ).join('');

  updateAuraButtonStyles();

  auraFiltersEl.querySelectorAll('button').forEach((btn) => {
    btn.addEventListener('click', () => {
      activeAura = btn.dataset.aura;
      updateAuraButtonStyles();
      applyFilters();
    });
  });
}

/**
 * Mengambil data anime dari Jikan API secara asynchronous, mengelola loading state dan error handling.
 * Konsep Modul 2: Bagian 7 & 8 (Asynchronous JS, Fetch API, async/await, try/catch/finally, response.ok).
 */
async function fetchAnime() {
  loadingEl.classList.remove('hidden');
  errorEl.classList.add('hidden');
  emptyEl.classList.add('hidden');
  resultCountEl.textContent = '';
  gridEl.innerHTML = '';

  try {
    const response = await fetch(API_URL, {
      signal: AbortSignal.timeout(10000),
    });

    if (!response.ok) {
      throw new Error(`Gagal mengambil data dari server (HTTP status ${response.status}).`);
    }

    const json = await response.json();
    animeData = json.data || [];

    applyFilters();
  } catch (error) {
    console.error('Error saat fetch anime:', error);

    if (error.name === 'TimeoutError') {
      errorText.textContent = 'Waktu permintaan habis (timeout). Server terlalu lama merespons.';
    } else if (error instanceof TypeError) {
      errorText.textContent = 'Gagal terhubung ke jaringan. Periksa koneksi internetmu lalu coba lagi.';
    } else {
      errorText.textContent = error.message;
    }

    errorEl.classList.remove('hidden');
  } finally {
    loadingEl.classList.add('hidden');
  }
}

// Event listener untuk pencarian real-time saat pengguna mengetik (Modul 2 Bagian 6 - Event Handling 'input')
searchInput.addEventListener('input', (event) => {
  keyword = event.target.value.trim().toLowerCase();
  applyFilters();
});

// Event listener untuk tombol "Coba lagi" saat terjadi error (Modul 2 Bagian 6 - Event Handling 'click')
retryBtn.addEventListener('click', fetchAnime);

// Inisialisasi awal: render tombol Aura dan ambil data anime dari API
renderAuraButtons();
fetchAnime();

