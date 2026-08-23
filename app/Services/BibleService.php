<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class BibleService
{
    private const TRANSLATIONS = [
        'ar' => 'SVD',
        'en' => 'NKJV',
    ];

    /**
     * @return array<int, array{id: int, name: string, chapters: int}>
     */
    public function books(): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $translation = self::TRANSLATIONS[$locale];

        return Cache::remember("bible-books:{$translation}", now()->addDays(30), function () use ($translation) {
            try {
                $response = Http::timeout(10)->get("https://bolls.life/get-books/{$translation}/");

                if (! $response->successful()) {
                    return [];
                }

                return collect($response->json() ?? [])
                    ->map(fn (array $book) => [
                        'id' => $book['bookid'] ?? null,
                        'name' => $book['name'] ?? '',
                        'chapters' => $book['chapters'] ?? 0,
                    ])
                    ->filter(fn (array $book) => $book['id'] !== null)
                    ->values()
                    ->all();
            } catch (Throwable $e) {
                Log::warning('Failed to fetch Bible books from bolls.life', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }

    /**
     * @return array<int, array{verse: int, text: ?string}>
     */
    public function chapter(int $bookId, int $chapter): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $translation = self::TRANSLATIONS[$locale];

        return Cache::remember("bible-chapter:{$translation}:{$bookId}:{$chapter}", now()->addDays(30), function () use ($translation, $bookId, $chapter) {
            try {
                $response = Http::timeout(10)->get("https://bolls.life/get-text/{$translation}/{$bookId}/{$chapter}/");

                if (! $response->successful()) {
                    return [];
                }

                return collect($response->json() ?? [])
                    ->map(fn (array $verse) => [
                        'verse' => $verse['verse'] ?? 0,
                        'text' => isset($verse['text']) ? trim(strip_tags($verse['text'])) : null,
                    ])
                    ->values()
                    ->all();
            } catch (Throwable $e) {
                Log::warning('Failed to fetch Bible chapter text from bolls.life', ['error' => $e->getMessage()]);

                return [];
            }
        });
    }
}
