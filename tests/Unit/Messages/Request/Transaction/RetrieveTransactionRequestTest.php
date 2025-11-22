<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\RetrieveTransactionRequest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RetrieveTransactionRequest message.
 */
class RetrieveTransactionRequestTest extends TestCase
{
    public function test_construct_withEmptyTransactionId_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction ID cannot be empty');

        // Act
        new RetrieveTransactionRequest('');
    }

    public function test_construct_withValidTransactionId_createsInstance(): void
    {
        // Arrange & Act
        $request = new RetrieveTransactionRequest('txn123');

        // Assert
        $this->assertSame('txn123', $request->getTransactionId());
    }

    public function test_build_createsGetRequest(): void
    {
        // Arrange
        $request = new RetrieveTransactionRequest('txn123');

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertSame('GET', $psrRequest->getMethod());
        $this->assertStringEndsWith('/transactions/txn123', (string) $psrRequest->getUri());
    }

    public function test_build_hasNoBody(): void
    {
        // Arrange
        $request = new RetrieveTransactionRequest('txn123');

        // Act
        $psrRequest = $request->build();
        $body = (string) $psrRequest->getBody();

        // Assert
        $this->assertEmpty($body);
    }

    public function test_getTransactionId_returnsCorrectId(): void
    {
        // Arrange
        $request = new RetrieveTransactionRequest('my-txn-id');

        // Act & Assert
        $this->assertSame('my-txn-id', $request->getTransactionId());
    }

    public function test_build_withDifferentTransactionIds_createsCorrectUrls(): void
    {
        // Arrange & Act & Assert
        $request1 = new RetrieveTransactionRequest('txn-123');
        $this->assertStringEndsWith('/transactions/txn-123', (string) $request1->build()->getUri());

        $request2 = new RetrieveTransactionRequest('abc-xyz-789');
        $this->assertStringEndsWith('/transactions/abc-xyz-789', (string) $request2->build()->getUri());
    }
}
