<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Support;

use Psr\Http\Message\StreamInterface;

/**
 * Simple PSR-7 Stream implementation.
 *
 * Provides a minimal implementation for HTTP message bodies.
 */
class Stream implements StreamInterface
{
    /** @var resource|null */
    private $resource;

    private bool $seekable;
    private bool $readable;
    private bool $writable;

    /**
     * @param resource|string $stream Stream resource or string content
     */
    public function __construct($stream = '')
    {
        if (is_string($stream)) {
            $this->resource = fopen('php://temp', 'r+');
            if ($stream !== '') {
                fwrite($this->resource, $stream);
                rewind($this->resource);
            }
        } elseif (is_resource($stream)) {
            $this->resource = $stream;
        } else {
            throw new \InvalidArgumentException('Stream must be a resource or string');
        }

        // Determine capabilities from stream metadata
        $this->initializeStreamCapabilities();
    }

    /**
     * Initialize stream capabilities based on metadata.
     */
    private function initializeStreamCapabilities(): void
    {
        if ($this->resource === null) {
            $this->seekable = false;
            $this->readable = false;
            $this->writable = false;
            return;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'] ?? '';

        // Check if stream is seekable
        $this->seekable = $meta['seekable'] ?? false;

        // Determine readable/writable from mode
        // Modes: r, r+, w, w+, a, a+, x, x+, c, c+
        $this->readable = str_contains($mode, 'r') || str_contains($mode, '+');
        $this->writable = str_contains($mode, 'w') || str_contains($mode, 'a') ||
                         str_contains($mode, 'x') || str_contains($mode, 'c') ||
                         str_contains($mode, '+');
    }

    public function __toString(): string
    {
        try {
            // If stream is not readable, try to read from the underlying file
            if (!$this->isReadable()) {
                $meta = stream_get_meta_data($this->resource);
                $uri = $meta['uri'] ?? null;

                // If we have a file path, read from it directly
                if ($uri !== null && file_exists($uri)) {
                    return (string) file_get_contents($uri);
                }

                return '';
            }

            $this->rewind();
            return $this->getContents();
        } catch (\Throwable) {
            return '';
        }
    }

    public function close(): void
    {
        if ($this->resource !== null) {
            fclose($this->resource);
            $this->detach();
        }
    }

    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    public function getSize(): ?int
    {
        if ($this->resource === null) {
            return null;
        }

        $stats = fstat($this->resource);
        return $stats['size'] ?? null;
    }

    public function tell(): int
    {
        if ($this->resource === null) {
            throw new \RuntimeException('Stream is detached');
        }

        $position = ftell($this->resource);
        if ($position === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $position;
    }

    public function eof(): bool
    {
        return $this->resource === null || feof($this->resource);
    }

    public function isSeekable(): bool
    {
        return $this->seekable && $this->resource !== null;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position');
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return $this->writable && $this->resource !== null;
    }

    public function write(string $string): int
    {
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable');
        }

        $result = fwrite($this->resource, $string);
        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    public function isReadable(): bool
    {
        return $this->readable && $this->resource !== null;
    }

    public function read(int $length): string
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        $result = fread($this->resource, $length);
        if ($result === false) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $result;
    }

    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        $contents = stream_get_contents($this->resource);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    public function getMetadata(?string $key = null)
    {
        if ($this->resource === null) {
            return $key === null ? [] : null;
        }

        $metadata = stream_get_meta_data($this->resource);

        if ($key === null) {
            return $metadata;
        }

        return $metadata[$key] ?? null;
    }
}