<?php

namespace Tests\Unit;

use App\Support\WebhookGuard;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WebhookGuardTest extends TestCase
{
    #[DataProvider('blockedUrls')]
    public function test_blocks_non_public_or_non_http_targets(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);

        WebhookGuard::options($url);
    }

    public static function blockedUrls(): array
    {
        return [
            'cloud metadata' => ['http://169.254.169.254/latest/meta-data'],
            'private 10.x' => ['https://10.0.0.5/hook'],
            'private 192.168.x' => ['https://192.168.1.10/hook'],
            'ftp scheme' => ['ftp://8.8.8.8/hook'],
            'no host' => ['https:///hook'],
        ];
    }

    public function test_allows_public_address_pins_it_and_disables_redirects(): void
    {
        $options = WebhookGuard::options('https://8.8.8.8/hook');

        $this->assertFalse($options['allow_redirects']);
        $this->assertSame(['8.8.8.8:443:8.8.8.8'], $options['curl'][CURLOPT_RESOLVE]);
    }
}
