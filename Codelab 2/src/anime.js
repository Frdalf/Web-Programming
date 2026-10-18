const API_URL = 'https://kitsu.io/api/edge/anime?sort=-averageRating&page[limit]=12&include=categories';


//const API_URL = 'https://kitsu.io/api/edge/animex';


const loadingEl = document.getElementById('loading');
const errorEl = document.getElementById('error');
const errorText = document.getElementById('error-text');
const listEl = document.getElementById('anime-list');
const retryBtn = document.getElementById('retry-btn');

/**
 * Mengubah satu objek data anime (JSON:API format dari Kitsu) menjadi string markup HTML card.
 * @param {Object} anime - Objek anime dari json.data
 * @param {Map} categoryMap - Map id kategori ke title kategori dari json.included
 */
function createCard(anime, categoryMap) {
  const attrs = anime.attributes || {};
  const title = attrs.canonicalTitle || 'Tanpa judul';
  const posterUrl = attrs.posterImage?.small || attrs.posterImage?.original || '';

  let scoreDisplay = '-';
  if (attrs.averageRating) {
    const ratingNum = parseFloat(attrs.averageRating);
    if (!isNaN(ratingNum)) {
      scoreDisplay = (ratingNum / 10).toFixed(1);
    }
  }

  const episodesDisplay = attrs.episodeCount ?? '?';

  const categoryRefs = anime.relationships?.categories?.data || [];
  const genreNames = categoryRefs
    .map((ref) => categoryMap.get(ref.id))
    .filter(Boolean)
    .slice(0, 3);

  const genresDisplay = genreNames.length > 0 ? genreNames.join(', ') : 'Tanpa genre';

  return `
    <article class="bg-white rounded shadow overflow-hidden flex flex-col">
      <img src="${posterUrl}" alt="${title}" class="w-full h-64 object-cover" loading="lazy">
      <div class="p-4 flex flex-col flex-1 justify-between">
        <div>
          <h3 class="font-bold text-gray-800 line-clamp-2 mb-1" title="${title}">${title}</h3>
          <p class="text-sm text-gray-600">⭐ ${scoreDisplay} · ${episodesDisplay} episode</p>
        </div>
        <p class="text-xs text-gray-500 mt-2">${genresDisplay}</p>
      </div>
    </article>
  `;
}

async function fetchAnime() {
  loadingEl.classList.remove('hidden');
  errorEl.classList.add('hidden');
  listEl.innerHTML = '';

  try {
    const response = await fetch(API_URL, {
      headers: {
        'Accept': 'application/vnd.api+json',
      },
      signal: AbortSignal.timeout(10000),
    });

    if (!response.ok) {
      throw new Error(`Gagal mengambil data (status ${response.status})`);
    }

    const json = await response.json();
    const animeList = json.data || [];
    const included = json.included || [];

    const categoryMap = new Map();
    if (Array.isArray(included)) {
      included.forEach((item) => {
        if (item.type === 'categories' && item.id && item.attributes?.title) {
          categoryMap.set(item.id, item.attributes.title);
        }
      });
    }

    listEl.innerHTML = animeList.map((anime) => createCard(anime, categoryMap)).join('');
  } catch (error) {
    console.error('Error saat fetch anime:', error);

    if (error.name === 'TimeoutError') {
      errorText.textContent = 'Server terlalu lama merespons. Coba lagi beberapa saat lagi.';
    } else {
      errorText.textContent = error.message;
    }

    errorEl.classList.remove('hidden');
  } finally {
    loadingEl.classList.add('hidden');
  }
}

retryBtn.addEventListener('click', fetchAnime);

fetchAnime();
