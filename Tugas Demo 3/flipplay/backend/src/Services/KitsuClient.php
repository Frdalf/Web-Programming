<?php

declare(strict_types=1);

namespace FlipPlay\Services;

use FlipPlay\Models\Anime;
use FlipPlay\Models\Manga;
use FlipPlay\Models\Media;

class KitsuClient
{
    private const BASE_URL = 'https://kitsu.io/api/edge';

    private int $timeout;

    public function __construct(int $timeout = 10)
    {
        $this->timeout = $timeout;
    }

    public function fetchTopAnime(int $limit = 12): array
    {
        return $this->fetch('anime', $limit);
    }

    public function fetchTopManga(int $limit = 6): array
    {
        return $this->fetch('manga', $limit);
    }

    private function fetch(string $type, int $limit): array
    {
        $url = sprintf('%s/%s?sort=-averageRating&page[limit]=%d&include=categories', self::BASE_URL, $type, $limit);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/vnd.api+json\r\nUser-Agent: FlipPlay-CLI\r\n",
                'timeout' => $this->timeout,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            throw new \RuntimeException('Gagal terhubung ke Kitsu API. Periksa koneksi internet.');
        }

        $status = $this->parseStatus($http_response_header ?? []);
        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException("Gagal mengambil data dari server (HTTP status $status).");
        }

        $json = json_decode($body, true);
        if (!is_array($json)) {
            throw new \RuntimeException('Respons Kitsu bukan JSON yang valid.');
        }

        return self::mapJsonApi($json, $type);
    }

    private function parseStatus(array $headers): int
    {
        $status = 0;
        foreach ($headers as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) {
                $status = (int) $m[1];
            }
        }
        return $status;
    }

    public static function mapJsonApi(array $json, string $type): array
    {
        $categoryMap = [];
        foreach ($json['included'] ?? [] as $item) {
            if (($item['type'] ?? '') === 'categories' && isset($item['id'], $item['attributes']['title'])) {
                $categoryMap[$item['id']] = $item['attributes']['title'];
            }
        }

        $result = [];
        foreach ($json['data'] ?? [] as $row) {
            $attrs = $row['attributes'] ?? [];
            $refs = $row['relationships']['categories']['data'] ?? [];
            $genres = array_values(array_filter(array_map(fn(array $ref): ?string => $categoryMap[$ref['id']] ?? null, $refs)));

            $rating = isset($attrs['averageRating']) ? round(((float) $attrs['averageRating']) / 10, 1) : 0.0;
            $title = (string) ($attrs['canonicalTitle'] ?? '');
            $poster = (string) ($attrs['posterImage']['small'] ?? $attrs['posterImage']['original'] ?? '');

            $result[] = $type === 'manga'
                ? new Manga((string) $row['id'], $title, $rating, (int) ($attrs['chapterCount'] ?? 0), $genres, $poster)
                : new Anime((string) $row['id'], $title, $rating, (int) ($attrs['episodeCount'] ?? 0), $genres, $poster);
        }
        return $result;
    }
}
