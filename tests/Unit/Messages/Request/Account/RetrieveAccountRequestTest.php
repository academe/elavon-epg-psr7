<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Account;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Request\Account\RetrieveAccountRequest;
use PHPUnit\Framework\TestCase;

class RetrieveAccountRequestTest extends TestCase
{
    public function test_construct_withValidId_createsInstance(): void
    {
        $request = new RetrieveAccountRequest('account123');

        $this->assertSame('account123', $request->accountId);
    }

    public function test_construct_withEmptyId_throwsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Account ID cannot be empty');

        new RetrieveAccountRequest('');
    }

    public function test_build_createsValidPsr7Request(): void
    {
        $request = new RetrieveAccountRequest('account456');

        $psr7Request = $request->build();

        $this->assertSame('GET', $psr7Request->getMethod());
        $this->assertStringContainsString('/accounts/account456', (string) $psr7Request->getUri());
    }

    public function test_build_withLongAccountId_createsRequest(): void
    {
        $longId = 'f9g699w9v43r9gcp77y2bxq4rjcx';
        $request = new RetrieveAccountRequest($longId);

        $psr7Request = $request->build();

        $this->assertStringContainsString('/accounts/' . $longId, (string) $psr7Request->getUri());
    }

    public function test_getAccountId_returnsCorrectId(): void
    {
        $accountId = 'test-account-id';
        $request = new RetrieveAccountRequest($accountId);

        $this->assertSame($accountId, $request->accountId);
    }
}
