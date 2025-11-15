<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Tests\Unit\Support;

use Academe\Elavon\Epg\Psr7\Support\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * Tests for Stream implementation.
 */
class StreamTest extends TestCase
{
    public function test_implementsStreamInterface(): void
    {
        // Arrange & Act
        $stream = new Stream('test');

        // Assert
        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    public function test_construct_withString_createsStream(): void
    {
        // Arrange & Act
        $stream = new Stream('Hello, World!');

        // Assert
        $this->assertSame('Hello, World!', (string) $stream);
    }

    public function test_construct_withEmptyString_createsEmptyStream(): void
    {
        // Arrange & Act
        $stream = new Stream('');

        // Assert
        $this->assertSame('', (string) $stream);
    }

    public function test_construct_withResource_createsStream(): void
    {
        // Arrange
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'Resource content');
        rewind($resource);

        // Act
        $stream = new Stream($resource);

        // Assert
        $this->assertSame('Resource content', (string) $stream);

        // Cleanup
        fclose($resource);
    }

    public function test_construct_withInvalidType_throwsException(): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Stream must be a resource or string');

        // Act
        new Stream(123);
    }

    public function test_toString_returnsContents(): void
    {
        // Arrange
        $stream = new Stream('Test content');

        // Act
        $result = (string) $stream;

        // Assert
        $this->assertSame('Test content', $result);
    }

    public function test_getContents_returnsRemainingContents(): void
    {
        // Arrange
        $stream = new Stream('Hello, World!');
        $stream->read(7); // Read "Hello, "

        // Act
        $contents = $stream->getContents();

        // Assert
        $this->assertSame('World!', $contents);
    }

    public function test_getSize_returnsCorrectSize(): void
    {
        // Arrange
        $stream = new Stream('Hello');

        // Act
        $size = $stream->getSize();

        // Assert
        $this->assertSame(5, $size);
    }

    public function test_tell_returnsCurrentPosition(): void
    {
        // Arrange
        $stream = new Stream('Hello, World!');
        $stream->read(5);

        // Act
        $position = $stream->tell();

        // Assert
        $this->assertSame(5, $position);
    }

    public function test_eof_returnsFalseWhenNotAtEnd(): void
    {
        // Arrange
        $stream = new Stream('Hello');

        // Act & Assert
        $this->assertFalse($stream->eof());
    }

    public function test_eof_returnsTrueWhenAtEnd(): void
    {
        // Arrange
        $stream = new Stream('Hello');
        $stream->getContents();

        // Act & Assert
        $this->assertTrue($stream->eof());
    }

    public function test_isSeekable_returnsTrue(): void
    {
        // Arrange
        $stream = new Stream('test');

        // Act & Assert
        $this->assertTrue($stream->isSeekable());
    }

    public function test_seek_changesPosition(): void
    {
        // Arrange
        $stream = new Stream('Hello, World!');

        // Act
        $stream->seek(7);
        $result = $stream->getContents();

        // Assert
        $this->assertSame('World!', $result);
    }

    public function test_rewind_resetsPositionToStart(): void
    {
        // Arrange
        $stream = new Stream('Hello');
        $stream->read(3);

        // Act
        $stream->rewind();
        $result = $stream->getContents();

        // Assert
        $this->assertSame('Hello', $result);
    }

    public function test_isWritable_returnsTrue(): void
    {
        // Arrange
        $stream = new Stream('test');

        // Act & Assert
        $this->assertTrue($stream->isWritable());
    }

    public function test_write_addsContent(): void
    {
        // Arrange
        $stream = new Stream('');

        // Act
        $bytesWritten = $stream->write('Hello');

        // Assert
        $this->assertSame(5, $bytesWritten);
        $this->assertSame('Hello', (string) $stream);
    }

    public function test_write_appendsContent(): void
    {
        // Arrange
        $stream = new Stream('Hello');
        $stream->seek(0, SEEK_END);

        // Act
        $stream->write(', World!');

        // Assert
        $this->assertSame('Hello, World!', (string) $stream);
    }

    public function test_isReadable_returnsTrue(): void
    {
        // Arrange
        $stream = new Stream('test');

        // Act & Assert
        $this->assertTrue($stream->isReadable());
    }

    public function test_read_returnsRequestedBytes(): void
    {
        // Arrange
        $stream = new Stream('Hello, World!');

        // Act
        $result = $stream->read(5);

        // Assert
        $this->assertSame('Hello', $result);
    }

    public function test_read_multipleReads_returnsSequentialContent(): void
    {
        // Arrange
        $stream = new Stream('Hello, World!');

        // Act
        $part1 = $stream->read(7);
        $part2 = $stream->read(6);

        // Assert
        $this->assertSame('Hello, ', $part1);
        $this->assertSame('World!', $part2);
    }

    public function test_close_closesStream(): void
    {
        // Arrange
        $stream = new Stream('test');

        // Act
        $stream->close();

        // Assert
        $this->assertTrue($stream->eof());
    }

    public function test_detach_returnsResource(): void
    {
        // Arrange
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);

        // Act
        $detached = $stream->detach();

        // Assert
        $this->assertSame($resource, $detached);
        $this->assertNull($stream->getSize());

        // Cleanup
        fclose($resource);
    }

    public function test_detach_makesStreamUnusable(): void
    {
        // Arrange
        $stream = new Stream('test');
        $stream->detach();

        // Assert
        $this->expectException(\RuntimeException::class);

        // Act
        $stream->tell();
    }

    public function test_getMetadata_withoutKey_returnsAllMetadata(): void
    {
        // Arrange
        $stream = new Stream('test');

        // Act
        $metadata = $stream->getMetadata();

        // Assert
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('mode', $metadata);
    }

    public function test_getMetadata_withKey_returnsSingleValue(): void
    {
        // Arrange
        $stream = new Stream('test');

        // Act
        $mode = $stream->getMetadata('mode');

        // Assert
        $this->assertIsString($mode);
    }

    public function test_getMetadata_withInvalidKey_returnsNull(): void
    {
        // Arrange
        $stream = new Stream('test');

        // Act
        $result = $stream->getMetadata('nonexistent_key');

        // Assert
        $this->assertNull($result);
    }

    public function test_getMetadata_afterDetach_returnsEmptyArray(): void
    {
        // Arrange
        $stream = new Stream('test');
        $stream->detach();

        // Act
        $metadata = $stream->getMetadata();

        // Assert
        $this->assertSame([], $metadata);
    }

    public function test_getMetadata_afterDetachWithKey_returnsNull(): void
    {
        // Arrange
        $stream = new Stream('test');
        $stream->detach();

        // Act
        $result = $stream->getMetadata('mode');

        // Assert
        $this->assertNull($result);
    }

    public function test_toString_afterException_returnsEmptyString(): void
    {
        // Arrange
        $stream = new Stream('test');
        $stream->detach();

        // Act
        $result = (string) $stream;

        // Assert
        $this->assertSame('', $result);
    }

    public function test_seek_afterDetach_throwsException(): void
    {
        // Arrange
        $stream = new Stream('test');
        $stream->detach();

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable');

        // Act
        $stream->seek(0);
    }

    public function test_write_afterDetach_throwsException(): void
    {
        // Arrange
        $stream = new Stream('test');
        $stream->detach();

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable');

        // Act
        $stream->write('content');
    }

    public function test_read_afterDetach_throwsException(): void
    {
        // Arrange
        $stream = new Stream('test');
        $stream->detach();

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');

        // Act
        $stream->read(5);
    }

    public function test_getContents_afterDetach_throwsException(): void
    {
        // Arrange
        $stream = new Stream('test');
        $stream->detach();

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');

        // Act
        $stream->getContents();
    }
}