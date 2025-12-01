<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\StoredCard;

use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\StoredCard\CreateStoredCardRequest;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CreateStoredCardRequest message.
 */
class CreateStoredCardRequestTest extends TestCase
{
    public function test_construct_withStoredCardObject_createsInstance(): void
    {
        // Arrange
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s123',
            hostedCard: 'https://api.example.com/hosted-cards/hc456',
        );

        // Act
        $request = new CreateStoredCardRequest($storedCard);

        // Assert
        $this->assertSame($storedCard, $request->getStoredCard());
    }

    public function test_construct_withStoredCardArray_normalizesToObject(): void
    {
        // Arrange
        $storedCardData = [
            'shopper' => 'https://api.example.com/shoppers/s789',
            'hostedCard' => 'https://api.example.com/hosted-cards/hc789',
            'customReference' => 'ref-123',
        ];

        // Act
        $request = new CreateStoredCardRequest($storedCardData);

        // Assert
        $this->assertInstanceOf(StoredCard::class, $request->getStoredCard());
        $this->assertSame('ref-123', $request->getStoredCard()->customReference);
    }

    public function test_construct_withoutShopper_throwsException(): void
    {
        // Arrange
        $storedCard = new StoredCard(
            hostedCard: 'https://api.example.com/hosted-cards/hc123',
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Shopper URL is required to create a stored card');

        // Act
        new CreateStoredCardRequest($storedCard);
    }

    public function test_construct_withoutHostedCardOrCard_throwsException(): void
    {
        // Arrange
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s123',
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Either hostedCard URL or card data is required to create a stored card');

        // Act
        new CreateStoredCardRequest($storedCard);
    }

    public function test_construct_withCardData_isValid(): void
    {
        // Arrange
        $card = new Card(
            last4: '1111',
            bin: '411111',
        );
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s123',
            card: $card,
        );

        // Act
        $request = new CreateStoredCardRequest($storedCard);

        // Assert
        $this->assertSame($storedCard, $request->getStoredCard());
    }

    public function test_build_withDefaultFactory_returnsValidRequest(): void
    {
        // Arrange
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s123',
            hostedCard: 'https://api.example.com/hosted-cards/hc456',
            customReference: 'customer-789',
        );
        $request = new CreateStoredCardRequest($storedCard);

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertSame('POST', $psrRequest->getMethod());
        $this->assertSame('/stored-cards', (string) $psrRequest->getUri());

        // Verify body content
        $body = (string) $psrRequest->getBody();
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('https://api.example.com/shoppers/s123', $decoded['shopper']);
        $this->assertSame('https://api.example.com/hosted-cards/hc456', $decoded['hostedCard']);
        $this->assertSame('customer-789', $decoded['customReference']);
    }

    public function test_build_canBeCalledMultipleTimes(): void
    {
        // Arrange
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s123',
            hostedCard: 'https://api.example.com/hosted-cards/hc123',
        );
        $request = new CreateStoredCardRequest($storedCard);

        // Act
        $psrRequest1 = $request->build();
        $psrRequest2 = $request->build();

        // Assert
        $this->assertNotSame($psrRequest1, $psrRequest2);
        $this->assertEquals((string) $psrRequest1->getBody(), (string) $psrRequest2->getBody());
    }

    public function test_build_withCustomFields_includesThem(): void
    {
        // Arrange
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s123',
            hostedCard: 'https://api.example.com/hosted-cards/hc123',
            customFields: new CustomFields([
                'subscriptionPlan' => 'premium',
                'autoRenew' => 'true',
            ]),
        );
        $request = new CreateStoredCardRequest($storedCard);

        // Act
        $psrRequest = $request->build();

        // Assert
        $body = (string) $psrRequest->getBody();
        $decoded = json_decode($body, true);
        $this->assertArrayHasKey('customFields', $decoded);
        $this->assertSame('premium', $decoded['customFields']['subscriptionPlan']);
        $this->assertSame('true', $decoded['customFields']['autoRenew']);
    }

    public function test_build_doesNotIncludeNullFields(): void
    {
        // Arrange
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s123',
            hostedCard: 'https://api.example.com/hosted-cards/hc123',
        );
        $request = new CreateStoredCardRequest($storedCard);

        // Act
        $psrRequest = $request->build();

        // Assert
        $body = (string) $psrRequest->getBody();
        $decoded = json_decode($body, true);
        $this->assertArrayHasKey('shopper', $decoded);
        $this->assertArrayHasKey('hostedCard', $decoded);
        $this->assertArrayNotHasKey('customReference', $decoded);
        $this->assertArrayNotHasKey('customFields', $decoded);
        $this->assertArrayNotHasKey('id', $decoded);
        $this->assertArrayNotHasKey('href', $decoded);
    }

    public function test_build_withCardData_serializesCard(): void
    {
        // Arrange
        $card = new Card(
            last4: '4444',
            bin: '555555',
        );
        $storedCard = new StoredCard(
            shopper: 'https://api.example.com/shoppers/s123',
            card: $card,
        );
        $request = new CreateStoredCardRequest($storedCard);

        // Act
        $psrRequest = $request->build();

        // Assert
        $body = (string) $psrRequest->getBody();
        $decoded = json_decode($body, true);
        $this->assertArrayHasKey('card', $decoded);
        $this->assertSame('4444', $decoded['card']['last4']);
        $this->assertSame('555555', $decoded['card']['bin']);
    }
}
