<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\HostedCard;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\HostedCard\RetrieveHostedCardRequest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RetrieveHostedCardRequest message.
 */
class RetrieveHostedCardRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        // Act
        $request = new RetrieveHostedCardRequest('hc123');

        // Assert
        $this->assertSame('hc123', $request->getHostedCardId());
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Hosted card ID cannot be empty');

        // Act
        new RetrieveHostedCardRequest('');
    }

    public function test_build_withDefaultFactory_returnsValidRequest(): void
    {
        // Arrange
        $request = new RetrieveHostedCardRequest('hc456');

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertSame('GET', $psrRequest->getMethod());
        $this->assertSame('/hosted-cards/hc456', (string) $psrRequest->getUri());
        $this->assertSame(['application/json'], $psrRequest->getHeader('Accept'));
    }

    public function test_build_canBeCalledMultipleTimes(): void
    {
        // Arrange
        $request = new RetrieveHostedCardRequest('hc999');

        // Act
        $psrRequest1 = $request->build();
        $psrRequest2 = $request->build();

        // Assert
        $this->assertNotSame($psrRequest1, $psrRequest2);
        $this->assertSame((string) $psrRequest1->getUri(), (string) $psrRequest2->getUri());
    }

    public function test_build_withSpecialCharactersInId_includesThemInUri(): void
    {
        // Arrange
        $request = new RetrieveHostedCardRequest('hc-abc-123_xyz');

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertStringContainsString('hc-abc-123_xyz', (string) $psrRequest->getUri());
    }

    public function test_build_hasNoBody(): void
    {
        // Arrange
        $request = new RetrieveHostedCardRequest('hc123');

        // Act
        $psrRequest = $request->build();

        // Assert
        $body = (string) $psrRequest->getBody();
        $this->assertEmpty($body);
    }
}
