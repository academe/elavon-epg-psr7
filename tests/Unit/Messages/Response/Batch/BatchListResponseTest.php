<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Batch;

use Academe\Elavon\Epg\Psr7\Dtos\Batch;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Batch\BatchListResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class BatchListResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesBatches(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'batch123',
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
                ],
                [
                    'id' => 'batch456',
                    'state' => 'submitted',
                    'processorReference' => '21180001',
                    'credits' => [
                        'count' => 2,
                        'total' => ['amount' => '200.00', 'currencyCode' => 'EUR'],
                    ],
                    'debits' => [
                        'count' => 5,
                        'total' => ['amount' => '318.00', 'currencyCode' => 'EUR'],
                    ],
                    'net' => [
                        'count' => 7,
                        'total' => ['amount' => '518.00', 'currencyCode' => 'EUR'],
                    ],
                ],
            ],
            'next' => 'https://api.converge.eu.elavon.net/batches?offset=50',
            'first' => 'https://api.converge.eu.elavon.net/batches',
        ];

        $listResponse = new BatchListResponse($responseData, 200);

        $this->assertTrue($listResponse->isSuccessful());
        $this->assertNull($listResponse->error);
        $this->assertIsArray($listResponse->batches);
        $this->assertCount(2, $listResponse->batches);
        $this->assertInstanceOf(Batch::class, $listResponse->batches[0]);
        $this->assertSame('batch123', $listResponse->batches[0]->id);
        $this->assertSame('batch456', $listResponse->batches[1]->id);
    }

    public function test_construct_withEmptyItems_parsesSuccessfully(): void
    {
        $responseData = [
            'items' => [],
        ];

        $listResponse = new BatchListResponse($responseData, 200);

        $this->assertTrue($listResponse->isSuccessful());
        $this->assertIsArray($listResponse->batches);
        $this->assertCount(0, $listResponse->batches);
    }

    public function test_construct_withPaginationLinks_storesLinks(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'batch1',
                    'state' => 'settled',
                ],
            ],
            'next' => 'https://api.converge.eu.elavon.net/batches?page=2',
            'first' => 'https://api.converge.eu.elavon.net/batches?page=1',
        ];

        $listResponse = new BatchListResponse($responseData, 200);

        $this->assertSame('https://api.converge.eu.elavon.net/batches?page=2', $listResponse->nextPage);
        $this->assertSame('https://api.converge.eu.elavon.net/batches?page=1', $listResponse->firstPage);
        $this->assertTrue($listResponse->hasMorePages());
    }

    public function test_construct_withoutNextLink_hasNoMorePages(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'batch1',
                    'state' => 'settled',
                ],
            ],
        ];

        $listResponse = new BatchListResponse($responseData, 200);

        $this->assertNull($listResponse->nextPage);
        $this->assertFalse($listResponse->hasMorePages());
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 401,
            'failures' => [
                [
                    'code' => 'UNAUTHORIZED',
                    'description' => 'Unauthorized access',
                    'field' => null,
                ],
            ],
        ];

        $listResponse = new BatchListResponse($errorData, 401);

        $this->assertFalse($listResponse->isSuccessful());
        $this->assertNull($listResponse->batches);
        $this->assertNotNull($listResponse->error);
        $this->assertSame('Unauthorized access', $listResponse->error->getMessage());
    }

    public function test_construct_withMissingItemsKey_throwsException(): void
    {
        $responseData = [
            'data' => [],  // Wrong key
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        BatchListResponse::fromPsr7Response($response);
    }

    public function test_construct_withNonArrayItems_throwsException(): void
    {
        $responseData = [
            'items' => 'not an array',
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        BatchListResponse::fromPsr7Response($response);
    }

    public function test_construct_withInvalidItemData_throwsException(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'batch1', 'state' => 'settled'],
                'invalid item',  // String instead of array
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item at index 1 is not an array');

        BatchListResponse::fromPsr7Response($response);
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

        BatchListResponse::fromPsr7Response($response);
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

        BatchListResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'batch789', 'state' => 'settled'],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = BatchListResponse::fromPsr7Response($response);

        $this->assertInstanceOf(BatchListResponse::class, $listResponse);
        $this->assertCount(1, $listResponse->batches);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'batch999', 'state' => 'settled'],
            ],
        ];

        $listResponse = new BatchListResponse($responseData, 200);

        $this->assertSame(200, $listResponse->statusCode);
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
