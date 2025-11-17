<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Support;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Authenticated Request Decorator.
 *
 * Decorates a PSR-7 RequestInterface with HTTP Basic Authentication.
 * This follows the decorator pattern to add authentication without modifying the original request.
 *
 * Usage:
 * ```php
 * $request = (new CreateTransactionRequest($transaction))->build();
 * $authenticatedRequest = new AuthenticatedRequest($request, $merchantAlias, $apiKey);
 * $client->sendRequest($authenticatedRequest);
 * ```
 */
class AuthenticatedRequest implements RequestInterface
{
    private RequestInterface $request;

    /**
     * @param RequestInterface $request The request to decorate with authentication
     * @param string $username The merchant alias (username for Basic Auth)
     * @param string $password The API key (password for Basic Auth)
     */
    public function __construct(
        RequestInterface $request,
        private readonly string $username,
        private readonly string $password,
    ) {
        // Add Authorization header to the decorated request
        $this->request = $request->withHeader(
            'Authorization',
            'Basic ' . base64_encode("{$this->username}:{$this->password}")
        );
    }

    /**
     * Gets the username (merchant alias) used for authentication.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    // Delegate all RequestInterface methods to the decorated request

    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }

    public function withProtocolVersion(string $version): static
    {
        $new = clone $this;
        $new->request = $this->request->withProtocolVersion($version);
        return $new;
    }

    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    public function hasHeader(string $name): bool
    {
        return $this->request->hasHeader($name);
    }

    public function getHeader(string $name): array
    {
        return $this->request->getHeader($name);
    }

    public function getHeaderLine(string $name): string
    {
        return $this->request->getHeaderLine($name);
    }

    public function withHeader(string $name, $value): static
    {
        $new = clone $this;
        $new->request = $this->request->withHeader($name, $value);
        return $new;
    }

    public function withAddedHeader(string $name, $value): static
    {
        $new = clone $this;
        $new->request = $this->request->withAddedHeader($name, $value);
        return $new;
    }

    public function withoutHeader(string $name): static
    {
        $new = clone $this;
        $new->request = $this->request->withoutHeader($name);
        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }

    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->request = $this->request->withBody($body);
        return $new;
    }

    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }

    public function withRequestTarget(string $requestTarget): static
    {
        $new = clone $this;
        $new->request = $this->request->withRequestTarget($requestTarget);
        return $new;
    }

    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    public function withMethod(string $method): static
    {
        $new = clone $this;
        $new->request = $this->request->withMethod($method);
        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        $new = clone $this;
        $new->request = $this->request->withUri($uri, $preserveHost);
        return $new;
    }
}
