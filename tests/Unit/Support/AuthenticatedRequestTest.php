<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Support;

use Academe\Elavon\Epg\Psr7\Support\AuthenticatedRequest;
use Academe\Elavon\Epg\Psr7\Support\Request;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use Academe\Elavon\Epg\Psr7\Support\Uri;
use PHPUnit\Framework\TestCase;

class AuthenticatedRequestTest extends TestCase
{
    public function test_construct_addsAuthorizationHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');

        // Act
        $authenticated = new AuthenticatedRequest($request, 'merchant123', 'secret-key');

        // Assert
        $this->assertTrue($authenticated->hasHeader('Authorization'));
        $authHeader = $authenticated->getHeaderLine('Authorization');
        $this->assertStringStartsWith('Basic ', $authHeader);
    }

    public function test_construct_encodesCredentialsCorrectly(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/transactions');
        $username = 'testmerchant';
        $password = 'testkey123';

        // Act
        $authenticated = new AuthenticatedRequest($request, $username, $password);

        // Assert
        $expected = 'Basic ' . base64_encode("{$username}:{$password}");
        $this->assertSame($expected, $authenticated->getHeaderLine('Authorization'));
    }

    public function test_getUsername_returnsUsername(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $username = 'mymerchant';

        // Act
        $authenticated = new AuthenticatedRequest($request, $username, 'password');

        // Assert
        $this->assertSame($username, $authenticated->getUsername());
    }

    public function test_preservesOriginalRequestMethod(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/transactions');

        // Act
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Assert
        $this->assertSame('POST', $authenticated->getMethod());
    }

    public function test_preservesOriginalRequestUri(): void
    {
        // Arrange
        $uri = 'https://api.example.com/transactions';
        $request = new Request('GET', $uri);

        // Act
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Assert
        $this->assertSame($uri, (string) $authenticated->getUri());
    }

    public function test_preservesOriginalRequestBody(): void
    {
        // Arrange
        $body = '{"amount": "99.99"}';
        $request = new Request('POST', 'https://api.example.com/test', [], $body);

        // Act
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Assert
        $this->assertSame($body, (string) $authenticated->getBody());
    }

    public function test_preservesOriginalRequestHeaders(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test', [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        // Act
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Assert
        $this->assertSame('application/json', $authenticated->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $authenticated->getHeaderLine('Accept'));
    }

    public function test_withHeader_returnsNewInstanceWithHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Act
        $newAuthenticated = $authenticated->withHeader('X-Custom', 'value');

        // Assert
        $this->assertNotSame($authenticated, $newAuthenticated);
        $this->assertFalse($authenticated->hasHeader('X-Custom'));
        $this->assertTrue($newAuthenticated->hasHeader('X-Custom'));
        $this->assertSame('value', $newAuthenticated->getHeaderLine('X-Custom'));
    }

    public function test_withAddedHeader_returnsNewInstanceWithAddedHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test', [
            'Accept' => 'application/json',
        ]);
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Act
        $newAuthenticated = $authenticated->withAddedHeader('Accept', 'text/html');

        // Assert
        $this->assertNotSame($authenticated, $newAuthenticated);
        $acceptHeaders = $newAuthenticated->getHeader('Accept');
        $this->assertContains('application/json', $acceptHeaders);
        $this->assertContains('text/html', $acceptHeaders);
    }

    public function test_withoutHeader_returnsNewInstanceWithoutHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test', [
            'X-Custom' => 'value',
        ]);
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Act
        $newAuthenticated = $authenticated->withoutHeader('X-Custom');

        // Assert
        $this->assertNotSame($authenticated, $newAuthenticated);
        $this->assertTrue($authenticated->hasHeader('X-Custom'));
        $this->assertFalse($newAuthenticated->hasHeader('X-Custom'));
    }

    public function test_withoutHeader_cannotRemoveAuthorizationHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Act
        $newAuthenticated = $authenticated->withoutHeader('Authorization');

        // Assert - Authorization header is removed from the decorated request
        $this->assertFalse($newAuthenticated->hasHeader('Authorization'));
    }

    public function test_withBody_returnsNewInstanceWithBody(): void
    {
        // Arrange
        $request = new Request('POST', 'https://api.example.com/test');
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');
        $newBody = new Stream('{"updated": true}');

        // Act
        $newAuthenticated = $authenticated->withBody($newBody);

        // Assert
        $this->assertNotSame($authenticated, $newAuthenticated);
        $this->assertSame('{"updated": true}', (string) $newAuthenticated->getBody());
    }

    public function test_withMethod_returnsNewInstanceWithMethod(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Act
        $newAuthenticated = $authenticated->withMethod('PUT');

        // Assert
        $this->assertNotSame($authenticated, $newAuthenticated);
        $this->assertSame('GET', $authenticated->getMethod());
        $this->assertSame('PUT', $newAuthenticated->getMethod());
    }

    public function test_withUri_returnsNewInstanceWithUri(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');
        $newUri = new Uri('https://api.example.com/updated');

        // Act
        $newAuthenticated = $authenticated->withUri($newUri);

        // Assert
        $this->assertNotSame($authenticated, $newAuthenticated);
        $this->assertSame('https://api.example.com/test', (string) $authenticated->getUri());
        $this->assertSame('https://api.example.com/updated', (string) $newAuthenticated->getUri());
    }

    public function test_withProtocolVersion_returnsNewInstanceWithVersion(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');

        // Act
        $newAuthenticated = $authenticated->withProtocolVersion('2.0');

        // Assert
        $this->assertNotSame($authenticated, $newAuthenticated);
        $this->assertSame('1.1', $authenticated->getProtocolVersion());
        $this->assertSame('2.0', $newAuthenticated->getProtocolVersion());
    }

    public function test_getHeaders_includesAuthorizationHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test', [
            'Content-Type' => 'application/json',
        ]);

        // Act
        $authenticated = new AuthenticatedRequest($request, 'merchant', 'key');
        $headers = $authenticated->getHeaders();

        // Assert
        $this->assertArrayHasKey('authorization', $headers);
        $this->assertArrayHasKey('content-type', $headers);
    }

    public function test_realWorldUsage_withCreateTransactionRequest(): void
    {
        // Arrange
        $requestBody = '{"total": {"amount": "99.99", "currencyCode": "USD"}}';
        $request = new Request(
            'POST',
            'https://api.eu.elavonpayments.com/transactions',
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            $requestBody
        );

        // Act
        $authenticated = new AuthenticatedRequest($request, 'my-merchant-alias', 'my-api-key');

        // Assert
        $this->assertSame('POST', $authenticated->getMethod());
        $this->assertSame('https://api.eu.elavonpayments.com/transactions', (string) $authenticated->getUri());
        $this->assertSame('application/json', $authenticated->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $authenticated->getHeaderLine('Accept'));
        $this->assertTrue($authenticated->hasHeader('Authorization'));
        $this->assertStringStartsWith('Basic ', $authenticated->getHeaderLine('Authorization'));
        $this->assertSame($requestBody, (string) $authenticated->getBody());
        $this->assertSame('my-merchant-alias', $authenticated->getUsername());
    }

    public function test_authorizationHeader_isValidBase64(): void
    {
        // Arrange
        $request = new Request('GET', 'https://api.example.com/test');
        $username = 'merchant';
        $password = 'password';

        // Act
        $authenticated = new AuthenticatedRequest($request, $username, $password);
        $authHeader = $authenticated->getHeaderLine('Authorization');

        // Assert
        $this->assertStringStartsWith('Basic ', $authHeader);
        $encodedPart = substr($authHeader, 6); // Remove 'Basic ' prefix
        $decoded = base64_decode($encodedPart, true);
        $this->assertNotFalse($decoded, 'Authorization header should contain valid base64');
        $this->assertSame("{$username}:{$password}", $decoded);
    }
}
