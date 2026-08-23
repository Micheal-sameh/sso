<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CopticCalendarService
{
    /**
     * The Synaxarium subsection katameros-api nests inside the Liturgy
     * section; we skip it since Synaxarium commemorations (with their
     * copticchurch.net links) are sourced from coptic.io instead.
     */
    private const KATAMEROS_SYNAXARIUM_SUBSECTION_ID = 10;

    /**
     * Fetch today's Coptic date, Synaxarium commemorations, and daily
     * readings, cached for the rest of the day since the Coptic date only
     * changes once every 24 hours.
     *
     * @return array{date: ?string, commemorations: array<int, array{name: string, url: ?string}>, readings: array<int, array{title: ?string, subSections: array<int, array{title: ?string, readings: array<int, array{title: ?string, reference: ?string, text: ?string}>}>}>}
     */
    public function today(): array
    {
        return $this->forDate(null);
    }

    /**
     * Fetch the Coptic date, Synaxarium commemorations, and daily readings
     * for a given Gregorian date (Y-m-d), or today's if null/invalid.
     *
     * The Coptic date and Synaxarium commemoration links come from
     * coptic.io; the reading passages (with full verse text) come from
     * api.katameros.app.
     *
     * @return array{date: ?string, commemorations: array<int, array{name: string, url: ?string}>, readings: array<int, array{title: ?string, subSections: array<int, array{title: ?string, readings: array<int, array{title: ?string, reference: ?string, text: ?string}>}>}>}
     */
    public function forDate(?string $date): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';

        if ($date !== null && ! $this->isValidDate($date)) {
            $date = null;
        }

        $isToday = $date === null || $date === now()->toDateString();
        $resolvedDate = $date ?? now()->toDateString();
        $cacheKey = "coptic-calendar:{$locale}:{$resolvedDate}";
        $ttl = $isToday ? now()->endOfDay() : now()->addDays(30);

        return Cache::remember($cacheKey, $ttl, fn () => [
            ...$this->fetchCalendar($locale, $isToday ? null : $date),
            'readings' => $this->fetchReadings($locale, $resolvedDate),
        ]);
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
     * @return array{date: ?string, commemorations: array<int, array{name: string, url: ?string}>}
     */
    private function fetchCalendar(string $locale, ?string $date): array
    {
        try {
            $url = $date
                ? "https://api.coptic.io/api/readings/{$date}"
                : 'https://api.coptic.io/api/readings';

            $response = Http::timeout(5)->get($url, ['lang' => $locale]);

            if (! $response->successful()) {
                return ['date' => null, 'commemorations' => []];
            }

            $data = $response->json();

            return [
                'date' => $data['fullDate']['dateString'] ?? null,
                'commemorations' => collect($data['Synaxarium'] ?? [])
                    ->map(fn (array $entry) => [
                        'name' => $entry['name'] ?? '',
                        'url' => $entry['url'] ?? null,
                    ])
                    ->values()
                    ->all(),
            ];
        } catch (Throwable $e) {
            Log::warning('Failed to fetch Coptic calendar data from coptic.io', ['error' => $e->getMessage()]);

            return ['date' => null, 'commemorations' => []];
        }
    }

    /**
     * @return array<int, array{title: ?string, subSections: array<int, array{title: ?string, readings: array<int, array{title: ?string, reference: ?string, text: ?string}>}>}>
     */
    private function fetchReadings(string $locale, string $date): array
    {
        try {
            $languageId = $locale === 'ar' ? 3 : 2;
            $formattedDate = \DateTime::createFromFormat('Y-m-d', $date)->format('d-m-Y');

            $response = Http::timeout(5)->get("https://api.katameros.app/readings/gregorian/{$formattedDate}", [
                'languageId' => $languageId,
            ]);

            if (! $response->successful()) {
                return [];
            }

            $sections = $response->json('sections') ?? [];

            return collect($sections)
                ->map(fn (array $section) => [
                    'title' => $section['title'] ?? null,
                    'subSections' => collect($section['subSections'] ?? [])
                        ->reject(fn (array $subSection) => ($subSection['id'] ?? null) === self::KATAMEROS_SYNAXARIUM_SUBSECTION_ID)
                        ->map(fn (array $subSection) => [
                            'title' => $subSection['title'] ?? null,
                            'readings' => collect($subSection['readings'] ?? [])
                                ->map(fn (array $reading) => $this->mapReading($reading))
                                ->values()
                                ->all(),
                        ])
                        ->values()
                        ->all(),
                ])
                ->filter(fn (array $section) => count($section['subSections']) > 0)
                ->values()
                ->all();
        } catch (Throwable $e) {
            Log::warning('Failed to fetch Coptic daily readings from katameros-api', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $reading
     * @return array{title: ?string, reference: ?string, text: ?string}
     */
    private function mapReading(array $reading): array
    {
        $passages = collect($reading['passages'] ?? []);

        $reference = $passages
            ->map(fn (array $passage) => trim(($passage['bookTranslation'] ?? '').' '.($passage['ref'] ?? '')))
            ->filter()
            ->implode('; ');

        $text = $passages
            ->map(fn (array $passage) => collect($passage['verses'] ?? [])
                ->map(fn (array $verse) => $verse['text'] ?? '')
                ->filter()
                ->implode(' '))
            ->filter()
            ->implode("\n\n");

        return [
            'title' => $reading['title'] ?? null,
            'reference' => $reference !== '' ? $reference : null,
            'text' => $text !== '' ? $text : null,
        ];
    }
}
