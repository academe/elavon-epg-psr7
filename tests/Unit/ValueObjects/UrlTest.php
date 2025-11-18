<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\ValueObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function test_construct_withValidUrl_createsInstance(): void
    {
        $url = new Url('https://example.com');

        $this->assertSame('https://example.com', $url->url);
    }

    public function test_fromData_withValidString_createsInstance(): void
    {
        $url = Url::fromData('https://example.com');

        $this->assertSame('https://example.com', $url->url);
    }

    public function test_fromData_withNonString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URL must be a string');

        Url::fromData(['url' => 'https://example.com']);
    }

    public function test_toData_returnsString(): void
    {
        $url = new Url('https://example.com');

        $this->assertSame('https://example.com', $url->toData());
    }

    public function test_toString_returnsUrl(): void
    {
        $url = new Url('https://example.com');

        $this->assertSame('https://example.com', (string) $url);
    }

    public function test_construct_withEmptyString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URL cannot be empty');

        new Url('');
    }

    public function test_construct_withInvalidFormat_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid URL format: 'not-a-url'");

        new Url('not-a-url');
    }

    public function test_construct_withTooLongUrl_throwsException(): void
    {
        // Create a URL longer than 2048 characters
        $longUrl = 'https://example.com/' . str_repeat('a', 2040);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URL cannot exceed 2048 characters');

        new Url($longUrl);
    }

    /**
     * @dataProvider validUrlProvider
     */
    public function test_construct_withValidFormats_succeeds(string $url): void
    {
        $urlObject = new Url($url);

        $this->assertSame($url, $urlObject->url);
    }

    public static function validUrlProvider(): array
    {
        return [
            'http' => ['http://example.com'],
            'https' => ['https://example.com'],
            'with path' => ['https://example.com/path/to/resource'],
            'with query' => ['https://example.com/path?key=value&other=123'],
            'with fragment' => ['https://example.com/page#section'],
            'with port' => ['https://example.com:8080/path'],
            'with subdomain' => ['https://api.example.com'],
            'with auth' => ['https://user:pass@example.com'],
            'complex' => ['https://user:pass@api.example.com:8080/v1/resource?key=value#section'],
        ];
    }

    /**
     * @dataProvider invalidUrlProvider
     */
    public function test_construct_withInvalidFormats_throwsException(string $url): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL format');

        new Url($url);
    }

    public static function invalidUrlProvider(): array
    {
        return [
            'no protocol' => ['example.com'],
            'spaces' => ['https://example .com'],
            'missing domain' => ['https://'],
            'just protocol' => ['https'],
        ];
    }

    public function test_serializationRoundTrip_preservesValue(): void
    {
        $original = new Url('https://api.example.com/v1/transactions');
        $data = $original->toData();
        $restored = Url::fromData($data);

        $this->assertSame($original->url, $restored->url);
        $this->assertSame('https://api.example.com/v1/transactions', $data);
    }
}
