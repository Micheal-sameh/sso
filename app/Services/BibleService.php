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
     * bolls.life's book "name" field is always English regardless of the
     * translation requested (even for the Arabic SVD translation), so book
     * names are localized from this table instead, keyed by the API's
     * bookid (1-66, standard Protestant canon order).
     *
     * @var array<int, array{en: string, ar: string}>
     */
    private const BOOK_NAMES = [
        1 => ['en' => 'Genesis', 'ar' => 'التكوين'],
        2 => ['en' => 'Exodus', 'ar' => 'الخروج'],
        3 => ['en' => 'Leviticus', 'ar' => 'اللاويين'],
        4 => ['en' => 'Numbers', 'ar' => 'العدد'],
        5 => ['en' => 'Deuteronomy', 'ar' => 'التثنية'],
        6 => ['en' => 'Joshua', 'ar' => 'يشوع'],
        7 => ['en' => 'Judges', 'ar' => 'القضاة'],
        8 => ['en' => 'Ruth', 'ar' => 'راعوث'],
        9 => ['en' => '1 Samuel', 'ar' => 'صموئيل الأول'],
        10 => ['en' => '2 Samuel', 'ar' => 'صموئيل الثاني'],
        11 => ['en' => '1 Kings', 'ar' => 'الملوك الأول'],
        12 => ['en' => '2 Kings', 'ar' => 'الملوك الثاني'],
        13 => ['en' => '1 Chronicles', 'ar' => 'أخبار الأيام الأول'],
        14 => ['en' => '2 Chronicles', 'ar' => 'أخبار الأيام الثاني'],
        15 => ['en' => 'Ezra', 'ar' => 'عزرا'],
        16 => ['en' => 'Nehemiah', 'ar' => 'نحميا'],
        17 => ['en' => 'Esther', 'ar' => 'أستير'],
        18 => ['en' => 'Job', 'ar' => 'أيوب'],
        19 => ['en' => 'Psalms', 'ar' => 'المزامير'],
        20 => ['en' => 'Proverbs', 'ar' => 'الأمثال'],
        21 => ['en' => 'Ecclesiastes', 'ar' => 'الجامعة'],
        22 => ['en' => 'Song of Solomon', 'ar' => 'نشيد الأنشاد'],
        23 => ['en' => 'Isaiah', 'ar' => 'إشعياء'],
        24 => ['en' => 'Jeremiah', 'ar' => 'إرميا'],
        25 => ['en' => 'Lamentations', 'ar' => 'مراثي إرميا'],
        26 => ['en' => 'Ezekiel', 'ar' => 'حزقيال'],
        27 => ['en' => 'Daniel', 'ar' => 'دانيال'],
        28 => ['en' => 'Hosea', 'ar' => 'هوشع'],
        29 => ['en' => 'Joel', 'ar' => 'يوئيل'],
        30 => ['en' => 'Amos', 'ar' => 'عاموس'],
        31 => ['en' => 'Obadiah', 'ar' => 'عوبديا'],
        32 => ['en' => 'Jonah', 'ar' => 'يونان'],
        33 => ['en' => 'Micah', 'ar' => 'ميخا'],
        34 => ['en' => 'Nahum', 'ar' => 'ناحوم'],
        35 => ['en' => 'Habakkuk', 'ar' => 'حبقوق'],
        36 => ['en' => 'Zephaniah', 'ar' => 'صفنيا'],
        37 => ['en' => 'Haggai', 'ar' => 'حجي'],
        38 => ['en' => 'Zechariah', 'ar' => 'زكريا'],
        39 => ['en' => 'Malachi', 'ar' => 'ملاخي'],
        40 => ['en' => 'Matthew', 'ar' => 'متى'],
        41 => ['en' => 'Mark', 'ar' => 'مرقس'],
        42 => ['en' => 'Luke', 'ar' => 'لوقا'],
        43 => ['en' => 'John', 'ar' => 'يوحنا'],
        44 => ['en' => 'Acts', 'ar' => 'أعمال الرسل'],
        45 => ['en' => 'Romans', 'ar' => 'رومية'],
        46 => ['en' => '1 Corinthians', 'ar' => 'كورنثوس الأولى'],
        47 => ['en' => '2 Corinthians', 'ar' => 'كورنثوس الثانية'],
        48 => ['en' => 'Galatians', 'ar' => 'غلاطية'],
        49 => ['en' => 'Ephesians', 'ar' => 'أفسس'],
        50 => ['en' => 'Philippians', 'ar' => 'فيلبي'],
        51 => ['en' => 'Colossians', 'ar' => 'كولوسي'],
        52 => ['en' => '1 Thessalonians', 'ar' => 'تسالونيكي الأولى'],
        53 => ['en' => '2 Thessalonians', 'ar' => 'تسالونيكي الثانية'],
        54 => ['en' => '1 Timothy', 'ar' => 'تيموثاوس الأولى'],
        55 => ['en' => '2 Timothy', 'ar' => 'تيموثاوس الثانية'],
        56 => ['en' => 'Titus', 'ar' => 'تيطس'],
        57 => ['en' => 'Philemon', 'ar' => 'فليمون'],
        58 => ['en' => 'Hebrews', 'ar' => 'العبرانيين'],
        59 => ['en' => 'James', 'ar' => 'يعقوب'],
        60 => ['en' => '1 Peter', 'ar' => 'بطرس الأولى'],
        61 => ['en' => '2 Peter', 'ar' => 'بطرس الثانية'],
        62 => ['en' => '1 John', 'ar' => 'يوحنا الأولى'],
        63 => ['en' => '2 John', 'ar' => 'يوحنا الثانية'],
        64 => ['en' => '3 John', 'ar' => 'يوحنا الثالثة'],
        65 => ['en' => 'Jude', 'ar' => 'يهوذا'],
        66 => ['en' => 'Revelation', 'ar' => 'رؤيا يوحنا'],
    ];

    /**
     * @return array<int, array{id: int, name: string, chapters: int}>
     */
    public function books(): array
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $translation = self::TRANSLATIONS[$locale];

        $books = Cache::remember("bible-books:{$translation}", now()->addDays(30), function () use ($translation) {
            try {
                $response = Http::timeout(10)->get("https://bolls.life/get-books/{$translation}/");

                if (! $response->successful()) {
                    return [];
                }

                return collect($response->json() ?? [])
                    ->map(fn (array $book) => [
                        'id' => $book['bookid'] ?? null,
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

        return collect($books)
            ->map(fn (array $book) => [
                'id' => $book['id'],
                'name' => self::BOOK_NAMES[$book['id']][$locale] ?? (string) $book['id'],
                'chapters' => $book['chapters'],
            ])
            ->all();
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
