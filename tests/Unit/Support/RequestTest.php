<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Support;

use Academe\Elavon\Epg\Psr7\Support\Request;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Tests for Request implementation.
 */
class RequestTest extends TestCase
{
    public function test_implementsRequestInterface(): void
    {
        // Arrange & Act
        $request = new Request('GET', 'https://example.com');

        // Assert
        $this->assertInstanceOf(RequestInterface::class, $request);
    }

    public function test_construct_withMethodAndUri_createsRequest(): void
    {
        // Arrange & Act
        $request = new Request('POST', 'https://api.example.com/transactions');

        // Assert
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://api.example.com/transactions', (string) $request->getUri());
    }

    public function test_construct_normalizesMethodToUppercase(): void
    {
        // Arrange & Act
        $request = new Request('post', 'https://example.com');

        // Assert
        $this->assertSame('POST', $request->getMethod());
    }

    public function test_construct_withHeaders_setsHeaders(): void
    {
        // Arrange & Act
        $request = new Request(
            'GET',
            'https://example.com',
            ['Content-Type' => 'application/json', 'Accept' => 'application/json']
        );

        // Assert
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $request->getHeaderLine('Accept'));
    }

    public function test_construct_withArrayHeaderValue_setsHeaderArray(): void
    {
        // Arrange & Act
        $request = new Request(
            'GET',
            'https://example.com',
            ['Accept' => ['application/json', 'text/html']]
        );

        // Act
        $values = $request->getHeader('Accept');

        // Assert
        $this->assertSame(['application/json', 'text/html'], $values);
    }

    public function test_construct_withStringBody_createsStream(): void
    {
        // Arrange & Act
        $request = new Request('POST', 'https://example.com', [], 'test body');

        // Assert
        $this->assertSame('test body', (string) $request->getBody());
    }

    public function test_construct_withStreamBody_usesStream(): void
    {
        // Arrange
        $stream = new Stream('stream body');

        // Act
        $request = new Request('POST', 'https://example.com', [], $stream);

        // Assert
        $this->assertSame($stream, $request->getBody());
    }

    public function test_construct_withNullBody_createsEmptyStream(): void
    {
        // Arrange & Act
        $request = new Request('GET', 'https://example.com');

        // Assert
        $this->assertSame('', (string) $request->getBody());
    }

    public function test_getMethod_returnsMethod(): void
    {
        // Arrange
        $request = new Request('DELETE', 'https://example.com');

        // Act
        $method = $request->getMethod();

        // Assert
        $this->assertSame('DELETE', $method);
    }

    public function test_withMethod_createsNewInstance(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $newRequest = $request->withMethod('POST');

        // Assert
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('POST', $newRequest->getMethod());
        $this->assertNotSame($request, $newRequest);
    }

    public function test_withMethod_normalizesToUppercase(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $newRequest = $request->withMethod('patch');

        // Assert
        $this->assertSame('PATCH', $newRequest->getMethod());
    }

    public function test_getUri_returnsUri(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com/path');

        // Act
        $uri = $request->getUri();

        // Assert
        $this->assertSame('https://example.com/path', (string) $uri);
    }

    public function test_getProtocolVersion_returnsDefaultVersion(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $version = $request->getProtocolVersion();

        // Assert
        $this->assertSame('1.1', $version);
    }

    public function test_withProtocolVersion_createsNewInstance(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $newRequest = $request->withProtocolVersion('2.0');

        // Assert
        $this->assertSame('1.1', $request->getProtocolVersion());
        $this->assertSame('2.0', $newRequest->getProtocolVersion());
        $this->assertNotSame($request, $newRequest);
    }

    public function test_getHeaders_returnsAllHeaders(): void
    {
        // Arrange
        $request = new Request(
            'GET',
            'https://example.com',
            ['Content-Type' => 'application/json', 'Accept' => 'text/html']
        );

        // Act
        $headers = $request->getHeaders();

        // Assert
        $this->assertArrayHasKey('content-type', $headers);
        $this->assertArrayHasKey('accept', $headers);
        $this->assertSame(['application/json'], $headers['content-type']);
        $this->assertSame(['text/html'], $headers['accept']);
    }

    public function test_hasHeader_withExistingHeader_returnsTrue(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com', ['Content-Type' => 'application/json']);

        // Act & Assert
        $this->assertTrue($request->hasHeader('Content-Type'));
    }

    public function test_hasHeader_withNonexistentHeader_returnsFalse(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act & Assert
        $this->assertFalse($request->hasHeader('Authorization'));
    }

    public function test_hasHeader_isCaseInsensitive(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com', ['Content-Type' => 'application/json']);

        // Act & Assert
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertTrue($request->hasHeader('CONTENT-TYPE'));
        $this->assertTrue($request->hasHeader('Content-Type'));
    }

    public function test_getHeader_returnsHeaderValues(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com', ['Accept' => ['application/json', 'text/html']]);

        // Act
        $values = $request->getHeader('Accept');

        // Assert
        $this->assertSame(['application/json', 'text/html'], $values);
    }

    public function test_getHeader_withNonexistentHeader_returnsEmptyArray(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $values = $request->getHeader('Authorization');

        // Assert
        $this->assertSame([], $values);
    }

    public function test_getHeaderLine_returnsCommaSeparatedValues(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com', ['Accept' => ['application/json', 'text/html']]);

        // Act
        $line = $request->getHeaderLine('Accept');

        // Assert
        $this->assertSame('application/json, text/html', $line);
    }

    public function test_getHeaderLine_withNonexistentHeader_returnsEmptyString(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $line = $request->getHeaderLine('Authorization');

        // Assert
        $this->assertSame('', $line);
    }

    public function test_withHeader_setsHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $newRequest = $request->withHeader('Content-Type', 'application/json');

        // Assert
        $this->assertFalse($request->hasHeader('Content-Type'));
        $this->assertTrue($newRequest->hasHeader('Content-Type'));
        $this->assertSame('application/json', $newRequest->getHeaderLine('Content-Type'));
    }

    public function test_withHeader_replacesExistingHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com', ['Content-Type' => 'text/html']);

        // Act
        $newRequest = $request->withHeader('Content-Type', 'application/json');

        // Assert
        $this->assertSame('text/html', $request->getHeaderLine('Content-Type'));
        $this->assertSame('application/json', $newRequest->getHeaderLine('Content-Type'));
    }

    public function test_withHeader_withArrayValue_setsMultipleValues(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $newRequest = $request->withHeader('Accept', ['application/json', 'text/html']);

        // Assert
        $this->assertSame(['application/json', 'text/html'], $newRequest->getHeader('Accept'));
    }

    public function test_withAddedHeader_addsHeaderValue(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com', ['Accept' => 'application/json']);

        // Act
        $newRequest = $request->withAddedHeader('Accept', 'text/html');

        // Assert
        $this->assertSame(['application/json'], $request->getHeader('Accept'));
        $this->assertSame(['application/json', 'text/html'], $newRequest->getHeader('Accept'));
    }

    public function test_withAddedHeader_createsHeaderIfNotExists(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $newRequest = $request->withAddedHeader('Accept', 'application/json');

        // Assert
        $this->assertSame(['application/json'], $newRequest->getHeader('Accept'));
    }

    public function test_withoutHeader_removesHeader(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com', ['Content-Type' => 'application/json']);

        // Act
        $newRequest = $request->withoutHeader('Content-Type');

        // Assert
        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertFalse($newRequest->hasHeader('Content-Type'));
    }

    public function test_withoutHeader_withNonexistentHeader_doesNotError(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $newRequest = $request->withoutHeader('Nonexistent');

        // Assert
        $this->assertInstanceOf(Request::class, $newRequest);
    }

    public function test_getBody_returnsStream(): void
    {
        // Arrange
        $request = new Request('POST', 'https://example.com', [], 'test body');

        // Act
        $body = $request->getBody();

        // Assert
        $this->assertInstanceOf(Stream::class, $body);
        $this->assertSame('test body', (string) $body);
    }

    public function test_withBody_replacesBody(): void
    {
        // Arrange
        $request = new Request('POST', 'https://example.com', [], 'original');
        $newBody = new Stream('new body');

        // Act
        $newRequest = $request->withBody($newBody);

        // Assert
        $this->assertSame('original', (string) $request->getBody());
        $this->assertSame('new body', (string) $newRequest->getBody());
        $this->assertNotSame($request, $newRequest);
    }

    public function test_getRequestTarget_returnsPath(): void
    {
        // Arrange
        $request = new Request('GET', '/path/to/resource');

        // Act
        $target = $request->getRequestTarget();

        // Assert
        $this->assertSame('/path/to/resource', $target);
    }

    public function test_getRequestTarget_withRootPath_returnsSlash(): void
    {
        // Arrange
        $request = new Request('GET', '/');

        // Act
        $target = $request->getRequestTarget();

        // Assert
        $this->assertSame('/', $target);
    }

    public function test_immutability_withMethodsReturnNewInstances(): void
    {
        // Arrange
        $request = new Request('GET', 'https://example.com');

        // Act
        $request2 = $request->withMethod('POST');
        $request3 = $request->withHeader('Content-Type', 'application/json');
        $request4 = $request->withProtocolVersion('2.0');

        // Assert
        $this->assertNotSame($request, $request2);
        $this->assertNotSame($request, $request3);
        $this->assertNotSame($request, $request4);
        $this->assertSame('GET', $request->getMethod());
    }
}