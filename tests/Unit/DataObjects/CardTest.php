<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\DataObjects;

use Academe\Elavon\Epg\Psr7\DataObjects\Card;
use Academe\Elavon\Epg\Psr7\Enums\CardScheme;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Card DTO.
 */
class CardTest extends TestCase
{
    public function test_construct_withValidRequestData_createsInstance(): void
    {
        // Arrange & Act
        $card = new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
            holderName: 'John Doe',
        );

        // Assert
        $this->assertSame('4111111111111111', $card->number);
        $this->assertSame('123', $card->securityCode);
        $this->assertSame(12, $card->expirationMonth);
        $this->assertSame(2025, $card->expirationYear);
        $this->assertSame('John Doe', $card->holderName);
        $this->assertNull($card->last4);
        $this->assertNull($card->bin);
        $this->assertNull($card->scheme);
        $this->assertNull($card->fingerprint);
    }

    public function test_construct_withValidResponseData_createsInstance(): void
    {
        // Arrange & Act
        $card = new Card(
            last4: '1111',
            bin: '411111',
            scheme: CardScheme::VISA,
            fingerprint: 'abc123',
        );

        // Assert
        $this->assertSame('1111', $card->last4);
        $this->assertSame('411111', $card->bin);
        $this->assertSame(CardScheme::VISA, $card->scheme);
        $this->assertSame('abc123', $card->fingerprint);
        $this->assertNull($card->number);
        $this->assertNull($card->securityCode);
        $this->assertNull($card->expirationMonth);
        $this->assertNull($card->expirationYear);
        $this->assertNull($card->holderName);
    }

    public function test_construct_withCardNumberContainingSpaces_isValid(): void
    {
        // Arrange & Act
        $card = new Card(
            number: '4111 1111 1111 1111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );

        // Assert
        $this->assertSame('4111 1111 1111 1111', $card->number);
    }

    public function test_construct_withCardNumberContainingDashes_isValid(): void
    {
        // Arrange & Act
        $card = new Card(
            number: '4111-1111-1111-1111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );

        // Assert
        $this->assertSame('4111-1111-1111-1111', $card->number);
    }

    public function test_construct_withFourDigitSecurityCode_isValid(): void
    {
        // Arrange & Act
        $card = new Card(
            number: '378282246310005',
            securityCode: '1234',
            expirationMonth: 12,
            expirationYear: 2025,
        );

        // Assert
        $this->assertSame('1234', $card->securityCode);
    }

    public function test_construct_withInvalidCardNumberTooShort_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Card number must contain 13-19 digits');

        // Act
        new Card(
            number: '123456789012',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );
    }

    public function test_construct_withInvalidCardNumberTooLong_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Card number must contain 13-19 digits');

        // Act
        new Card(
            number: '12345678901234567890',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );
    }

    public function test_construct_withInvalidSecurityCodeTooShort_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Security code must be 3 or 4 digits');

        // Act
        new Card(
            number: '4111111111111111',
            securityCode: '12',
            expirationMonth: 12,
            expirationYear: 2025,
        );
    }

    public function test_construct_withInvalidSecurityCodeTooLong_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Security code must be 3 or 4 digits');

        // Act
        new Card(
            number: '4111111111111111',
            securityCode: '12345',
            expirationMonth: 12,
            expirationYear: 2025,
        );
    }

    public function test_construct_withInvalidSecurityCodeNonNumeric_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Security code must be 3 or 4 digits');

        // Act
        new Card(
            number: '4111111111111111',
            securityCode: 'abc',
            expirationMonth: 12,
            expirationYear: 2025,
        );
    }

    public function test_construct_withInvalidExpirationMonthTooLow_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expiration month must be between 1 and 12, got: 0');

        // Act
        new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 0,
            expirationYear: 2025,
        );
    }

    public function test_construct_withInvalidExpirationMonthTooHigh_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expiration month must be between 1 and 12, got: 13');

        // Act
        new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 13,
            expirationYear: 2025,
        );
    }

    public function test_construct_withInvalidExpirationYearTooLow_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expiration year must be between 2000 and 2099, got: 1999');

        // Act
        new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 1999,
        );
    }

    public function test_construct_withInvalidExpirationYearTooHigh_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expiration year must be between 2000 and 2099, got: 2100');

        // Act
        new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2100,
        );
    }

    public function test_fromArray_withRequestData_createsInstance(): void
    {
        // Arrange
        $data = [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
            'holderName' => 'John Doe',
        ];

        // Act
        $card = Card::fromArray($data);

        // Assert
        $this->assertSame('4111111111111111', $card->number);
        $this->assertSame('123', $card->securityCode);
        $this->assertSame(12, $card->expirationMonth);
        $this->assertSame(2025, $card->expirationYear);
        $this->assertSame('John Doe', $card->holderName);
    }

    public function test_fromArray_withResponseData_createsInstance(): void
    {
        // Arrange
        $data = [
            'last4' => '1111',
            'bin' => '411111',
            'scheme' => 'Visa',
            'fingerprint' => 'abc123',
        ];

        // Act
        $card = Card::fromArray($data);

        // Assert
        $this->assertSame('1111', $card->last4);
        $this->assertSame('411111', $card->bin);
        $this->assertSame(CardScheme::VISA, $card->scheme);
        $this->assertSame('abc123', $card->fingerprint);
    }

    public function test_fromArray_withInvalidScheme_throwsException(): void
    {
        // Arrange
        $data = [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
            'scheme' => 'InvalidScheme',
        ];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid card scheme: InvalidScheme');

        // Act
        Card::fromArray($data);
    }

    public function test_fromArray_withEmptyArray_createsEmptyCard(): void
    {
        // Arrange
        $data = [];

        // Act
        $card = Card::fromArray($data);

        // Assert
        $this->assertNull($card->number);
        $this->assertNull($card->securityCode);
        $this->assertNull($card->expirationMonth);
        $this->assertNull($card->expirationYear);
        $this->assertNull($card->holderName);
        $this->assertNull($card->last4);
        $this->assertNull($card->bin);
        $this->assertNull($card->scheme);
        $this->assertNull($card->fingerprint);
    }

    public function test_toArray_withRequestData_returnsArray(): void
    {
        // Arrange
        $card = new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
            holderName: 'John Doe',
        );

        // Act
        $array = $card->toArray();

        // Assert
        $this->assertSame([
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
            'holderName' => 'John Doe',
        ], $array);
    }

    public function test_toArray_withResponseData_returnsArray(): void
    {
        // Arrange
        $card = new Card(
            last4: '1111',
            bin: '411111',
            scheme: CardScheme::VISA,
            fingerprint: 'abc123',
        );

        // Act
        $array = $card->toArray();

        // Assert
        $this->assertSame([
            'last4' => '1111',
            'bin' => '411111',
            'scheme' => 'Visa',
            'fingerprint' => 'abc123',
        ], $array);
    }

    public function test_toArray_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $card = new Card(
            number: '4111111111111111',
            expirationMonth: 12,
            expirationYear: 2025,
        );

        // Act
        $array = $card->toArray();

        // Assert
        $this->assertSame([
            'number' => '4111111111111111',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
        ], $array);
        $this->assertArrayNotHasKey('securityCode', $array);
        $this->assertArrayNotHasKey('holderName', $array);
        $this->assertArrayNotHasKey('last4', $array);
    }

    public function test_toArray_withEmptyCard_returnsEmptyArray(): void
    {
        // Arrange
        $card = new Card();

        // Act
        $array = $card->toArray();

        // Assert
        $this->assertSame([], $array);
    }

    public function test_roundTrip_fromArrayToArray_preservesData(): void
    {
        // Arrange
        $originalData = [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
            'holderName' => 'John Doe',
        ];

        // Act
        $card = Card::fromArray($originalData);
        $resultData = $card->toArray();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $card = new Card(number: '4111111111111111');

        // Act & Assert
        $reflection = new \ReflectionProperty($card, 'number');
        $this->assertTrue($reflection->isReadOnly());
    }
}