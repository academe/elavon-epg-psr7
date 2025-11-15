<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Support;

use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Academe\Elavon\Epg\Psr7\Support\Request;
use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Tests for Psr17Factory.
 */
class Psr17FactoryTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    public function test_implementsRequestFactoryInterface(): void
    {
        // Assert
        $this->assertInstanceOf(RequestFactoryInterface::class, $this->factory);
    }

    public function test_implementsStreamFactoryInterface(): void
    {
        // Assert
        $this->assertInstanceOf(StreamFactoryInterface::class, $this->factory);
    }

    public function test_createRequest_withMethodAndUri_returnsRequest(): void
    {
        // Act
        $request = $this->factory->createRequest('GET', 'https://example.com');

        // Assert
        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://example.com', (string) $request->getUri());
    }

    public function test_createRequest_withPostMethod_returnsRequest(): void
    {
        // Act
        $request = $this->factory->createRequest('POST', 'https://api.example.com/transactions');

        // Assert
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://api.example.com/transactions', (string) $request->getUri());
    }

    public function test_createRequest_withLowercaseMethod_normalizesToUppercase(): void
    {
        // Act
        $request = $this->factory->createRequest('post', 'https://example.com');

        // Assert
        $this->assertSame('POST', $request->getMethod());
    }

    public function test_createStream_withEmptyString_returnsStream(): void
    {
        // Act
        $stream = $this->factory->createStream();

        // Assert
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('', (string) $stream);
    }

    public function test_createStream_withContent_returnsStreamWithContent(): void
    {
        // Arrange
        $content = 'Hello, World!';

        // Act
        $stream = $this->factory->createStream($content);

        // Assert
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame($content, (string) $stream);
    }

    public function test_createStream_withJsonContent_returnsStreamWithJson(): void
    {
        // Arrange
        $content = json_encode(['key' => 'value']);

        // Act
        $stream = $this->factory->createStream($content);

        // Assert
        $this->assertSame($content, (string) $stream);
    }

    public function test_createStreamFromFile_withValidFile_returnsStream(): void
    {
        // Arrange
        $tempFile = tempnam(sys_get_temp_dir(), 'psr7_test_');
        file_put_contents($tempFile, 'Test content');

        // Act
        $stream = $this->factory->createStreamFromFile($tempFile);

        // Assert
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('Test content', (string) $stream);

        // Cleanup
        unlink($tempFile);
    }

    public function test_createStreamFromFile_withWriteMode_returnsWritableStream(): void
    {
        // Arrange
        $tempFile = tempnam(sys_get_temp_dir(), 'psr7_test_');

        // Act
        $stream = $this->factory->createStreamFromFile($tempFile, 'w');
        $stream->write('New content');

        // Assert
        $stream->rewind();
        $this->assertSame('New content', (string) $stream);

        // Cleanup
        unlink($tempFile);
    }

    public function test_createStreamFromFile_withNonexistentFile_throwsException(): void
    {
        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to open file');

        // Act
        $this->factory->createStreamFromFile('/nonexistent/file.txt');
    }

    public function test_createStreamFromResource_withValidResource_returnsStream(): void
    {
        // Arrange
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'Resource content');
        rewind($resource);

        // Act
        $stream = $this->factory->createStreamFromResource($resource);

        // Assert
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('Resource content', (string) $stream);

        // Cleanup
        fclose($resource);
    }

    public function test_createStreamFromResource_withMemoryResource_returnsStream(): void
    {
        // Arrange
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'Memory stream');
        rewind($resource);

        // Act
        $stream = $this->factory->createStreamFromResource($resource);

        // Assert
        $this->assertSame('Memory stream', (string) $stream);

        // Cleanup
        fclose($resource);
    }

    public function test_multipleFactoryInstances_workIndependently(): void
    {
        // Arrange
        $factory1 = new Psr17Factory();
        $factory2 = new Psr17Factory();

        // Act
        $stream1 = $factory1->createStream('Content 1');
        $stream2 = $factory2->createStream('Content 2');

        // Assert
        $this->assertSame('Content 1', (string) $stream1);
        $this->assertSame('Content 2', (string) $stream2);
        $this->assertNotSame($stream1, $stream2);
    }
}