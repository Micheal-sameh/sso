<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CopticCalendarService
{
    /**
     * Fetch today's Coptic date and Synaxarium commemorations from the
     * coptic.io API, cached for the rest of the day since it only changes
     * once every 24 hours.
     *
     * @return array{date: ?string, commemorations: array<int, array{name: string, url: ?string}>, readings: array<string, ?string>}
     */
    public function today(): array
    {
        return $this->forDate(null);
    }

    /**
     * Fetch the Coptic date, Synaxarium commemorations, and daily readings
     * for a given Gregorian date (Y-m-d), or today's if null/invalid.
     *
     * @return array{date: ?string, commemorations: array<int, array{name: string, url: ?string}>, readings: array<string, ?string>}
     */
    public function forDate(?string $date): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';

        if ($date !== null && ! $this->isValidDate($date)) {
            $date = null;
        }

        $isToday = $date === null || $date === now()->toDateString();
        $cacheKey = "coptic-calendar:{$locale}:".($date ?? now()->toDateString());
        $ttl = $isToday ? now()->endOfDay() : now()->addDays(30);

        return Cache::remember($cacheKey, $ttl, fn () => $this->fetch($locale, $isToday ? null : $date));
    }

    private function isValidDate(string $date): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && checkdate(
            (int) substr($date, 5, 2),
            (int) substr($date, 8, 2),
            (int) substr($date, 0, 4),
        );
    }

    /**
     * @return array{date: ?string, commemorations: array<int, array{name: string, url: ?string}>, readings: array<string, ?string>}
     */
    private function fetch(string $locale, ?string $date): array
    {
        $emptyReadings = [
            'vespersPsalm' => null, 'vespersGospel' => null,
            'matinsPsalm' => null, 'matinsGospel' => null,
            'liturgyPsalm' => null, 'liturgyGospel' => null,
            'pauline' => null, 'catholic' => null, 'acts' => null,
        ];

        try {
            $url = $date
                ? "https://api.coptic.io/api/readings/{$date}"
                : 'https://api.coptic.io/api/readings';

            $response = Http::timeout(5)->get($url, ['lang' => $locale]);

            if (! $response->successful()) {
                return ['date' => null, 'commemorations' => [], 'readings' => $emptyReadings];
            }

            $data = $response->json();
            $reference = $data['reference'] ?? [];

            return [
                'date' => $data['fullDate']['dateString'] ?? null,
                'commemorations' => collect($data['Synaxarium'] ?? [])
                    ->map(fn (array $entry) => [
                        'name' => $entry['name'] ?? '',
                        'url' => $entry['url'] ?? null,
                    ])
                    ->values()
                    ->all(),
                'readings' => [
                    'vespersPsalm' => $reference['VPsalm'] ?? null,
                    'vespersGospel' => $reference['VGospel'] ?? null,
                    'matinsPsalm' => $reference['MPsalm'] ?? null,
                    'matinsGospel' => $reference['MGospel'] ?? null,
                    'liturgyPsalm' => $reference['LPsalm'] ?? null,
                    'liturgyGospel' => $reference['LGospel'] ?? null,
                    'pauline' => $reference['Pauline'] ?? null,
                    'catholic' => $reference['Catholic'] ?? null,
                    'acts' => $reference['Acts'] ?? null,
                ],
            ];
        } catch (Throwable $e) {
            Log::warning('Failed to fetch Coptic calendar data', ['error' => $e->getMessage()]);

            return ['date' => null, 'commemorations' => [], 'readings' => $emptyReadings];
        }
    }
}
