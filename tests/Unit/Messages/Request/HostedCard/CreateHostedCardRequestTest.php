<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\HostedCard;

use Academe\Elavon\Epg\Psr7\Dtos\Card;
use Academe\Elavon\Epg\Psr7\Dtos\HostedCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\HostedCard\CreateHostedCardRequest;
use Academe\Elavon\Epg\Psr7\ValueObjects\CustomFields;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CreateHostedCardRequest message.
 */
class CreateHostedCardRequestTest extends TestCase
{
    public function test_construct_withHostedCardObject_createsInstance(): void
    {
        // Arrange
        $card = new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );
        $hostedCard = new HostedCard(card: $card);

        // Act
        $request = new CreateHostedCardRequest($hostedCard);

        // Assert
        $this->assertSame($hostedCard, $request->hostedCard);
    }

    public function test_fromData_withHostedCardArray_normalizesToObject(): void
    {
        // Arrange
        $hostedCardData = [
            'card' => [
                'number' => '5555555555554444',
                'securityCode' => '456',
                'expirationMonth' => 6,
                'expirationYear' => 2026,
            ],
            'customReference' => 'order-123',
        ];

        // Act
        $request = CreateHostedCardRequest::fromData(['hostedCard' => $hostedCardData]);

        // Assert
        $this->assertInstanceOf(HostedCard::class, $request->hostedCard);
        $this->assertSame('order-123', $request->hostedCard->customReference);
    }

    public function test_construct_withoutCard_throwsException(): void
    {
        // Arrange
        $hostedCard = new HostedCard(customReference: 'ref-123');

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Card data is required to create a hosted card');

        // Act
        new CreateHostedCardRequest($hostedCard);
    }

    public function test_build_withDefaultFactory_returnsValidRequest(): void
    {
        // Arrange
        $card = new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
            holderName: 'John Doe',
        );
        $hostedCard = new HostedCard(
            card: $card,
            customReference: 'order-789',
        );
        $request = new CreateHostedCardRequest($hostedCard);

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertSame('POST', $psrRequest->getMethod());
        $this->assertSame('/hosted-cards', (string) $psrRequest->getUri());

        // Verify body content
        $body = (string) $psrRequest->getBody();
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('card', $decoded);
        $this->assertSame('4111111111111111', $decoded['card']['number']);
        $this->assertSame('order-789', $decoded['customReference']);
    }

    public function test_build_canBeCalledMultipleTimes(): void
    {
        // Arrange
        $card = new Card(number: '4111111111111111');
        $hostedCard = new HostedCard(card: $card);
        $request = new CreateHostedCardRequest($hostedCard);

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
        $card = new Card(
            number: '4111111111111111',
            securityCode: '123',
            expirationMonth: 12,
            expirationYear: 2025,
        );
        $hostedCard = new HostedCard(
            card: $card,
            customFields: new CustomFields([
                'orderId' => 'ORD-12345',
                'customerId' => 'CUST-67890',
            ]),
        );
        $request = new CreateHostedCardRequest($hostedCard);

        // Act
        $psrRequest = $request->build();

        // Assert
        $body = (string) $psrRequest->getBody();
        $decoded = json_decode($body, true);
        $this->assertArrayHasKey('customFields', $decoded);
        $this->assertSame('ORD-12345', $decoded['customFields']['orderId']);
        $this->assertSame('CUST-67890', $decoded['customFields']['customerId']);
    }

    public function test_build_doesNotIncludeNullFields(): void
    {
        // Arrange
        $card = new Card(
            number: '4111111111111111',
            expirationMonth: 12,
            expirationYear: 2025,
        );
        $hostedCard = new HostedCard(card: $card);
        $request = new CreateHostedCardRequest($hostedCard);

        // Act
        $psrRequest = $request->build();

        // Assert
        $body = (string) $psrRequest->getBody();
        $decoded = json_decode($body, true);
        $this->assertArrayHasKey('card', $decoded);
        $this->assertArrayNotHasKey('customReference', $decoded);
        $this->assertArrayNotHasKey('customFields', $decoded);
        $this->assertArrayNotHasKey('id', $decoded);
        $this->assertArrayNotHasKey('href', $decoded);
    }

    public function test_build_serializesCardCorrectly(): void
    {
        // Arrange
        $card = new Card(
            number: '378282246310005',
            securityCode: '1234',
            expirationMonth: 3,
            expirationYear: 2027,
            holderName: 'Jane Smith',
        );
        $hostedCard = new HostedCard(card: $card);
        $request = new CreateHostedCardRequest($hostedCard);

        // Act
        $psrRequest = $request->build();

        // Assert
        $body = (string) $psrRequest->getBody();
        $decoded = json_decode($body, true);
        $this->assertSame('378282246310005', $decoded['card']['number']);
        $this->assertSame('1234', $decoded['card']['securityCode']);
        $this->assertSame(3, $decoded['card']['expirationMonth']);
        $this->assertSame(2027, $decoded['card']['expirationYear']);
        $this->assertSame('Jane Smith', $decoded['card']['holderName']);
    }
}
