<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\StoredCard;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\StoredCard\RetrieveStoredCardRequest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RetrieveStoredCardRequest message.
 */
class RetrieveStoredCardRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        // Act
        $request = new RetrieveStoredCardRequest('sc123');

        // Assert
        $this->assertSame('sc123', $request->storedCardId);
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stored card ID cannot be empty');

        // Act
        new RetrieveStoredCardRequest('');
    }

    public function test_build_createsValidRequest(): void
    {
        // Arrange
        $request = new RetrieveStoredCardRequest('sc456');

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertSame('GET', $psrRequest->getMethod());
        $this->assertSame('/stored-cards/sc456', (string) $psrRequest->getUri());
    }

    public function test_build_hasNoBody(): void
    {
        // Arrange
        $request = new RetrieveStoredCardRequest('sc789');

        // Act
        $psrRequest = $request->build();
        $body = (string) $psrRequest->getBody();

        // Assert
        $this->assertSame('', $body);
    }

    public function test_build_multipleCalls_returnsSeparateInstances(): void
    {
        // Arrange
        $request = new RetrieveStoredCardRequest('sc111');

        // Act
        $psrRequest1 = $request->build();
        $psrRequest2 = $request->build();

        // Assert
        $this->assertNotSame($psrRequest1, $psrRequest2);
        $this->assertEquals($psrRequest1->getUri(), $psrRequest2->getUri());
    }

    public function test_getStoredCardId_returnsCorrectId(): void
    {
        // Arrange
        $request = new RetrieveStoredCardRequest('sc-test-id-222');

        // Act & Assert
        $this->assertSame('sc-test-id-222', $request->storedCardId);
    }
}
