<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\Concerns;

use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Provides fluent factory injection for request builders.
 *
 * By default, uses the built-in Psr17Factory. Custom factories can be
 * injected via withRequestFactory() and withStreamFactory() methods.
 */
trait HasPsr17Factories
{
    private ?RequestFactoryInterface $requestFactory = null;
    private ?StreamFactoryInterface $streamFactory = null;

    /**
     * Returns a new instance with the specified request factory.
     */
    public function withRequestFactory(RequestFactoryInterface $requestFactory): static
    {
        $clone = clone $this;
        $clone->requestFactory = $requestFactory;

        return $clone;
    }

    /**
     * Returns a new instance with the specified stream factory.
     */
    public function withStreamFactory(StreamFactoryInterface $streamFactory): static
    {
        $clone = clone $this;
        $clone->streamFactory = $streamFactory;

        return $clone;
    }

    /**
     * Gets the request factory, using the built-in factory if none was set.
     */
    protected function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory ?? new Psr17Factory();
    }

    /**
     * Gets the stream factory, using the built-in factory if none was set.
     */
    protected function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory ?? new Psr17Factory();
    }
}
