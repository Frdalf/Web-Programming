// Endpoint utama Kitsu API (mengambil 12 anime dengan rating tertinggi beserta kategorinya)
const API_URL = 'https://kitsu.io/api/edge/anime?sort=-averageRating&page[limit]=12&include=categories';

// Aktifkan baris di bawah ini (dan komentari API_URL di atas) saat demo untuk menguji error HTTP 404:
// const API_URL = 'https://kitsu.io/api/edge/animex';

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
let animeData = [];            // data asli anime dari json.data, diisi sekali
let categoryMap = new Map();   // map id kategori -> nama kategori dari json.included
let keyword = '';              // kata kunci dari input search real-time
let activeAura = 'all';        // id Aura yang sedang aktif dari tombol Aura

/**
 * Mengambil daftar nama kategori/genre milik satu anime berdasarkan relasi id di categoryMap.
 * Konsep Modul 2: Bagian 4.2 (Array Methods: map & filter).
 */
function getGenres(anime) {
  const categoryRefs = anime.relationships?.categories?.data || [];
  return categoryRefs
    .map((ref) => categoryMap.get(ref.id))
    .filter(Boolean);
}

/**
 * Mengecek apakah sebuah anime memiliki salah satu kategori/genre yang dicari.
 * Konsep Modul 2: Bagian 4.2 (Array Method some & includes).
 */
function hasGenre(anime, targetGenres) {
  const genres = getGenres(anime);
  return genres.some((genre) => targetGenres.includes(genre));
}

// Definisi kategori Aura sebagai Array of Objects (Modul 2 Bagian 4.1 - Array & Object)
const AURAS = [
  { id: 'all',        label: 'Semua',       match: () => true },
  { id: 'energy',     label: 'High Energy', match: (anime) => hasGenre(anime, ['Action', 'Sports', 'Adventure']) },
  { id: 'melancholy', label: 'Melancholy',  match: (anime) => hasGenre(anime, ['Drama', 'Romance']) },
  { id: 'binge',      label: 'Binge Mode',  match: (anime) => (anime.attributes?.episodeCount ?? 0) > 24 },
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
 * Mengubah satu objek anime (format JSON:API dari Kitsu) menjadi string HTML card.
 * Konsep Modul 2: Bagian 3.3 (Template Literal), Bagian 4.2 (Array Methods: map, slice, join), & Nullish Coalescing (??).
 */
function createCard(anime) {
  const attrs = anime.attributes || {};
  const title = escapeHTML(attrs.canonicalTitle || 'Tanpa judul');
  const posterUrl = attrs.posterImage?.small || attrs.posterImage?.original || '';

  let scoreDisplay = '-';
  if (attrs.averageRating) {
    const ratingNum = parseFloat(attrs.averageRating);
    if (!isNaN(ratingNum)) {
      scoreDisplay = (ratingNum / 10).toFixed(1);
    }
  }

  const episodesDisplay = attrs.episodeCount ?? '?';
  const genreNames = getGenres(anime)
    .map((name) => escapeHTML(name))
    .slice(0, 3);
  const genresDisplay = genreNames.length > 0 ? genreNames.join(', ') : 'Tanpa genre';

  return `
    <article data-id="${anime.id}" class="bg-secondary rounded-lg shadow overflow-hidden flex flex-col border border-gray-800 hover:border-primary transition">
      <img src="${posterUrl}" alt="${title}" class="w-full h-64 object-cover" loading="lazy">
      <div class="p-4 flex flex-col flex-1 justify-between">
        <div>
          <h3 class="font-bold text-white line-clamp-2 mb-1" title="${title}">${title}</h3>
          <p class="text-sm text-gray-300">⭐ ${scoreDisplay} · ${episodesDisplay} episode</p>
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
      const title = (anime.attributes?.canonicalTitle || '').toLowerCase();
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
 * Mengambil data anime dari Kitsu API secara asynchronous, mengelola loading state dan error handling.
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
      headers: {
        'Accept': 'application/vnd.api+json',
      },
      signal: AbortSignal.timeout(10000),
    });

    if (!response.ok) {
      throw new Error(`Gagal mengambil data dari server (HTTP status ${response.status}).`);
    }

    const json = await response.json();
    animeData = json.data || [];
    const included = json.included || [];

    categoryMap = new Map();
    if (Array.isArray(included)) {
      included.forEach((item) => {
        if (item.type === 'categories' && item.id && item.attributes?.title) {
          categoryMap.set(item.id, item.attributes.title);
        }
      });
    }

    applyFilters();
  } catch (error) {
    console.error('Error saat fetch anime:', error);

    if (error.name === 'TimeoutError') {
      errorText.textContent = 'Server terlalu lama merespons. Coba lagi beberapa saat lagi.';
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
