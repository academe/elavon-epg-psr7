<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\UpdateTransactionRequest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for UpdateTransactionRequest message.
 */
class UpdateTransactionRequestTest extends TestCase
{
    public function test_construct_withEmptyTransactionId_throwsException(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction ID cannot be empty');

        // Act
        new UpdateTransactionRequest('', new Transaction());
    }

    public function test_construct_withValidTransactionId_createsInstance(): void
    {
        // Arrange & Act
        $request = new UpdateTransactionRequest('txn123', new Transaction());

        // Assert
        $this->assertSame('txn123', $request->getTransactionId());
    }

    public function test_build_withTransactionObject_createsPatchRequest(): void
    {
        // Arrange
        $updates = new Transaction(
            description: 'Updated description'
        );
        $request = new UpdateTransactionRequest('txn123', $updates);

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertSame('PATCH', $psrRequest->getMethod());
        $this->assertStringEndsWith('/transactions/txn123', (string) $psrRequest->getUri());
    }

    public function test_build_withTransactionArray_createsPatchRequest(): void
    {
        // Arrange
        $updates = [
            'description' => 'Updated description',
            'customReference' => 'REF-456',
        ];
        $request = new UpdateTransactionRequest('txn123', $updates);

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertSame('PATCH', $psrRequest->getMethod());
        $this->assertStringEndsWith('/transactions/txn123', (string) $psrRequest->getUri());
    }

    public function test_build_serializesOnlyNonNullFields(): void
    {
        // Arrange
        $updates = new Transaction(
            description: 'Updated description'
            // Other fields are null
        );
        $request = new UpdateTransactionRequest('txn123', $updates);

        // Act
        $psrRequest = $request->build();
        $body = (string) $psrRequest->getBody();
        $data = json_decode($body, true);

        // Assert
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('Updated description', $data['description']);
        // Should not include null fields
        $this->assertArrayNotHasKey('id', $data);
        $this->assertArrayNotHasKey('state', $data);
    }

    public function test_getUpdates_withTransactionObject_returnsTransaction(): void
    {
        // Arrange
        $updates = new Transaction(description: 'Test');
        $request = new UpdateTransactionRequest('txn123', $updates);

        // Act
        $result = $request->getUpdates();

        // Assert
        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertSame('Test', $result->description);
    }

    public function test_getUpdates_withArray_returnsTransaction(): void
    {
        // Arrange
        $updates = ['description' => 'Test'];
        $request = new UpdateTransactionRequest('txn123', $updates);

        // Act
        $result = $request->getUpdates();

        // Assert
        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertSame('Test', $result->description);
    }

    public function test_build_withMultipleUpdates_includesAllFields(): void
    {
        // Arrange
        $updates = new Transaction(
            description: 'Updated description',
            customReference: 'NEW-REF',
        );
        $request = new UpdateTransactionRequest('txn123', $updates);

        // Act
        $psrRequest = $request->build();
        $body = (string) $psrRequest->getBody();
        $data = json_decode($body, true);

        // Assert
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('customReference', $data);
        $this->assertSame('Updated description', $data['description']);
        $this->assertSame('NEW-REF', $data['customReference']);
    }

    public function test_getTransactionId_returnsCorrectId(): void
    {
        // Arrange
        $request = new UpdateTransactionRequest('my-txn-id', new Transaction());

        // Act & Assert
        $this->assertSame('my-txn-id', $request->getTransactionId());
    }
}
