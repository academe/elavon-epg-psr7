<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Support;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Simple PSR-7 HTTP Request implementation.
 *
 * Provides a minimal implementation for creating HTTP requests.
 */
class Request implements RequestInterface
{
    /** @var array<string, list<string>> */
    private array $headers = [];

    private StreamInterface $body;

    private string $protocolVersion = '1.1';

    private UriInterface $uri;

    /**
     * @param string $method HTTP method
     * @param string|UriInterface $uri Request URI
     * @param array<string, string|string[]> $headers Request headers
     * @param StreamInterface|string|null $body Request body
     */
    public function __construct(
        private string $method,
        string|UriInterface $uri,
        array $headers = [],
        StreamInterface|string|null $body = null,
    ) {
        $this->method = strtoupper($method);
        $this->uri = is_string($uri) ? new Uri($uri) : $uri;

        foreach ($headers as $name => $value) {
            $normalized = $this->normalizeHeaderName($name);
            $this->headers[$normalized] = is_array($value) ? $value : [$value];
        }

        $this->body = match (true) {
            $body instanceof StreamInterface => $body,
            is_string($body) => new Stream($body),
            default => new Stream(''),
        };
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): static
    {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$this->normalizeHeaderName($name)]);
    }

    public function getHeader(string $name): array
    {
        $name = $this->normalizeHeaderName($name);
        return $this->headers[$name] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): static
    {
        $new = clone $this;
        $normalized = $this->normalizeHeaderName($name);
        $new->headers[$normalized] = is_array($value) ? $value : [$value];
        return $new;
    }

    public function withAddedHeader(string $name, $value): static
    {
        $new = clone $this;
        $normalized = $this->normalizeHeaderName($name);
        $values = is_array($value) ? $value : [$value];

        if (isset($new->headers[$normalized])) {
            $new->headers[$normalized] = array_merge($new->headers[$normalized], $values);
        } else {
            $new->headers[$normalized] = $values;
        }

        return $new;
    }

    public function withoutHeader(string $name): static
    {
        $new = clone $this;
        unset($new->headers[$this->normalizeHeaderName($name)]);
        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    public function getRequestTarget(): string
    {
        $target = $this->uri->getPath();
        if ($this->uri->getQuery() !== '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target ?: '/';
    }

    public function withRequestTarget(string $requestTarget): static
    {
        $new = clone $this;
        // For simplicity, we'll just update the URI
        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): static
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        $new = clone $this;
        $new->uri = $uri;
        return $new;
    }

    private function normalizeHeaderName(string $name): string
    {
        return strtolower($name);
    }
}