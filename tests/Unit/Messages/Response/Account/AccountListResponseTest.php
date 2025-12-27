<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Account;

use Academe\Elavon\Epg\Psr7\Dtos\Account;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Account\AccountListResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class AccountListResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesAccounts(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'account1',
                    'name' => 'First Account',
                ],
                [
                    'id' => 'account2',
                    'name' => 'Second Account',
                ],
            ],
            'next' => 'https://api.eu.elavonpayments.com/accounts?pageToken=next123',
            'first' => 'https://api.eu.elavonpayments.com/accounts',
        ];

        $listResponse = new AccountListResponse($responseData, 200);

        $this->assertTrue($listResponse->isSuccessful());
        $this->assertNull($listResponse->error);
        $this->assertIsArray($listResponse->accounts);
        $this->assertCount(2, $listResponse->accounts);
        $this->assertContainsOnlyInstancesOf(Account::class, $listResponse->accounts);
        $this->assertSame('account1', $listResponse->accounts[0]->id);
        $this->assertSame('account2', $listResponse->accounts[1]->id);
    }

    public function test_construct_withPaginationLinks_parsesPagination(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'account123', 'name' => 'Test Account'],
            ],
            'next' => 'https://api.eu.elavonpayments.com/accounts?pageToken=abc',
            'first' => 'https://api.eu.elavonpayments.com/accounts',
        ];

        $listResponse = new AccountListResponse($responseData, 200);

        $this->assertTrue($listResponse->hasMorePages());
        $this->assertSame('https://api.eu.elavonpayments.com/accounts?pageToken=abc', $listResponse->nextPage);
        $this->assertSame('https://api.eu.elavonpayments.com/accounts', $listResponse->firstPage);
    }

    public function test_construct_withoutNextLink_hasNoMorePages(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'account1', 'name' => 'Only Account'],
            ],
            'first' => 'https://api.eu.elavonpayments.com/accounts',
        ];

        $listResponse = new AccountListResponse($responseData, 200);

        $this->assertFalse($listResponse->hasMorePages());
        $this->assertNull($listResponse->nextPage);
    }

    public function test_construct_withEmptyItems_parsesEmptyList(): void
    {
        $responseData = [
            'items' => [],
            'first' => 'https://api.eu.elavonpayments.com/accounts',
        ];

        $listResponse = new AccountListResponse($responseData, 200);

        $this->assertTrue($listResponse->isSuccessful());
        $this->assertIsArray($listResponse->accounts);
        $this->assertCount(0, $listResponse->accounts);
        $this->assertFalse($listResponse->hasMorePages());
    }

    public function test_construct_withComplexAccounts_parsesAllFields(): void
    {
        $responseData = [
            'items' => [
                [
                    'id' => 'f9g699w9v43r9gcp77y2bxq4rjcx',
                    'name' => 'Complex Account',
                    'description' => 'Test description',
                    'tradeName' => 'TestCo',
                    'processorAccounts' => [
                        [
                            'id' => 'proc1',
                            'legalName' => 'Processor One',
                        ],
                    ],
                    'autoSettleAt' => [
                        'time' => '23:00',
                        'timeZoneId' => 'Europe/Berlin',
                    ],
                ],
            ],
        ];

        $listResponse = new AccountListResponse($responseData, 200);

        $this->assertTrue($listResponse->isSuccessful());
        $accounts = $listResponse->accounts;
        $this->assertCount(1, $accounts);
        $account = $accounts[0];
        $this->assertSame('f9g699w9v43r9gcp77y2bxq4rjcx', $account->id);
        $this->assertSame('Complex Account', $account->name);
        $this->assertNotNull($account->autoSettleAt);
        $this->assertSame('23:00', $account->autoSettleAt->time);
        $this->assertNotNull($account->processorAccounts);
        $this->assertCount(1, $account->processorAccounts);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 401,
            'failures' => [
                [
                    'code' => 'UNAUTHORIZED',
                    'description' => 'Authentication required',
                    'field' => null,
                ],
            ],
        ];

        $listResponse = new AccountListResponse($errorData, 401);

        $this->assertFalse($listResponse->isSuccessful());
        $this->assertNull($listResponse->accounts);
        $this->assertNotNull($listResponse->error);
        $this->assertSame('Authentication required', $listResponse->error->getMessage());
    }

    public function test_construct_withMissingItemsArray_throwsException(): void
    {
        $responseData = [
            'next' => 'https://api.eu.elavonpayments.com/accounts?pageToken=abc',
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must contain an "items" array');

        AccountListResponse::fromPsr7Response($response);
    }

    public function test_construct_withInvalidItemType_throwsException(): void
    {
        $responseData = [
            'items' => [
                'not-an-array',
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item at index 0 is not an array');

        AccountListResponse::fromPsr7Response($response);
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

        AccountListResponse::fromPsr7Response($response);
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

        AccountListResponse::fromPsr7Response($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'items' => [
                ['id' => 'account789', 'name' => 'Test'],
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $listResponse = AccountListResponse::fromPsr7Response($response);

        $this->assertInstanceOf(AccountListResponse::class, $listResponse);
        $this->assertSame('account789', $listResponse->accounts[0]->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = [
            'items' => [],
        ];

        $listResponse = new AccountListResponse($responseData, 200);

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
