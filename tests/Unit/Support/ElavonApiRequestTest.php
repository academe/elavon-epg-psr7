<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Support;

use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
use Academe\Elavon\Epg\Psr7\Support\Request;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;

class ElavonApiRequestTest extends TestCase
{
    public function test_create_addsAcceptHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request);

        // Assert
        $this->assertTrue($elavonRequest->hasHeader('Accept'));
        $this->assertSame('application/json;charset=UTF-8', $elavonRequest->getHeaderLine('Accept'));
    }

    public function test_construct_addsContentTypeHeaderWhenBodyPresent(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/transactions', [], '{"test": "data"}');

        // Act
        $elavonRequest = ElavonApiRequest::create($request);

        // Assert
        $this->assertTrue($elavonRequest->hasHeader('Content-Type'));
        $this->assertSame('application/json', $elavonRequest->getHeaderLine('Content-Type'));
    }

    public function test_construct_doesNotAddContentTypeWhenNoBody(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request);

        // Assert
        $this->assertFalse($elavonRequest->hasHeader('Content-Type'));
    }

    public function test_construct_addsDefaultApiVersionHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request);

        // Assert
        $this->assertTrue($elavonRequest->hasHeader('Accept-Version'));
        $this->assertSame('1', $elavonRequest->getHeaderLine('Accept-Version'));
        $this->assertSame('1', $elavonRequest->getApiVersion());
    }

    public function test_create_withCustomApiVersion_addsCustomVersionHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request, '2');

        // Assert
        $this->assertSame('2', $elavonRequest->getHeaderLine('Accept-Version'));
        $this->assertSame('2', $elavonRequest->getApiVersion());
    }

    public function test_construct_preservesExistingAcceptHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test', [
            'Accept' => 'text/html',
        ]);

        // Act
        $elavonRequest = ElavonApiRequest::create($request);

        // Assert - Should preserve existing Accept header
        $this->assertSame('text/html', $elavonRequest->getHeaderLine('Accept'));
    }

    public function test_construct_preservesExistingContentTypeHeader(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/test', [
            'Content-Type' => 'application/xml',
        ], '<xml></xml>');

        // Act
        $elavonRequest = ElavonApiRequest::create($request);

        // Assert - Should preserve existing Content-Type header
        $this->assertSame('application/xml', $elavonRequest->getHeaderLine('Content-Type'));
    }

    public function test_withBaseUri_replacesBaseUri(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions/123');

        // Act
        $elavonRequest = ElavonApiRequest::create($request)
            ->withBaseUri('https://uat.api.converge.eu.elavonaws.com');

        // Assert
        $uri = $elavonRequest->getUri();
        $this->assertSame('https://uat.api.converge.eu.elavonaws.com/transactions/123', (string) $uri);
    }

    public function test_withBaseUri_preservesPathAndQuery(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions?filter=state_eq_captured');

        // Act
        $elavonRequest = ElavonApiRequest::create($request)
            ->withBaseUri('https://uat.api.converge.eu.elavonaws.com');

        // Assert
        $uri = $elavonRequest->getUri();
        $this->assertSame('/transactions', $uri->getPath());
        $this->assertSame('filter=state_eq_captured', $uri->getQuery());
    }

    public function test_withSandbox_createsUatRequest(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request)->withSandbox();

        // Assert
        $uri = $elavonRequest->getUri();
        $this->assertStringStartsWith('https://uat.api.converge.eu.elavonaws.com', (string) $uri);
        $this->assertTrue($elavonRequest->hasHeader('Accept-Version'));
        $this->assertSame('1', $elavonRequest->getApiVersion());
    }

    public function test_withProduction_createsLiveRequest(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request)->withProduction();

        // Assert - withProduction() defaults to EU production
        $uri = $elavonRequest->getUri();
        $this->assertStringStartsWith('https://api.eu.convergepay.com', (string) $uri);
        $this->assertTrue($elavonRequest->hasHeader('Accept-Version'));
        $this->assertSame('1', $elavonRequest->getApiVersion());
    }

    public function test_withSandbox_withCustomApiVersion(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request, '2')->withSandbox();

        // Assert
        $this->assertSame('2', $elavonRequest->getHeaderLine('Accept-Version'));
        $this->assertSame('2', $elavonRequest->getApiVersion());
    }

    public function test_withProduction_withCustomApiVersion(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request, '3')->withProduction();

        // Assert
        $this->assertSame('3', $elavonRequest->getHeaderLine('Accept-Version'));
        $this->assertSame('3', $elavonRequest->getApiVersion());
    }

    public function test_withBaseUri_createsCustomRequest(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');
        $customBaseUri = 'https://custom.api.example.com';

        // Act
        $elavonRequest = ElavonApiRequest::create($request)->withBaseUri($customBaseUri);

        // Assert
        $uri = $elavonRequest->getUri();
        $this->assertStringStartsWith($customBaseUri, (string) $uri);
    }

    public function test_withBaseUri_withCustomApiVersion(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request, '5')->withBaseUri('https://custom.api.example.com');

        // Assert
        $this->assertSame('5', $elavonRequest->getApiVersion());
    }

    public function test_preservesOriginalRequestMethod(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/transactions');

        // Act
        $elavonRequest = ElavonApiRequest::create($request);

        // Assert
        $this->assertSame('POST', $elavonRequest->getMethod());
    }

    public function test_preservesOriginalRequestBody(): void
    {
        // Arrange
        $body = '{"amount": "99.99"}';
        $request = new Request('POST', 'https://api.example.com/test', [], $body);

        // Act
        $elavonRequest = ElavonApiRequest::create($request);

        // Assert
        $this->assertSame($body, (string) $elavonRequest->getBody());
    }

    public function test_withHeader_returnsNewInstanceWithHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $elavonRequest = ElavonApiRequest::create($request);

        // Act
        $newRequest = $elavonRequest->withHeader('X-Custom', 'value');

        // Assert
        $this->assertNotSame($elavonRequest, $newRequest);
        $this->assertFalse($elavonRequest->hasHeader('X-Custom'));
        $this->assertTrue($newRequest->hasHeader('X-Custom'));
        $this->assertSame('value', $newRequest->getHeaderLine('X-Custom'));
    }

    public function test_realWorldUsage_withTransactionRequest(): void
    {
        // Arrange
        $requestBody = '{"total": {"amount": "99.99", "currencyCode": "USD"}}';
        $request = new Request(
            'POST',
            'https://placeholder.example.com/transactions',
            [],
            $requestBody
        );

        // Act
        $elavonRequest = ElavonApiRequest::create($request)->withSandbox();

        // Assert
        $this->assertSame('POST', $elavonRequest->getMethod());
        $this->assertStringContainsString('/transactions', (string) $elavonRequest->getUri());
        $this->assertSame('application/json;charset=UTF-8', $elavonRequest->getHeaderLine('Accept'));
        $this->assertSame('application/json', $elavonRequest->getHeaderLine('Content-Type'));
        $this->assertSame('1', $elavonRequest->getHeaderLine('Accept-Version'));
        $this->assertSame($requestBody, (string) $elavonRequest->getBody());
    }

    public function test_withSandboxAndAuthentication_createsFullyDecoratedRequest(): void
    {
        // Arrange
        $request = new Request(
            'POST',
            'https://placeholder.example.com/transactions',
            [],
            '{"test": "data"}'
        );

        // Act - Use fluent interface to add all options
        $elavonRequest = ElavonApiRequest::create($request)
            ->withSandbox()
            ->withAuthentication('merchant', 'apikey');

        // Assert - Should have both Elavon API headers and Authorization
        $this->assertTrue($elavonRequest->hasHeader('Accept'));
        $this->assertTrue($elavonRequest->hasHeader('Content-Type'));
        $this->assertTrue($elavonRequest->hasHeader('Accept-Version'));
        $this->assertTrue($elavonRequest->hasHeader('Authorization'));
        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $elavonRequest->getUri());
    }

    public function test_getHeaders_includesAllAddedHeaders(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/test', [], '{"data": "test"}');

        // Act
        $elavonRequest = ElavonApiRequest::create($request);
        $headers = $elavonRequest->getHeaders();

        // Assert
        $this->assertArrayHasKey('accept', $headers);
        $this->assertArrayHasKey('content-type', $headers);
        $this->assertArrayHasKey('accept-version', $headers);
    }

    public function test_environmentConstants_areCorrect(): void
    {
        $this->assertSame('https://api.convergepay.com', ElavonApiRequest::ENVIRONMENT_PRODUCTION);
        $this->assertSame('https://uat.api.converge.eu.elavonaws.com', ElavonApiRequest::ENVIRONMENT_UAT);
        $this->assertSame(ElavonApiRequest::ENVIRONMENT_UAT, ElavonApiRequest::ENVIRONMENT_SANDBOX);
    }

    public function test_withAuthentication_addsAuthorizationHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');

        // Act
        $elavonRequest = ElavonApiRequest::create($request)
            ->withAuthentication('merchant123', 'secret-key');

        // Assert
        $this->assertTrue($elavonRequest->hasHeader('Authorization'));
        $authHeader = $elavonRequest->getHeaderLine('Authorization');
        $this->assertStringStartsWith('Basic ', $authHeader);
    }

    public function test_withAuthentication_encodesCredentialsCorrectly(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/transactions');
        $username = 'testmerchant';
        $password = 'testkey123';

        // Act
        $elavonRequest = ElavonApiRequest::create($request)
            ->withAuthentication($username, $password);

        // Assert
        $expected = 'Basic ' . base64_encode("{$username}:{$password}");
        $this->assertSame($expected, $elavonRequest->getHeaderLine('Authorization'));
    }

    public function test_withAuthentication_returnsNewInstance(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $original = ElavonApiRequest::create($request);

        // Act
        $authenticated = $original->withAuthentication('merchant', 'key');

        // Assert
        $this->assertNotSame($original, $authenticated);
        $this->assertFalse($original->hasHeader('Authorization'));
        $this->assertTrue($authenticated->hasHeader('Authorization'));
    }

    public function test_getUsername_returnsUsername(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $username = 'mymerchant';

        // Act
        $elavonRequest = ElavonApiRequest::create($request)
            ->withAuthentication($username, 'password');

        // Assert
        $this->assertSame($username, $elavonRequest->getUsername());
    }

    public function test_getUsername_returnsNullWhenNotAuthenticated(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');

        // Act
        $elavonRequest = ElavonApiRequest::create($request);

        // Assert
        $this->assertNull($elavonRequest->getUsername());
    }

    public function test_fluentInterface_withAllOptions(): void
    {
        // Arrange
        $request = new Request('POST', 'https://placeholder.example.com/transactions', [], '{"test": "data"}');

        // Act
        $elavonRequest = ElavonApiRequest::create($request)
            ->withApiVersion('2')
            ->withSandbox()
            ->withAuthentication('merchant', 'apikey');

        // Assert - All headers should be present
        $this->assertTrue($elavonRequest->hasHeader('Accept'));
        $this->assertTrue($elavonRequest->hasHeader('Content-Type'));
        $this->assertTrue($elavonRequest->hasHeader('Accept-Version'));
        $this->assertTrue($elavonRequest->hasHeader('Authorization'));
        $this->assertSame('2', $elavonRequest->getApiVersion());
        $this->assertSame('merchant', $elavonRequest->getUsername());
        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $elavonRequest->getUri());
    }

    public function test_authorizationHeader_isValidBase64(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $username = 'merchant';
        $password = 'password';

        // Act
        $elavonRequest = ElavonApiRequest::create($request)
            ->withAuthentication($username, $password);
        $authHeader = $elavonRequest->getHeaderLine('Authorization');

        // Assert
        $this->assertStringStartsWith('Basic ', $authHeader);
        $encodedPart = substr($authHeader, 6); // Remove 'Basic ' prefix
        $decoded = base64_decode($encodedPart, true);
        $this->assertNotFalse($decoded, 'Authorization header should contain valid base64');
        $this->assertSame("{$username}:{$password}", $decoded);
    }

    public function test_withMessage_preservesAllConfiguration(): void
    {
        // Arrange
        $firstRequest = new Request('POST', 'https://placeholder.example.com/transactions', [], '{"test": "1"}');
        $secondRequest = new Request('POST', 'https://placeholder.example.com/payments', [], '{"test": "2"}');

        // Configure the first request
        $elavonRequest1 = ElavonApiRequest::create($firstRequest, '2')
            ->withSandbox()
            ->withAuthentication('merchant', 'apikey');

        // Act - Reuse configuration with second request
        $elavonRequest2 = $elavonRequest1->withMessage($secondRequest);

        // Assert - Second request should have same configuration
        $this->assertNotSame($elavonRequest1, $elavonRequest2);
        $this->assertSame('2', $elavonRequest2->getApiVersion());
        $this->assertSame('merchant', $elavonRequest2->getUsername());
        $this->assertTrue($elavonRequest2->hasHeader('Authorization'));
        $this->assertTrue($elavonRequest2->hasHeader('Accept-Version'));
        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $elavonRequest2->getUri());
    }

    public function test_withMessage_preservesBaseUri(): void
    {
        // Arrange
        $firstRequest = new Request('GET', 'https://placeholder.example.com/transactions');
        $secondRequest = new Request('GET', 'https://different.example.com/payments');

        $elavonRequest1 = ElavonApiRequest::create($firstRequest)
            ->withBaseUri('https://uat.api.converge.eu.elavonaws.com');

        // Act
        $elavonRequest2 = $elavonRequest1->withMessage($secondRequest);

        // Assert - Should have UAT base URI, not the original URIs
        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $elavonRequest2->getUri());
        $this->assertStringContainsString('/payments', (string) $elavonRequest2->getUri());
        $this->assertStringNotContainsString('different.example.com', (string) $elavonRequest2->getUri());
    }

    public function test_withMessage_preservesRequestBodyAndMethod(): void
    {
        // Arrange
        $firstRequest = new Request('POST', 'https://api.example.com/transactions', [], '{"first": "request"}');
        $secondRequest = new Request('GET', 'https://api.example.com/payments', [], '{"second": "request"}');

        $elavonRequest1 = ElavonApiRequest::create($firstRequest);

        // Act
        $elavonRequest2 = $elavonRequest1->withMessage($secondRequest);

        // Assert - Should have second request's body and method
        $this->assertSame('GET', $elavonRequest2->getMethod());
        $this->assertSame('{"second": "request"}', (string) $elavonRequest2->getBody());
    }

    public function test_withMessage_returnsNewInstance(): void
    {
        // Arrange
        $firstRequest = new Request('POST', 'https://api.example.com/transactions');
        $secondRequest = new Request('GET', 'https://api.example.com/payments');

        $elavonRequest1 = ElavonApiRequest::create($firstRequest)
            ->withAuthentication('merchant', 'key');

        // Act
        $elavonRequest2 = $elavonRequest1->withMessage($secondRequest);

        // Assert - Should be different instances
        $this->assertNotSame($elavonRequest1, $elavonRequest2);
        // But both should have authentication
        $this->assertTrue($elavonRequest1->hasHeader('Authorization'));
        $this->assertTrue($elavonRequest2->hasHeader('Authorization'));
    }

    public function test_configure_createsConfigurationBuilder(): void
    {
        // Act
        $decorator = ElavonApiRequest::configure();

        // Assert - Should have default API version
        $this->assertSame('1', $decorator->getApiVersion());
        $this->assertNull($decorator->getUsername());
    }

    public function test_configure_withCustomApiVersion(): void
    {
        // Act
        $decorator = ElavonApiRequest::configure('2');

        // Assert
        $this->assertSame('2', $decorator->getApiVersion());
    }

    public function test_configure_thenApplyToMultipleMessages(): void
    {
        // Arrange
        $request1 = new Request('POST', 'https://placeholder.example.com/transactions', [], '{"test": "1"}');
        $request2 = new Request('GET', 'https://placeholder.example.com/payments');
        $request3 = new Request('PUT', 'https://placeholder.example.com/refunds', [], '{"test": "3"}');

        // Act - Configure once
        $decorator = ElavonApiRequest::configure('2')
            ->withSandbox()
            ->withAuthentication('merchant', 'apikey');

        // Apply to multiple requests
        $elavonRequest1 = $decorator->withMessage($request1);
        $elavonRequest2 = $decorator->withMessage($request2);
        $elavonRequest3 = $decorator->withMessage($request3);

        // Assert - All should have same configuration
        $this->assertSame('2', $elavonRequest1->getApiVersion());
        $this->assertSame('2', $elavonRequest2->getApiVersion());
        $this->assertSame('2', $elavonRequest3->getApiVersion());

        $this->assertSame('merchant', $elavonRequest1->getUsername());
        $this->assertSame('merchant', $elavonRequest2->getUsername());
        $this->assertSame('merchant', $elavonRequest3->getUsername());

        $this->assertTrue($elavonRequest1->hasHeader('Authorization'));
        $this->assertTrue($elavonRequest2->hasHeader('Authorization'));
        $this->assertTrue($elavonRequest3->hasHeader('Authorization'));

        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $elavonRequest1->getUri());
        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $elavonRequest2->getUri());
        $this->assertStringContainsString('uat.api.converge.eu.elavonaws.com', (string) $elavonRequest3->getUri());

        // But each should have different paths
        $this->assertStringContainsString('/transactions', (string) $elavonRequest1->getUri());
        $this->assertStringContainsString('/payments', (string) $elavonRequest2->getUri());
        $this->assertStringContainsString('/refunds', (string) $elavonRequest3->getUri());
    }

    public function test_configure_preservesConfigurationAcrossMessages(): void
    {
        // Arrange
        $decorator = ElavonApiRequest::configure()
            ->withProduction()
            ->withAuthentication('test-merchant', 'test-key');

        $request1 = new Request('POST', 'https://example.com/first');
        $request2 = new Request('POST', 'https://example.com/second');

        // Act
        $elavonRequest1 = $decorator->withMessage($request1);
        $elavonRequest2 = $decorator->withMessage($request2);

        // Assert - Original decorator unchanged
        $this->assertNotSame($decorator, $elavonRequest1);
        $this->assertNotSame($decorator, $elavonRequest2);
        $this->assertNotSame($elavonRequest1, $elavonRequest2);

        // Both should have production environment (EU by default)
        $this->assertStringContainsString('api.eu.convergepay.com', (string) $elavonRequest1->getUri());
        $this->assertStringContainsString('api.eu.convergepay.com', (string) $elavonRequest2->getUri());
    }
}
