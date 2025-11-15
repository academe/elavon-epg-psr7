<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Support;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Simple built-in PSR-17 HTTP factory implementation.
 *
 * Provides basic implementations of PSR-17 RequestFactory and StreamFactory
 * to avoid requiring external dependencies for simple use cases.
 *
 * For more advanced needs, you can inject nyholm/psr7 or guzzlehttp/psr7 factories.
 */
class Psr17Factory implements RequestFactoryInterface, StreamFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }

    /**
     * {@inheritDoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return new Stream($content);
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);
        if ($resource === false) {
            throw new \RuntimeException("Unable to open file: {$filename}");
        }
        return new Stream($resource);
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}