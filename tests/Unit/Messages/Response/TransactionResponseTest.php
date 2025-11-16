<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response;

use Academe\Elavon\Epg\Psr7\Dtos\Transaction;
use Academe\Elavon\Epg\Psr7\Enums\CardScheme;
use Academe\Elavon\Epg\Psr7\Enums\Currency;
use Academe\Elavon\Epg\Psr7\Enums\TransactionState;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\TransactionResponse;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Tests for TransactionResponse message.
 */
class TransactionResponseTest extends TestCase
{
    public function test_construct_withValidResponse_createsInstance(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'txn_123',
            'state' => 'authorized',
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'createdAt' => '2025-11-13T10:00:00Z',
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionResponse($psr7Response);

        // Assert
        $this->assertInstanceOf(TransactionResponse::class, $response);
    }

    public function test_fromPsr7Response_withValidResponse_createsInstance(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'txn_123',
            'state' => 'authorized',
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'createdAt' => '2025-11-13T10:00:00Z',
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 200);

        // Act
        $response = TransactionResponse::fromPsr7Response($psr7Response);

        // Assert
        $this->assertInstanceOf(TransactionResponse::class, $response);
    }

    public function test_getTransaction_returnsTransactionObject(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'txn_123',
            'state' => 'authorized',
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'card' => [
                'last4' => '1111',
                'bin' => '411111',
                'scheme' => 'Visa',
            ],
            'createdAt' => '2025-11-13T10:00:00Z',
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 201);
        $response = new TransactionResponse($psr7Response);

        // Act
        $transaction = $response->getTransaction();

        // Assert
        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertSame('txn_123', $transaction->id);
        $this->assertSame(TransactionState::AUTHORIZED, $transaction->state);
        $this->assertSame('99.99', $transaction->total->amount);
        $this->assertSame(Currency::USD, $transaction->total->currency);
        $this->assertSame('1111', $transaction->card->last4);
        $this->assertSame('411111', $transaction->card->bin);
        $this->assertSame(CardScheme::VISA, $transaction->card->scheme);
        $this->assertSame('2025-11-13T10:00:00Z', $transaction->createdAt);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        // Arrange
        $responseBody = json_encode([
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 201);
        $response = new TransactionResponse($psr7Response);

        // Act
        $statusCode = $response->getStatusCode();

        // Assert
        $this->assertSame(201, $statusCode);
    }

    public function test_isSuccessful_with200StatusCode_returnsTrue(): void
    {
        // Arrange
        $responseBody = json_encode([
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 200);
        $response = new TransactionResponse($psr7Response);

        // Act & Assert
        $this->assertTrue($response->isSuccessful());
    }

    public function test_isSuccessful_with201StatusCode_returnsTrue(): void
    {
        // Arrange
        $responseBody = json_encode([
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 201);
        $response = new TransactionResponse($psr7Response);

        // Act & Assert
        $this->assertTrue($response->isSuccessful());
    }

    public function test_isSuccessful_with299StatusCode_returnsTrue(): void
    {
        // Arrange
        $responseBody = json_encode([
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 299);
        $response = new TransactionResponse($psr7Response);

        // Act & Assert
        $this->assertTrue($response->isSuccessful());
    }

    public function test_isSuccessful_with400StatusCode_returnsFalse(): void
    {
        // Arrange
        $responseBody = json_encode([
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 400);
        $response = new TransactionResponse($psr7Response);

        // Act & Assert
        $this->assertFalse($response->isSuccessful());
    }

    public function test_isSuccessful_with500StatusCode_returnsFalse(): void
    {
        // Arrange
        $responseBody = json_encode([
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 500);
        $response = new TransactionResponse($psr7Response);

        // Act & Assert
        $this->assertFalse($response->isSuccessful());
    }

    public function test_getPsr7Response_returnsOriginalResponse(): void
    {
        // Arrange
        $responseBody = json_encode([
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 200);
        $response = new TransactionResponse($psr7Response);

        // Act
        $originalResponse = $response->getPsr7Response();

        // Assert
        $this->assertSame($psr7Response, $originalResponse);
    }

    public function test_construct_withEmptyBody_throwsException(): void
    {
        // Arrange
        $psr7Response = $this->createMockResponse('', 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is empty');

        // Act
        new TransactionResponse($psr7Response);
    }

    public function test_construct_withInvalidJson_throwsException(): void
    {
        // Arrange
        $psr7Response = $this->createMockResponse('invalid json{', 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        // Act
        new TransactionResponse($psr7Response);
    }

    public function test_construct_withNonObjectJson_throwsException(): void
    {
        // Arrange
        $psr7Response = $this->createMockResponse('"just a string"', 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is not a JSON object');

        // Act
        new TransactionResponse($psr7Response);
    }

    public function test_construct_withJsonArray_throwsException(): void
    {
        // Arrange
        $psr7Response = $this->createMockResponse('[]', 200);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is not a JSON object');

        // Act
        new TransactionResponse($psr7Response);
    }

    public function test_construct_withDeclinedTransaction_createsInstance(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'txn_456',
            'state' => 'declined',
            'total' => ['amount' => '50.00', 'currencyCode' => 'EUR'],
            'createdAt' => '2025-11-13T11:00:00Z',
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionResponse($psr7Response);
        $transaction = $response->getTransaction();

        // Assert
        $this->assertSame(TransactionState::DECLINED, $transaction->state);
        $this->assertSame('txn_456', $transaction->id);
    }

    public function test_construct_withCapturedTransaction_createsInstance(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'txn_789',
            'state' => 'captured',
            'total' => ['amount' => '150.00', 'currencyCode' => 'GBP'],
            'card' => [
                'last4' => '4444',
                'bin' => '555555',
                'scheme' => 'MasterCard',
                'fingerprint' => 'fp_abc123',
            ],
            'description' => 'Completed order',
            'customReference' => 'ORDER-789',
            'createdAt' => '2025-11-13T12:00:00Z',
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionResponse($psr7Response);
        $transaction = $response->getTransaction();

        // Assert
        $this->assertSame(TransactionState::CAPTURED, $transaction->state);
        $this->assertSame('txn_789', $transaction->id);
        $this->assertSame('150.00', $transaction->total->amount);
        $this->assertSame(Currency::GBP, $transaction->total->currency);
        $this->assertSame('4444', $transaction->card->last4);
        $this->assertSame('555555', $transaction->card->bin);
        $this->assertSame(CardScheme::MASTERCARD, $transaction->card->scheme);
        $this->assertSame('fp_abc123', $transaction->card->fingerprint);
        $this->assertSame('Completed order', $transaction->description);
        $this->assertSame('ORDER-789', $transaction->customReference);
    }

    public function test_construct_withMinimalValidResponse_createsInstance(): void
    {
        // Arrange
        $responseBody = json_encode([
            'total' => ['amount' => '1.00', 'currencyCode' => 'USD'],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionResponse($psr7Response);
        $transaction = $response->getTransaction();

        // Assert
        $this->assertSame('1.00', $transaction->total->amount);
        $this->assertSame(Currency::USD, $transaction->total->currency);
        $this->assertNull($transaction->id);
        $this->assertNull($transaction->state);
        $this->assertNull($transaction->card);
    }

    // Error Response Tests

    public function test_construct_with401Error_parsesError(): void
    {
        // Arrange - Real API error response
        $responseBody = json_encode([
            'status' => 401,
            'failures' => [
                [
                    'code' => 'unauthorized',
                    'description' => 'A valid API key is required',
                    'field' => null,
                ],
            ],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 401);

        // Act
        $response = new TransactionResponse($psr7Response);

        // Assert
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->hasError());
        $this->assertNull($response->getTransaction());

        $error = $response->getError();
        $this->assertNotNull($error);
        $this->assertSame(401, $error->status);
        $this->assertSame('unauthorized', $error->getCode());
        $this->assertSame('A valid API key is required', $error->getMessage());
    }

    public function test_construct_with400ValidationError_parsesError(): void
    {
        // Arrange
        $responseBody = json_encode([
            'status' => 400,
            'failures' => [
                [
                    'code' => 'validation_error',
                    'description' => 'Card number is invalid',
                    'field' => 'card.number',
                ],
            ],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 400);

        // Act
        $response = new TransactionResponse($psr7Response);

        // Assert
        $this->assertTrue($response->hasError());
        $this->assertFalse($response->isSuccessful());

        $error = $response->getError();
        $this->assertSame('validation_error', $error->getCode());
        $this->assertSame('Card number is invalid', $error->getMessage());
        $this->assertSame('card.number', $error->getFailures()[0]->field);
    }

    public function test_construct_with500Error_parsesError(): void
    {
        // Arrange
        $responseBody = json_encode([
            'status' => 500,
            'failures' => [
                [
                    'code' => 'internal_error',
                    'description' => 'An internal server error occurred',
                    'field' => null,
                ],
            ],
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 500);

        // Act
        $response = new TransactionResponse($psr7Response);

        // Assert
        $this->assertTrue($response->hasError());
        $this->assertSame(500, $response->getStatusCode());

        $error = $response->getError();
        $this->assertSame('internal_error', $error->getCode());
    }

    public function test_construct_withSuccessResponse_hasNoError(): void
    {
        // Arrange
        $responseBody = json_encode([
            'id' => 'txn_123',
            'state' => 'authorized',
            'total' => ['amount' => '99.99', 'currencyCode' => 'USD'],
            'createdAt' => '2025-11-13T10:00:00Z',
        ]);

        $psr7Response = $this->createMockResponse($responseBody, 200);

        // Act
        $response = new TransactionResponse($psr7Response);

        // Assert
        $this->assertFalse($response->hasError());
        $this->assertNull($response->getError());
        $this->assertNotNull($response->getTransaction());
    }

    /**
     * Creates a mock PSR-7 response for testing.
     */
    private function createMockResponse(string $body, int $statusCode): ResponseInterface
    {
        $stream = new Stream($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }
}