<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Batch;

use Academe\Elavon\Epg\Psr7\Dtos\Batch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Batch\BatchResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class BatchResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesBatch(): void
    {
        $responseData = [
            'id' => 'batch123',
            'href' => 'https://api.converge.eu.elavon.net/batches/batch123',
            'createdAt' => '2018-07-31T00:00:01.508Z',
            'modifiedAt' => '2018-07-31T00:00:12.074Z',
            'state' => 'settled',
            'processorReference' => '21280002',
            'credits' => [
                'count' => 1,
                'total' => ['amount' => '100.00', 'currencyCode' => 'EUR'],
            ],
            'debits' => [
                'count' => 3,
                'total' => ['amount' => '22.00', 'currencyCode' => 'EUR'],
            ],
            'net' => [
                'count' => 4,
                'total' => ['amount' => '78.00', 'currencyCode' => 'EUR'],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $batchResponse = new BatchResponse($response);

        $this->assertTrue($batchResponse->isSuccessful());
        $this->assertNull($batchResponse->getError());
        $this->assertInstanceOf(Batch::class, $batchResponse->getBatch());
        $this->assertSame('batch123', $batchResponse->getBatch()->id);
        $this->assertSame('settled', $batchResponse->getBatch()->state->value);
        $this->assertSame('21280002', $batchResponse->getBatch()->processorReference);
    }

    public function test_construct_withSuccessResponseAndAllFields_parsesBatch(): void
    {
        $responseData = [
            'id' => 'wrKK4HcHCXcK3KkXwFRMXVjQ',
            'href' => 'https://api.converge.eu.elavon.net/batches/wrKK4HcHCXcK3KkXwFRMXVjQ',
            'createdAt' => '2018-07-31T00:00:01.508Z',
            'modifiedAt' => '2018-07-31T00:00:12.074Z',
            'merchant' => 'https://api.converge.eu.elavon.net/merchants/XrDXRBh9YHxwqQTj2Cmq7j49',
            'processorAccount' => 'https://api.converge.eu.elavon.net/processor-accounts/KmvmfQJpCBJpXHyP2kgrK2hD',
            'terminal' => 'https://api.converge.eu.elavon.net/terminals/terminal123',
            'account' => 'https://api.converge.eu.elavon.net/accounts/account456',
            'processorReference' => '21280002',
            'state' => 'settled',
            'credits' => [
                'count' => 0,
                'total' => ['amount' => '0.00', 'currencyCode' => 'EUR'],
            ],
            'debits' => [
                'count' => 3,
                'total' => ['amount' => '22.00', 'currencyCode' => 'EUR'],
            ],
            'net' => [
                'count' => 3,
                'total' => ['amount' => '22.00', 'currencyCode' => 'EUR'],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $batchResponse = new BatchResponse($response);

        $this->assertTrue($batchResponse->isSuccessful());
        $batch = $batchResponse->getBatch();
        $this->assertNotNull($batch->credits);
        $this->assertSame(0, $batch->credits->count);
        $this->assertSame('0', $batch->credits->total->getAmount());
        $this->assertNotNull($batch->debits);
        $this->assertSame(3, $batch->debits->count);
        $this->assertSame('2200', $batch->debits->total->getAmount());
        $this->assertNotNull($batch->net);
        $this->assertSame(3, $batch->net->count);
        $this->assertSame('2200', $batch->net->total->getAmount());
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 404,
            'failures' => [
                [
                    'code' => 'BATCH_NOT_FOUND',
                    'description' => 'Batch not found',
                    'field' => null,
                ],
            ],
        ];

        $response = $this->createMockResponse(404, $errorData);
        $batchResponse = new BatchResponse($response);

        $this->assertFalse($batchResponse->isSuccessful());
        $this->assertNull($batchResponse->getBatch());
        $this->assertNotNull($batchResponse->getError());
        $this->assertSame('Batch not found', $batchResponse->getError()->getMessage());
    }

    public function test_construct_withEmptyBody_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is empty');

        new BatchResponse($response);
    }

    public function test_construct_withInvalidJson_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('invalid json{');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        new BatchResponse($response);
    }

    public function test_construct_withJsonArray_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('[]');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is not a JSON object');

        new BatchResponse($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'id' => 'batch789',
            'state' => 'submitted',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $batchResponse = BatchResponse::fromPsr7Response($response);

        $this->assertInstanceOf(BatchResponse::class, $batchResponse);
        $this->assertSame('batch789', $batchResponse->getBatch()->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = [
            'id' => 'batch999',
            'state' => 'settled',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $batchResponse = new BatchResponse($response);

        $this->assertSame(200, $batchResponse->getStatusCode());
    }

    public function test_getPsr7Response_returnsOriginalResponse(): void
    {
        $responseData = [
            'id' => 'batch111',
            'state' => 'rejected',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $batchResponse = new BatchResponse($response);

        $this->assertSame($response, $batchResponse->getPsr7Response());
    }

    /**
     * Creates a mock PSR-7 response.
     */
    private function createMockResponse(int $statusCode, array $data): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $json = json_encode($data);
        $stream->method('__toString')->willReturn($json);

        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }
}
