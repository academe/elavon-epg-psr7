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
    public function test_fromData_withMissingTransactionIdKey_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required key 'transactionId' in data");

        UpdateTransactionRequest::fromData(['transaction' => []]);
    }

    public function test_fromData_withMissingTransactionKey_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Missing required key 'transaction' in data");

        UpdateTransactionRequest::fromData(['transactionId' => 'txn123']);
    }

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
        $this->assertSame('txn123', $request->transactionId);
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

    public function test_fromData_withTransactionArray_createsPatchRequest(): void
    {
        // Arrange
        $request = UpdateTransactionRequest::fromData([
            'transactionId' => 'txn123',
            'transaction' => [
                'description' => 'Updated description',
                'customReference' => 'REF-456',
            ],
        ]);

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

    public function test_transaction_property_withTransactionObject_returnsTransaction(): void
    {
        // Arrange
        $updates = new Transaction(description: 'Test');
        $request = new UpdateTransactionRequest('txn123', $updates);

        // Assert
        $this->assertInstanceOf(Transaction::class, $request->transaction);
        $this->assertSame('Test', $request->transaction->description);
    }

    public function test_fromData_withTransactionArray_hydratesTransaction(): void
    {
        // Arrange
        $request = UpdateTransactionRequest::fromData([
            'transactionId' => 'txn123',
            'transaction' => ['description' => 'Test'],
        ]);

        // Assert
        $this->assertInstanceOf(Transaction::class, $request->transaction);
        $this->assertSame('Test', $request->transaction->description);
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

    public function test_transactionId_property_returnsCorrectId(): void
    {
        // Arrange
        $request = new UpdateTransactionRequest('my-txn-id', new Transaction());

        // Act & Assert
        $this->assertSame('my-txn-id', $request->transactionId);
    }

    public function test_fromData_withTransactionObject_preservesObject(): void
    {
        // Arrange
        $transaction = new Transaction(description: 'Original');
        $request = UpdateTransactionRequest::fromData([
            'transactionId' => 'txn123',
            'transaction' => $transaction,
        ]);

        // Assert
        $this->assertSame($transaction, $request->transaction);
    }
}