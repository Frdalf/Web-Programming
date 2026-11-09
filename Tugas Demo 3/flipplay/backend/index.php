<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'FlipPlay\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

use FlipPlay\Auras\AllAura;
use FlipPlay\Auras\BingeAura;
use FlipPlay\Auras\GenreAura;
use FlipPlay\Models\Anime;
use FlipPlay\Models\Manga;
use FlipPlay\Models\Media;
use FlipPlay\Models\User;
use FlipPlay\Services\KitsuClient;
use FlipPlay\Services\Library;

function heading(string $text): void
{
    echo PHP_EOL . str_repeat('═', 70) . PHP_EOL . "  $text" . PHP_EOL . str_repeat('═', 70) . PHP_EOL;
}

function printList(array $list, int $total = 0): void
{
    if ($total > 0) {
        echo "  Menampilkan " . count($list) . " dari $total media" . PHP_EOL;
    }
    if (empty($list)) {
        echo "  Tidak ada media yang cocok dengan pencarian atau Aura yang dipilih." . PHP_EOL;
        return;
    }
    foreach ($list as $media) {
        echo "  " . $media->getSummary() . PHP_EOL;
    }
}

echo PHP_EOL . "  ▶ FlipPlay Backend Engine · The Vibe Grid (CLI)" . PHP_EOL;

heading('1. Menambahkan media ke dalam library');
$library = new Library();
$offline = in_array('--offline', $argv, true);

function addAndPrint(Library $library, array $medias): int
{
    foreach ($medias as $media) {
        $library->addMedia($media);
        echo "  + [{$media->getType()}] {$media->getTitle()} ditambahkan" . PHP_EOL;
    }
    return count($medias);
}

echo "  Memuat data dari Kitsu API..." . PHP_EOL;
try {
    if ($offline) {
        throw new RuntimeException('Mode offline dipilih.');
    }
    $client = new KitsuClient(timeout: 10);
    $animeList = $client->fetchTopAnime(12);
    $mangaList = $client->fetchTopManga(6);
    echo "  ✔ Berhasil mengambil data dari Kitsu API" . PHP_EOL;
} catch (RuntimeException $e) {
    echo "  ✖ " . $e->getMessage() . PHP_EOL;
    echo "  ↺ Memakai data cadangan lokal (data/kitsu-fallback.json)" . PHP_EOL;
    $fallback = json_decode((string) file_get_contents(__DIR__ . '/data/kitsu-fallback.json'), true);
    $animeList = KitsuClient::mapJsonApi($fallback['anime'], 'anime');
    $mangaList = KitsuClient::mapJsonApi($fallback['manga'], 'manga');
}
$nAnime = addAndPrint($library, $animeList);
$nManga = addAndPrint($library, $mangaList);
echo "  ✔ Dimuat: $nAnime anime + $nManga manga" . PHP_EOL;

echo PHP_EOL . "  Penambahan manual:" . PHP_EOL;
$manualAnime = new Anime('local-1', 'Langit Merah Nusantara', 8.4, 26, ['Action', 'Adventure']);
$manualManga = new Manga('local-2', 'Surat untuk Senja', 8.1, 150, ['Drama', 'Romance']);
foreach ([$manualAnime, $manualManga] as $media) {
    $library->addMedia($media);
    echo "  + [{$media->getType()}] {$media->getTitle()} ditambahkan manual" . PHP_EOL;
}

echo PHP_EOL . "  Total media di library: {$library->count()}" . PHP_EOL;

$library->registerAura(new AllAura());
$library->registerAura(new GenreAura('energy', 'High Energy', ['Action', 'Sports', 'Adventure']));
$library->registerAura(new GenreAura('melancholy', 'Melancholy', ['Drama', 'Romance']));
$library->registerAura(new BingeAura());

// Mode interaktif: php index.php --interactive [--offline]
// Menu input/output untuk 5 alur simulasi, memakai class yang sama dengan mode demo.
if (in_array('--interactive', $argv, true)) {
    /** Baca satu baris input dari keyboard; null jika input berakhir (Ctrl+D). */
    function ask(string $prompt): ?string
    {
        echo $prompt;
        $line = fgets(STDIN);
        return $line === false ? null : trim($line);
    }

    /** Cari media berdasarkan judul persis; tampilkan pesan jika tidak ada. */
    function askMedia(Library $library, string $prompt): ?Media
    {
        $title = ask($prompt) ?? '';
        $media = $library->findByTitle($title);
        if ($media === null) {
            echo "  ✖ Media \"$title\" tidak ditemukan (judul harus persis, cek menu 2)" . PHP_EOL;
        }
        return $media;
    }

    $user = new User(ask(PHP_EOL . '  Masukkan username: ') ?: 'farid');
    $localId = 0;

    while (true) {
        heading('Menu FlipPlay (@' . $user->getUsername() . ')');
        echo "  1. Menambahkan media ke dalam library" . PHP_EOL
            . "  2. Menampilkan daftar media yang tersedia" . PHP_EOL
            . "  3. Mencari media berdasarkan judul" . PHP_EOL
            . "  4. Menampilkan informasi detail suatu media" . PHP_EOL
            . "  5. Menampilkan koleksi media milik pengguna" . PHP_EOL
            . "  6. Tambah media ke koleksi" . PHP_EOL
            . "  7. Hapus media dari koleksi" . PHP_EOL
            . "  0. Keluar" . PHP_EOL;
        $choice = ask('  Pilih menu: ');
        if ($choice === null || $choice === '0') {
            echo PHP_EOL . "  Sampai jumpa!" . PHP_EOL . PHP_EOL;
            break;
        }

        try {
            switch ($choice) {
                case '1':
                    $type = strtolower(ask('  Tipe (anime/manga): ') ?? '');
                    if (!in_array($type, ['anime', 'manga'], true)) {
                        echo "  ✖ Tipe harus anime atau manga" . PHP_EOL;
                        break;
                    }
                    $title = ask('  Judul: ') ?? '';
                    $rating = ask('  Rating (0-10): ') ?? '';
                    $unit = $type === 'anime' ? 'episode' : 'chapter';
                    $length = ask("  Jumlah $unit: ") ?? '';
                    if (!is_numeric($rating) || !ctype_digit($length)) {
                        echo "  ✖ Rating harus angka dan jumlah $unit harus bilangan bulat" . PHP_EOL;
                        break;
                    }
                    $genreInput = ask('  Genre (pisahkan dengan koma, mis. Action, Drama): ') ?? '';
                    $genres = array_values(array_filter(array_map('trim', explode(',', $genreInput))));
                    $id = 'local-' . (++$localId + 2); // local-1 & local-2 dipakai media manual bagian 1
                    // Constructor memvalidasi rating lewat setRating()
                    $media = $type === 'anime'
                        ? new Anime($id, $title, (float) $rating, (int) $length, $genres)
                        : new Manga($id, $title, (float) $rating, (int) $length, $genres);
                    $library->addMedia($media);
                    echo "  + [{$media->getType()}] {$media->getTitle()} ditambahkan" . PHP_EOL;
                    echo "  Total media di library: {$library->count()}" . PHP_EOL;
                    break;

                case '2':
                    echo "  -- Anime --" . PHP_EOL;
                    printList($library->getByType('Anime'));
                    echo PHP_EOL . "  -- Manga --" . PHP_EOL;
                    printList($library->getByType('Manga'));
                    break;

                case '3':
                    $keyword = ask('  Kata kunci judul: ') ?? '';
                    $auraIds = array_map(fn($a) => $a->getId(), $library->getAuras());
                    $aura = ask('  Aura (' . implode('/', $auraIds) . ', Enter = all): ') ?: 'all';
                    printList($library->search($keyword, $aura), $library->count());
                    break;

                case '4':
                    $media = askMedia($library, '  Judul media: ');
                    if ($media !== null) {
                        echo $media->getDetail() . PHP_EOL;
                    }
                    break;

                case '5':
                    echo "  Koleksi @{$user->getUsername()}:" . PHP_EOL;
                    printList($user->getCollection());
                    echo "  Total episode anime di koleksi: {$user->getTotalEpisodes()}" . PHP_EOL;
                    break;

                case '6':
                    $media = askMedia($library, '  Judul media yang ditambahkan ke koleksi: ');
                    if ($media !== null) {
                        echo $user->addToCollection($media)
                            ? "  + \"{$media->getTitle()}\" ditambahkan ke koleksi" . PHP_EOL
                            : "  ! \"{$media->getTitle()}\" sudah ada di koleksi" . PHP_EOL;
                    }
                    break;

                case '7':
                    $media = askMedia($library, '  Judul media yang dihapus dari koleksi: ');
                    if ($media !== null) {
                        echo $user->removeFromCollection($media)
                            ? "  - \"{$media->getTitle()}\" dihapus dari koleksi" . PHP_EOL
                            : "  ! \"{$media->getTitle()}\" tidak ada di koleksi" . PHP_EOL;
                    }
                    break;

                default:
                    echo "  ✖ Menu \"$choice\" tidak dikenal" . PHP_EOL;
            }
        } catch (InvalidArgumentException $e) {     // validasi rating / aura
            echo "  ✖ " . $e->getMessage() . PHP_EOL;
        }
    }
    exit(0);
}

heading('2. Menampilkan daftar media yang tersedia');
echo "  -- Anime --" . PHP_EOL;
printList($library->getByType('Anime'));
echo PHP_EOL . "  -- Manga --" . PHP_EOL;
printList($library->getByType('Manga'));

heading('3. Mencari media berdasarkan judul');
$firstAnime = $library->getByType('Anime')[0] ?? null;
$dynamicKeyword = $firstAnime ? explode(' ', $firstAnime->getTitle())[0] : 'a';
foreach ([$dynamicKeyword, 'nusantara', 'zzzxq'] as $keyword) {
    echo PHP_EOL . "  🔎 \"$keyword\"" . PHP_EOL;
    printList($library->search($keyword), $library->count());
}

heading('3b. Aura-Based Discovery');
foreach ($library->getAuras() as $aura) {
    if ($aura->getId() === 'all') {
        continue;
    }
    echo PHP_EOL . "  ✨ Aura: {$aura->getLabel()}" . PHP_EOL;
    printList($library->search('', $aura->getId()), $library->count());
}

echo PHP_EOL . "  🔎 \"on\" + ✨ Melancholy (filter gabungan)" . PHP_EOL;
printList($library->search('on', 'melancholy'), $library->count());

heading('4. Menampilkan informasi detail suatu media');
$firstManga = $library->getByType('Manga')[0] ?? null;
$titles = [
    $firstAnime?->getTitle() ?? '',
    $firstManga?->getTitle() ?? '',
    $manualManga->getTitle(),
    'Judul Yang Tidak Ada',
];
foreach ($titles as $title) {
    $media = $library->findByTitle($title);
    echo $media
        ? $media->getDetail() . PHP_EOL . PHP_EOL
        : "  Media \"$title\" tidak ditemukan" . PHP_EOL;
}

heading('5. Menampilkan koleksi media milik pengguna');
$user = new User('farid');
$picks = $library->getTopRated(4);
$picks[] = $picks[0];

foreach ($picks as $media) {
    echo $user->addToCollection($media)
        ? "  + \"{$media->getTitle()}\" ditambahkan ke koleksi" . PHP_EOL
        : "  ! \"{$media->getTitle()}\" sudah ada di koleksi" . PHP_EOL;
}
if ($user->removeFromCollection($picks[3])) {
    echo "  - \"{$picks[3]->getTitle()}\" dihapus dari koleksi" . PHP_EOL;
}

echo PHP_EOL . "  Koleksi @{$user->getUsername()}:" . PHP_EOL;
printList($user->getCollection());
echo "  Total episode anime di koleksi: {$user->getTotalEpisodes()}" . PHP_EOL;

heading('Bonus: validasi');
try {
    $library->getAll()[0]->setRating(12);
} catch (InvalidArgumentException $e) {
    echo "  ✖ " . $e->getMessage() . PHP_EOL;
}
try {
    $library->search('', 'happy');
} catch (InvalidArgumentException $e) {
    echo "  ✖ " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;
