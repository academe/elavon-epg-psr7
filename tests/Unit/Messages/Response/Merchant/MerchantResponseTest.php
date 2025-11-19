<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Merchant;

use Academe\Elavon\Epg\Psr7\Dtos\ErrorResponse;
use Academe\Elavon\Epg\Psr7\Dtos\Merchant;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Merchant\MerchantResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class MerchantResponseTest extends TestCase
{
    public function test_fromPsr7Response_withSuccessResponse_parsesMerchant(): void
    {
        $responseData = [
            'href' => 'https://api.eu.elavonpayments.com/merchants/test123',
            'id' => 'test123',
            'legalName' => 'Test Company Ltd',
            'friendlyName' => 'Test Company',
            'regions' => ['eu', 'na'],
            'isDemo' => true,
        ];

        $response = $this->createMockResponse(200, $responseData);
        $merchantResponse = MerchantResponse::fromPsr7Response($response);

        $this->assertTrue($merchantResponse->isSuccessful());
        $this->assertNull($merchantResponse->getError());
        $this->assertInstanceOf(Merchant::class, $merchantResponse->getMerchant());
        $this->assertSame('test123', $merchantResponse->getMerchant()->id);
        $this->assertSame('Test Company Ltd', $merchantResponse->getMerchant()->legalName);
        $this->assertTrue($merchantResponse->getMerchant()->isDemo);
    }

    public function test_fromPsr7Response_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 404,
            'failures' => [
                ['code' => 'not_found', 'description' => 'The requested merchant does not exist'],
            ],
        ];

        $response = $this->createMockResponse(404, $errorData);
        $merchantResponse = MerchantResponse::fromPsr7Response($response);

        $this->assertFalse($merchantResponse->isSuccessful());
        $this->assertNull($merchantResponse->getMerchant());
        $this->assertInstanceOf(ErrorResponse::class, $merchantResponse->getError());
        $this->assertSame(404, $merchantResponse->getError()->status);
        $this->assertSame('The requested merchant does not exist', $merchantResponse->getError()->getMessage());
    }

    public function test_fromPsr7Response_withEmptyBody_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response body is empty');

        MerchantResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_withInvalidJson_throwsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('__toString')->willReturn('invalid json{');
        $response->method('getBody')->willReturn($stream);
        $response->method('getStatusCode')->willReturn(200);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');

        MerchantResponse::fromPsr7Response($response);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $response = $this->createMockResponse(201, ['id' => 'test']);
        $merchantResponse = MerchantResponse::fromPsr7Response($response);

        $this->assertSame(201, $merchantResponse->getStatusCode());
    }

    public function test_getPsr7Response_returnsOriginalResponse(): void
    {
        $response = $this->createMockResponse(200, ['id' => 'test']);
        $merchantResponse = MerchantResponse::fromPsr7Response($response);

        $this->assertSame($response, $merchantResponse->getPsr7Response());
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
