<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\ValueObjects;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\ValueObjects\LanguageTag;
use PHPUnit\Framework\TestCase;

class LanguageTagTest extends TestCase
{
    public function test_construct_withValidTag_createsInstance(): void
    {
        $tag = new LanguageTag('en-GB');

        $this->assertSame('en-GB', $tag->tag);
    }

    public function test_fromData_withValidString_createsInstance(): void
    {
        $tag = LanguageTag::fromData('fr-FR');

        $this->assertSame('fr-FR', $tag->tag);
    }

    public function test_fromData_withNonString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Language tag must be a string');

        LanguageTag::fromData(['tag' => 'en-GB']);
    }

    public function test_toData_returnsString(): void
    {
        $tag = new LanguageTag('de-DE');

        $this->assertSame('de-DE', $tag->toData());
    }

    public function test_toString_returnsTag(): void
    {
        $tag = new LanguageTag('es-ES');

        $this->assertSame('es-ES', (string) $tag);
    }

    public function test_construct_withEmptyString_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Language tag cannot be empty');

        new LanguageTag('');
    }

    public function test_construct_withInvalidFormat_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid language tag format: 'not-a-tag-123-!'");

        new LanguageTag('not-a-tag-123-!');
    }

    public function test_construct_withTooLongTag_throwsException(): void
    {
        $longTag = 'en-' . str_repeat('X', 260);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Language tag cannot exceed 255 characters');

        new LanguageTag($longTag);
    }

    /**
     * @dataProvider validLanguageTagProvider
     */
    public function test_construct_withValidFormats_succeeds(string $tag, string $expectedLanguage, ?string $expectedRegion): void
    {
        $tagObject = new LanguageTag($tag);

        $this->assertSame($tag, $tagObject->tag);
        $this->assertSame($expectedLanguage, $tagObject->getLanguage());
        $this->assertSame($expectedRegion, $tagObject->getRegion());
    }

    public static function validLanguageTagProvider(): array
    {
        return [
            'language only' => ['en', 'en', null],
            'language-region' => ['en-GB', 'en', 'GB'],
            'language-region French' => ['fr-FR', 'fr', 'FR'],
            'language-region Spanish' => ['es-ES', 'es', 'ES'],
            'language-region German' => ['de-DE', 'de', 'DE'],
            'language-region Chinese' => ['zh-CN', 'zh', 'CN'],
            'language-script-region' => ['zh-Hans-CN', 'zh', 'CN'],
            'language-script-region Traditional' => ['zh-Hant-TW', 'zh', 'TW'],
            'language-region-variant' => ['en-US-x-twain', 'en', 'US'],
            'three letter language' => ['yue-HK', 'yue', 'HK'],
            'numeric region' => ['es-419', 'es', '419'],
        ];
    }

    /**
     * @dataProvider invalidLanguageTagProvider
     */
    public function test_construct_withInvalidFormats_throwsException(string $tag): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid language tag format');

        new LanguageTag($tag);
    }

    public static function invalidLanguageTagProvider(): array
    {
        return [
            'single character' => ['e'],
            'numbers only' => ['123'],
            'starts with hyphen' => ['-en'],
            'ends with hyphen' => ['en-'],
            'double hyphen' => ['en--GB'],
            'special characters' => ['en_GB'],
            'spaces' => ['en GB'],
            'too many characters in language' => ['english-GB'],
        ];
    }

    public function test_getLanguage_returnsLanguageSubtag(): void
    {
        $tag = new LanguageTag('en-GB');

        $this->assertSame('en', $tag->getLanguage());
    }

    public function test_getLanguage_withLanguageOnly_returnsLanguage(): void
    {
        $tag = new LanguageTag('fr');

        $this->assertSame('fr', $tag->getLanguage());
    }

    public function test_getRegion_withRegion_returnsRegion(): void
    {
        $tag = new LanguageTag('en-GB');

        $this->assertSame('GB', $tag->getRegion());
    }

    public function test_getRegion_withNumericRegion_returnsRegion(): void
    {
        $tag = new LanguageTag('es-419');

        $this->assertSame('419', $tag->getRegion());
    }

    public function test_getRegion_withoutRegion_returnsNull(): void
    {
        $tag = new LanguageTag('en');

        $this->assertNull($tag->getRegion());
    }

    public function test_getRegion_withScriptAndRegion_returnsRegion(): void
    {
        $tag = new LanguageTag('zh-Hans-CN');

        $this->assertSame('CN', $tag->getRegion());
    }

    public function test_serializationRoundTrip_preservesValue(): void
    {
        $original = new LanguageTag('en-GB');
        $data = $original->toData();
        $restored = LanguageTag::fromData($data);

        $this->assertSame($original->tag, $restored->tag);
        $this->assertSame('en-GB', $data);
    }

    public function test_serializationRoundTrip_withComplexTag_preservesValue(): void
    {
        $original = new LanguageTag('zh-Hans-CN');
        $data = $original->toData();
        $restored = LanguageTag::fromData($data);

        $this->assertSame($original->tag, $restored->tag);
        $this->assertSame('zh-Hans-CN', $data);
        $this->assertSame('zh', $restored->getLanguage());
        $this->assertSame('CN', $restored->getRegion());
    }
}
