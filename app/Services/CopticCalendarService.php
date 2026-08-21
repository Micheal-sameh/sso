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
     * @return array{date: ?string, commemorations: array<int, array{name: string, url: ?string}>}
     */
    public function today(): array
    {
        return Cache::remember(
            'coptic-calendar:'.now()->toDateString(),
            now()->endOfDay(),
            fn () => $this->fetch(),
        );
    }

    /**
     * @return array{date: ?string, commemorations: array<int, array{name: string, url: ?string}>}
     */
    private function fetch(): array
    {
        try {
            $response = Http::timeout(5)->get('https://api.coptic.io/api/readings', ['lang' => 'ar']);

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
            Log::warning('Failed to fetch Coptic calendar data', ['error' => $e->getMessage()]);

            return ['date' => null, 'commemorations' => []];
        }
    }
}
