<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\StoredCard;

use Academe\Elavon\Epg\Psr7\Dtos\StoredCard;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\StoredCard\UpdateStoredCardRequest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for UpdateStoredCardRequest message.
 */
class UpdateStoredCardRequestTest extends TestCase
{
    public function test_construct_withValidData_createsInstance(): void
    {
        // Arrange
        $updates = new StoredCard(customReference: 'new-ref-123');

        // Act
        $request = new UpdateStoredCardRequest('sc123', $updates);

        // Assert
        $this->assertSame('sc123', $request->getStoredCardId());
        $this->assertSame($updates, $request->getUpdates());
    }

    public function test_construct_withArrayUpdates_normalizesToObject(): void
    {
        // Arrange
        $updatesArray = [
            'customReference' => 'updated-reference',
            'customFields' => ['status' => 'active'],
        ];

        // Act
        $request = new UpdateStoredCardRequest('sc456', $updatesArray);

        // Assert
        $this->assertInstanceOf(StoredCard::class, $request->getUpdates());
        $this->assertSame('updated-reference', $request->getUpdates()->customReference);
        $this->assertSame('active', $request->getUpdates()->customFields['status']);
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        // Arrange
        $updates = new StoredCard(customReference: 'ref');

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stored card ID cannot be empty');

        // Act
        new UpdateStoredCardRequest('', $updates);
    }

    public function test_build_createsValidRequest(): void
    {
        // Arrange
        $updates = new StoredCard(
            customReference: 'order-789',
            customFields: ['tier' => 'platinum'],
        );
        $request = new UpdateStoredCardRequest('sc789', $updates);

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertSame('PATCH', $psrRequest->getMethod());
        $this->assertSame('/stored-cards/sc789', (string) $psrRequest->getUri());
        $this->assertSame('application/json', $psrRequest->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $psrRequest->getHeaderLine('Accept'));
    }

    public function test_build_serializesUpdatesToJson(): void
    {
        // Arrange
        $updates = new StoredCard(
            customReference: 'subscription-123',
            customFields: [
                'planType' => 'annual',
                'autoRenew' => 'yes',
            ],
        );
        $request = new UpdateStoredCardRequest('sc999', $updates);

        // Act
        $psrRequest = $request->build();
        $body = (string) $psrRequest->getBody();
        $data = json_decode($body, true);

        // Assert
        $this->assertSame('subscription-123', $data['customReference']);
        $this->assertSame('annual', $data['customFields']['planType']);
        $this->assertSame('yes', $data['customFields']['autoRenew']);
    }

    public function test_build_multipleCalls_returnsSeparateInstances(): void
    {
        // Arrange
        $updates = new StoredCard(customReference: 'ref');
        $request = new UpdateStoredCardRequest('sc222', $updates);

        // Act
        $psrRequest1 = $request->build();
        $psrRequest2 = $request->build();

        // Assert
        $this->assertNotSame($psrRequest1, $psrRequest2);
        $this->assertEquals($psrRequest1->getUri(), $psrRequest2->getUri());
    }

    public function test_build_onlyIncludesProvidedFields(): void
    {
        // Arrange - only update customReference, not other fields
        $updates = new StoredCard(customReference: 'minimal-update');
        $request = new UpdateStoredCardRequest('sc333', $updates);

        // Act
        $psrRequest = $request->build();
        $body = (string) $psrRequest->getBody();
        $data = json_decode($body, true);

        // Assert
        $this->assertArrayHasKey('customReference', $data);
        $this->assertArrayNotHasKey('shopper', $data);
        $this->assertArrayNotHasKey('hostedCard', $data);
        $this->assertArrayNotHasKey('card', $data);
    }

    public function test_getStoredCardId_returnsCorrectId(): void
    {
        // Arrange
        $updates = new StoredCard(customReference: 'ref');
        $request = new UpdateStoredCardRequest('sc-test-id-456', $updates);

        // Act & Assert
        $this->assertSame('sc-test-id-456', $request->getStoredCardId());
    }

    public function test_getUpdates_returnsCorrectObject(): void
    {
        // Arrange
        $updates = new StoredCard(
            customReference: 'get-updates-test',
            customFields: ['key' => 'value'],
        );
        $request = new UpdateStoredCardRequest('sc555', $updates);

        // Act
        $retrievedUpdates = $request->getUpdates();

        // Assert
        $this->assertSame($updates, $retrievedUpdates);
        $this->assertSame('get-updates-test', $retrievedUpdates->customReference);
    }
}
