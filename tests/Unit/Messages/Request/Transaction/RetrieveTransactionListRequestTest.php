<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Messages\Request\Transaction;

use Academe\Elavon\Epg\Psr7\Messages\Request\Transaction\RetrieveTransactionListRequest;
use PHPUnit\Framework\TestCase;

/**
 * Tests for RetrieveTransactionListRequest message.
 */
class RetrieveTransactionListRequestTest extends TestCase
{
    public function test_construct_withoutParams_createsInstance(): void
    {
        // Act
        $request = new RetrieveTransactionListRequest();

        // Assert
        $this->assertSame([], $request->getQueryParams());
    }

    public function test_construct_withParams_createsInstance(): void
    {
        // Arrange
        $params = ['limit' => 50, 'offset' => 100];

        // Act
        $request = new RetrieveTransactionListRequest($params);

        // Assert
        $this->assertSame($params, $request->getQueryParams());
    }

    public function test_build_withoutParams_createsValidRequest(): void
    {
        // Arrange
        $request = new RetrieveTransactionListRequest();

        // Act
        $psrRequest = $request->build();

        // Assert
        $this->assertSame('GET', $psrRequest->getMethod());
        $this->assertSame('/transactions', (string) $psrRequest->getUri());
        $this->assertSame('application/json', $psrRequest->getHeaderLine('Accept'));
    }

    public function test_build_withParams_includesQueryString(): void
    {
        // Arrange
        $params = ['limit' => 50, 'offset' => 100];
        $request = new RetrieveTransactionListRequest($params);

        // Act
        $psrRequest = $request->build();
        $uri = (string) $psrRequest->getUri();

        // Assert
        $this->assertStringContainsString('limit=50', $uri);
        $this->assertStringContainsString('offset=100', $uri);
        $this->assertStringStartsWith('/transactions?', $uri);
    }

    public function test_build_hasNoBody(): void
    {
        // Arrange
        $request = new RetrieveTransactionListRequest();

        // Act
        $psrRequest = $request->build();
        $body = (string) $psrRequest->getBody();

        // Assert
        $this->assertSame('', $body);
    }

    public function test_build_multipleCalls_returnsSeparateInstances(): void
    {
        // Arrange
        $request = new RetrieveTransactionListRequest(['page' => 1]);

        // Act
        $psrRequest1 = $request->build();
        $psrRequest2 = $request->build();

        // Assert
        $this->assertNotSame($psrRequest1, $psrRequest2);
        $this->assertEquals($psrRequest1->getUri(), $psrRequest2->getUri());
    }

    public function test_getQueryParams_returnsCorrectParams(): void
    {
        // Arrange
        $params = ['limit' => 25, 'page' => 2, 'status' => 'completed'];
        $request = new RetrieveTransactionListRequest($params);

        // Act & Assert
        $this->assertSame($params, $request->getQueryParams());
    }
}
