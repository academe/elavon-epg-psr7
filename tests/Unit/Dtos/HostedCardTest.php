<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Dtos;

use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for HostedCard DTO.
 */
class HostedCardTest extends TestCase
{
    public function test_construct_withCardObject_createsInstance(): void
    {
        // Arrange
        $card = new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );

        // Act
        $hostedCard = new HostedCard(card: $card);

        // Assert
        $this->assertSame($card, $hostedCard->card);
    }

    public function test_construct_withCardArray_normalizesToCardObject(): void
    {
        // Arrange
        $cardData = [
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
        ];

        // Act
        $hostedCard = new HostedCard(card: $cardData);

        // Assert
        $this->assertInstanceOf(Card::class, $hostedCard->card);
        $this->assertSame('4111111111111111', $hostedCard->card->number);
        $this->assertSame('123', $hostedCard->card->securityCode);
        $this->assertSame(12, $hostedCard->card->expirationMonth);
        $this->assertSame(2025, $hostedCard->card->expirationYear);
    }

    public function test_construct_withNullCard_createsInstance(): void
    {
        // Act
        $hostedCard = new HostedCard(card: null);

        // Assert
        $this->assertNull($hostedCard->card);
    }

    public function test_construct_withResponseFields_createsInstance(): void
    {
        // Act
        $hostedCard = new HostedCard(
            href: 'https://api.example.com/hosted-cards/hc123',
            id: 'hc123',
            createdAt: '2025-01-01T00:00:00Z',
            modifiedAt: '2025-01-02T00:00:00Z',
            expiresAt: '2025-01-31T23:59:59Z',
            merchant: 'https://api.example.com/merchants/m123',
            doVerify: true,
        );

        // Assert
        $this->assertSame('https://api.example.com/hosted-cards/hc123', $hostedCard->href);
        $this->assertSame('hc123', $hostedCard->id);
        $this->assertSame('2025-01-01T00:00:00Z', $hostedCard->createdAt);
        $this->assertSame('2025-01-02T00:00:00Z', $hostedCard->modifiedAt);
        $this->assertSame('2025-01-31T23:59:59Z', $hostedCard->expiresAt);
        $this->assertSame('https://api.example.com/merchants/m123', $hostedCard->merchant);
        $this->assertTrue($hostedCard->doVerify);
    }

    public function test_construct_withCustomReference_createsInstance(): void
    {
        // Act
        $hostedCard = new HostedCard(
            customReference: 'order-12345',
        );

        // Assert
        $this->assertSame('order-12345', $hostedCard->customReference);
    }

    public function test_construct_withCustomReferenceAt255Characters_isValid(): void
    {
        // Arrange
        $maxReference = str_repeat('a', 255);

        // Act
        $hostedCard = new HostedCard(
            customReference: $maxReference,
        );

        // Assert
        $this->assertSame($maxReference, $hostedCard->customReference);
    }

    public function test_construct_withCustomReferenceTooLong_throwsException(): void
    {
        // Arrange
        $tooLongReference = str_repeat('a', 256);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom reference must not exceed 255 characters');

        // Act
        new HostedCard(customReference: $tooLongReference);
    }

    public function test_construct_withCustomFields_createsInstance(): void
    {
        // Arrange
        $customFields = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        // Act
        $hostedCard = new HostedCard(
            customFields: $customFields,
        );

        // Assert
        $this->assertSame($customFields, $hostedCard->customFields);
    }

    public function test_construct_withCustomFieldKeyAt64Characters_isValid(): void
    {
        // Arrange
        $maxKey = str_repeat('a', 64);
        $customFields = [$maxKey => 'value'];

        // Act
        $hostedCard = new HostedCard(
            customFields: $customFields,
        );

        // Assert
        $this->assertSame($customFields, $hostedCard->customFields);
    }

    public function test_construct_withCustomFieldKeyTooLong_throwsException(): void
    {
        // Arrange
        $tooLongKey = str_repeat('a', 65);
        $customFields = [$tooLongKey => 'value'];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field name must not exceed 64 characters');

        // Act
        new HostedCard(customFields: $customFields);
    }

    public function test_construct_withCustomFieldValueAt1024Characters_isValid(): void
    {
        // Arrange
        $maxValue = str_repeat('a', 1024);
        $customFields = ['key' => $maxValue];

        // Act
        $hostedCard = new HostedCard(
            customFields: $customFields,
        );

        // Assert
        $this->assertSame($customFields, $hostedCard->customFields);
    }

    public function test_construct_withCustomFieldValueTooLong_throwsException(): void
    {
        // Arrange
        $tooLongValue = str_repeat('a', 1025);
        $customFields = ['key' => $tooLongValue];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Custom field value must not exceed 1024 characters');

        // Act
        new HostedCard(customFields: $customFields);
    }

    public function test_fromData_withCardData_createsInstance(): void
    {
        // Arrange
        $data = [
            'card' => [
                'number' => '4111111111111111',
                'securityCode' => '123',
                'expirationMonth' => 12,
                'expirationYear' => 2025,
            ],
            'customReference' => 'order-789',
        ];

        // Act
        $hostedCard = HostedCard::fromData($data);

        // Assert
        $this->assertInstanceOf(Card::class, $hostedCard->card);
        $this->assertSame('4111111111111111', $hostedCard->card->number);
        $this->assertSame('order-789', $hostedCard->customReference);
    }

    public function test_fromData_withResponseData_createsInstance(): void
    {
        // Arrange
        $data = [
            'href' => 'https://api.example.com/hosted-cards/hc456',
            'id' => 'hc456',
            'createdAt' => '2025-01-10T12:00:00Z',
            'merchant' => 'https://api.example.com/merchants/m789',
            'doVerify' => false,
            'card' => [
                'last4' => '1111',
                'bin' => '411111',
                'scheme' => 'Visa',
            ],
        ];

        // Act
        $hostedCard = HostedCard::fromData($data);

        // Assert
        $this->assertSame('hc456', $hostedCard->id);
        $this->assertFalse($hostedCard->doVerify);
        $this->assertInstanceOf(Card::class, $hostedCard->card);
        $this->assertSame('1111', $hostedCard->card->last4);
    }

    public function test_toData_withCardObject_returnsArray(): void
    {
        // Arrange
        $card = new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );
        $hostedCard = new HostedCard(
            card: $card,
            customReference: 'ref-123',
        );

        // Act
        $array = $hostedCard->toData();

        // Assert
        $this->assertIsArray($array);
        $this->assertArrayHasKey('card', $array);
        $this->assertSame([
            'number' => '4111111111111111',
            'securityCode' => '123',
            'expirationMonth' => 12,
            'expirationYear' => 2025,
        ], $array['card']);
        $this->assertSame('ref-123', $array['customReference']);
    }

    public function test_toData_withResponseData_returnsArray(): void
    {
        // Arrange
        $hostedCard = new HostedCard(
            href: 'https://api.example.com/hosted-cards/hc789',
            id: 'hc789',
            createdAt: '2025-01-15T08:30:00Z',
            doVerify: true,
        );

        // Act
        $array = $hostedCard->toData();

        // Assert
        $this->assertSame([
            'href' => 'https://api.example.com/hosted-cards/hc789',
            'id' => 'hc789',
            'createdAt' => '2025-01-15T08:30:00Z',
            'doVerify' => true,
        ], $array);
    }

    public function test_toData_onlyIncludesNonNullValues(): void
    {
        // Arrange
        $hostedCard = new HostedCard(
            id: 'hc999',
            customReference: 'ref-999',
        );

        // Act
        $array = $hostedCard->toData();

        // Assert
        $this->assertSame([
            'id' => 'hc999',
            'customReference' => 'ref-999',
        ], $array);
        $this->assertArrayNotHasKey('href', $array);
        $this->assertArrayNotHasKey('createdAt', $array);
        $this->assertArrayNotHasKey('card', $array);
        $this->assertArrayNotHasKey('doVerify', $array);
    }

    public function test_toData_withCustomFields_returnsArray(): void
    {
        // Arrange
        $customFields = [
            'orderNumber' => 'ORD-12345',
            'customerType' => 'premium',
        ];
        $hostedCard = new HostedCard(
            id: 'hc111',
            customFields: $customFields,
        );

        // Act
        $array = $hostedCard->toData();

        // Assert
        $this->assertSame([
            'customFields' => $customFields,
            'id' => 'hc111',
        ], $array);
    }

    public function test_roundTrip_fromDataToData_preservesData(): void
    {
        // Arrange
        $originalData = [
            'card' => [
                'number' => '5555555555554444',
                'securityCode' => '456',
                'expirationMonth' => 6,
                'expirationYear' => 2026,
                'holderName' => 'Jane Doe',
            ],
            'customFields' => [
                'field1' => 'value1',
                'field2' => 'value2',
            ],
            'customReference' => 'order-999',
        ];

        // Act
        $hostedCard = HostedCard::fromData($originalData);
        $resultData = $hostedCard->toData();

        // Assert
        $this->assertSame($originalData, $resultData);
    }

    public function test_properties_areReadonly(): void
    {
        // Arrange
        $hostedCard = new HostedCard(id: 'hc123');

        // Act & Assert
        $reflection = new \ReflectionProperty($hostedCard, 'id');
        $this->assertTrue($reflection->isReadOnly());

        $cardReflection = new \ReflectionProperty($hostedCard, 'card');
        $this->assertTrue($cardReflection->isReadOnly());
    }
}
