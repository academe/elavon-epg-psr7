<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Response\Account;

use Academe\Elavon\Epg\Psr7\Dtos\Account;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Account\AccountResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class AccountResponseTest extends TestCase
{
    public function test_construct_withSuccessResponse_parsesAccount(): void
    {
        $responseData = [
            'id' => 'account123',
            'href' => 'https://api.eu.elavonpayments.com/accounts/account123',
            'createdAt' => '2017-02-22T13:01:23.123Z',
            'modifiedAt' => '2017-02-22T13:01:33.567Z',
            'merchant' => 'https://api.eu.elavonpayments.com/merchants/merchant456',
            'name' => 'Sirius Corporation',
            'description' => 'A fintech company.',
            'tradeName' => 'Gringotts',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $accountResponse = new AccountResponse($response);

        $this->assertTrue($accountResponse->isSuccessful());
        $this->assertNull($accountResponse->getError());
        $this->assertInstanceOf(Account::class, $accountResponse->getAccount());
        $this->assertSame('account123', $accountResponse->getAccount()->id);
        $this->assertSame('Sirius Corporation', $accountResponse->getAccount()->name);
        $this->assertSame('Gringotts', $accountResponse->getAccount()->tradeName);
    }

    public function test_construct_withSuccessResponseAndAllFields_parsesAccount(): void
    {
        $responseData = [
            'id' => 'f9g699w9v43r9gcp77y2bxq4rjcx',
            'href' => 'https://api.eu.elavonpayments.com/accounts/f9g699w9v43r9gcp77y2bxq4rjcx',
            'createdAt' => '2017-02-22T13:01:23.123Z',
            'modifiedAt' => '2017-02-22T13:01:33.567Z',
            'merchant' => 'https://api.eu.elavonpayments.com/merchants/6xxFwvM8BqmM6T6DcF3DyTB3',
            'processorAccounts' => [
                [
                    'id' => 'proc123',
                    'legalName' => 'Test Processor',
                    'marketSegment' => 'retail',
                ],
            ],
            'name' => 'Sirius Corporation',
            'description' => 'A fintech company.',
            'tradeName' => 'Gringotts',
            'businessAddress' => '123 Main St, London',
            'businessPhone' => '+44 020 7946 0123',
            'businessEmail' => 'sales@gringotts.com',
            'businessWebsite' => 'www.gringotts.com',
            'planList' => 'https://api.eu.elavonpayments.com/plan-lists/f9g699w9v43r9gcp77y2bxq4rjcx',
            'logoUrl' => 'https://cf.media.eu.convergepay.com/logo.jpg',
            'autoSettleAt' => [
                'time' => '23:00',
                'timeZoneId' => 'Europe/Berlin',
            ],
        ];

        $response = $this->createMockResponse(200, $responseData);
        $accountResponse = new AccountResponse($response);

        $this->assertTrue($accountResponse->isSuccessful());
        $account = $accountResponse->getAccount();
        $this->assertNotNull($account);
        $this->assertSame('f9g699w9v43r9gcp77y2bxq4rjcx', $account->id);
        $this->assertSame('Sirius Corporation', $account->name);
        $this->assertNotNull($account->autoSettleAt);
        $this->assertSame('23:00', $account->autoSettleAt->time);
        $this->assertSame('Europe/Berlin', $account->autoSettleAt->timeZoneId);
        $this->assertNotNull($account->processorAccounts);
        $this->assertCount(1, $account->processorAccounts);
        $this->assertSame('proc123', $account->processorAccounts[0]->id);
    }

    public function test_construct_withErrorResponse_parsesError(): void
    {
        $errorData = [
            'status' => 404,
            'failures' => [
                [
                    'code' => 'ACCOUNT_NOT_FOUND',
                    'description' => 'Account not found',
                    'field' => null,
                ],
            ],
        ];

        $response = $this->createMockResponse(404, $errorData);
        $accountResponse = new AccountResponse($response);

        $this->assertFalse($accountResponse->isSuccessful());
        $this->assertNull($accountResponse->getAccount());
        $this->assertNotNull($accountResponse->getError());
        $this->assertSame('Account not found', $accountResponse->getError()->getMessage());
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

        new AccountResponse($response);
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

        new AccountResponse($response);
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

        new AccountResponse($response);
    }

    public function test_fromPsr7Response_createsInstance(): void
    {
        $responseData = [
            'id' => 'account789',
            'name' => 'Test Account',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $accountResponse = AccountResponse::fromPsr7Response($response);

        $this->assertInstanceOf(AccountResponse::class, $accountResponse);
        $this->assertSame('account789', $accountResponse->getAccount()->id);
    }

    public function test_getStatusCode_returnsCorrectCode(): void
    {
        $responseData = [
            'id' => 'account999',
            'name' => 'Status Test',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $accountResponse = new AccountResponse($response);

        $this->assertSame(200, $accountResponse->getStatusCode());
    }

    public function test_getPsr7Response_returnsOriginalResponse(): void
    {
        $responseData = [
            'id' => 'account111',
            'name' => 'Response Test',
        ];

        $response = $this->createMockResponse(200, $responseData);
        $accountResponse = new AccountResponse($response);

        $this->assertSame($response, $accountResponse->getPsr7Response());
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
