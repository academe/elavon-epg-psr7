<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Support;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Elavon API Request Decorator.
 *
 * Decorates a PSR-7 RequestInterface with Elavon-specific HTTP headers and authentication.
 * This follows the decorator pattern to add API-specific headers without modifying the original request.
 *
 * Adds the following headers automatically:
 * - Accept: application/json;charset=UTF-8 (if not already present)
 * - Content-Type: application/json (if not present and body exists)
 * - Accept-Version: 1 (default, can be customized)
 * - Authorization: Basic {credentials} (if withAuthentication() is called)
 *
 * Usage with fluent interface:
 * ```php
 * $request = (new CreateTransactionRequest($transaction))->build();
 * $elavonRequest = ElavonApiRequest::create($request)
 *     ->withSandbox()
 *     ->withAuthentication($merchantAlias, $apiKey);
 * $client->sendRequest($elavonRequest);
 * ```
 *
 * Complete example with all options:
 * ```php
 * $elavonRequest = ElavonApiRequest::create($request)
 *     ->withApiVersion('2')
 *     ->withProduction()
 *     ->withAuthentication($merchantAlias, $apiKey);
 * ```
 *
 * Reusing configuration for multiple requests:
 * ```php
 * // Method 1: Configure with first request, then reuse
 * $elavonRequest = ElavonApiRequest::create($firstRequest)
 *     ->withSandbox()
 *     ->withAuthentication($merchantAlias, $apiKey);
 *
 * $response1 = $client->sendRequest($elavonRequest);
 *
 * // Reuse same configuration with different request
 * $elavonRequest2 = $elavonRequest->withMessage($secondRequest);
 * $response2 = $client->sendRequest($elavonRequest2);
 *
 * // Method 2: Configure first, apply to requests later
 * $decorator = ElavonApiRequest::configure()
 *     ->withSandbox()
 *     ->withAuthentication($merchantAlias, $apiKey);
 *
 * $request1 = $decorator->withMessage($firstRequest);
 * $request2 = $decorator->withMessage($secondRequest);
 * $request3 = $decorator->withMessage($thirdRequest);
 * ```
 *
 * Environment shortcuts:
 * ```php
 * // EU environments (default)
 * $request = ElavonApiRequest::create($baseRequest)
 *     ->withEuSandbox()  // or withSandbox()
 *     ->withAuthentication($merchant, $apiKey);
 *
 * $request = ElavonApiRequest::create($baseRequest)
 *     ->withEuProduction()  // or withProduction()
 *     ->withAuthentication($merchant, $apiKey);
 *
 * // US environments
 * $request = ElavonApiRequest::create($baseRequest)
 *     ->withUsSandbox()
 *     ->withAuthentication($merchant, $apiKey);
 *
 * $request = ElavonApiRequest::create($baseRequest)
 *     ->withUsProduction()
 *     ->withAuthentication($merchant, $apiKey);
 *
 * // Using aliases with withBaseUri()
 * $request = ElavonApiRequest::create($baseRequest)
 *     ->withBaseUri('eu-sandbox')  // alias
 *     ->withAuthentication($merchant, $apiKey);
 *
 * $request = ElavonApiRequest::create($baseRequest)
 *     ->withBaseUri('us-production')  // alias
 *     ->withAuthentication($merchant, $apiKey);
 *
 * // Custom base URI (full URL)
 * $request = ElavonApiRequest::create($baseRequest)
 *     ->withBaseUri('https://custom.api.example.com')
 *     ->withAuthentication($merchant, $apiKey);
 *
 * // Get current base URI
 * $baseUri = $request->getBaseUri();
 * ```
 */
class ElavonApiRequest implements RequestInterface
{
    /**
     * Elavon API environments with their base URLs.
     *
     * EU (Europe):
     * - Production: https://api.eu.convergepay.com
     * - UAT/Sandbox: https://uat.api.converge.eu.elavonaws.com
     *
     * US (United States):
     * - Production: https://api.convergepay.com
     * - UAT/Sandbox: https://uat.api.convergepay.com
     */

    // EU environments
    public const EU_PRODUCTION = 'https://api.eu.convergepay.com';
    public const EU_UAT = 'https://uat.api.converge.eu.elavonaws.com';
    public const EU_SANDBOX = self::EU_UAT;

    // US environments
    public const US_PRODUCTION = 'https://api.convergepay.com';
    public const US_UAT = 'https://uat.api.convergepay.com';
    public const US_SANDBOX = self::US_UAT;

    // Legacy aliases (kept for backwards compatibility)
    public const ENVIRONMENT_PRODUCTION = self::US_PRODUCTION;
    public const ENVIRONMENT_UAT = self::EU_UAT;
    public const ENVIRONMENT_SANDBOX = self::EU_UAT;

    /**
     * Environment aliases for use with withBaseUri().
     *
     * @var array<string, string>
     */
    private const ENVIRONMENT_ALIASES = [
        'eu-production' => self::EU_PRODUCTION,
        'eu-live' => self::EU_PRODUCTION,
        'eu-prod' => self::EU_PRODUCTION,
        'eu-uat' => self::EU_UAT,
        'eu-sandbox' => self::EU_SANDBOX,
        'eu-test' => self::EU_UAT,
        'us-production' => self::US_PRODUCTION,
        'us-live' => self::US_PRODUCTION,
        'us-prod' => self::US_PRODUCTION,
        'us-uat' => self::US_UAT,
        'us-sandbox' => self::US_SANDBOX,
        'us-test' => self::US_UAT,
        // Short aliases
        'eu' => self::EU_PRODUCTION,
        'us' => self::US_PRODUCTION,
    ];

    /**
     * Default API version.
     */
    private const DEFAULT_API_VERSION = '1';

    /**
     * Default Accept header value.
     */
    private const DEFAULT_ACCEPT = 'application/json;charset=UTF-8';

    /**
     * Default Content-Type header value.
     */
    private const DEFAULT_CONTENT_TYPE = 'application/json';

    private RequestInterface $request;
    private string $apiVersion;
    private ?string $username = null;
    private ?string $password = null;

    /**
     * Private constructor - use static factory methods instead.
     *
     * @param RequestInterface $request The request to decorate with Elavon API headers
     * @param string $apiVersion API version (defaults to '1')
     * @param string|null $username Username for Basic Auth (merchant alias)
     * @param string|null $password Password for Basic Auth (public or secret API key)
     */
    private function __construct(
        RequestInterface $request,
        string $apiVersion = self::DEFAULT_API_VERSION,
        ?string $username = null,
        ?string $password = null,
    ) {
        $this->apiVersion = $apiVersion;
        $this->username = $username;
        $this->password = $password;
        $decorated = $request;

        // Add Accept header if not present
        if (!$decorated->hasHeader('Accept')) {
            $decorated = $decorated->withHeader('Accept', self::DEFAULT_ACCEPT);
        }

        // Add Content-Type header if not present and request has a body
        if (!$decorated->hasHeader('Content-Type') && $decorated->getBody()->getSize() > 0) {
            $decorated = $decorated->withHeader('Content-Type', self::DEFAULT_CONTENT_TYPE);
        }

        // Add Accept-Version header
        $decorated = $decorated->withHeader('Accept-Version', $this->apiVersion);

        // Add Authorization header if credentials provided
        if ($this->username !== null && $this->password !== null) {
            $decorated = $decorated->withHeader(
                'Authorization',
                'Basic ' . base64_encode("{$this->username}:{$this->password}")
            );
        }

        $this->request = $decorated;
    }

    /**
     * Creates an Elavon API request decorator (fluent interface entry point).
     *
     * @param RequestInterface $request The request to decorate
     * @param string|null $apiVersion API version (defaults to '1')
     * @return static
     */
    public static function create(RequestInterface $request, ?string $apiVersion = null): static
    {
        return new static($request, $apiVersion ?? self::DEFAULT_API_VERSION);
    }

    /**
     * Creates a configuration builder without an initial request.
     *
     * This allows you to configure authentication, environment, and API version
     * first, then apply it to messages later using withMessage().
     *
     * Example:
     * ```php
     * // Configure once
     * $decorator = ElavonApiRequest::configure()
     *     ->withSandbox()
     *     ->withAuthentication($merchant, $apiKey);
     *
     * // Apply to multiple requests
     * $request1 = $decorator->withMessage($firstRequest);
     * $request2 = $decorator->withMessage($secondRequest);
     * $request3 = $decorator->withMessage($thirdRequest);
     * ```
     *
     * @param string|null $apiVersion API version (defaults to '1')
     * @return static
     */
    public static function configure(?string $apiVersion = null): static
    {
        // Create a minimal placeholder request - will be replaced by withMessage()
        $placeholderRequest = new Request('GET', 'https://placeholder.local/');
        return new static($placeholderRequest, $apiVersion ?? self::DEFAULT_API_VERSION);
    }

    /**
     * Replaces the underlying request message while preserving all configuration.
     *
     * This is useful when you need to send multiple requests with the same
     * configuration (environment, API version, authentication).
     *
     * Example:
     * ```php
     * // Configure once
     * $elavonRequest = ElavonApiRequest::create($firstRequest)
     *     ->withSandbox()
     *     ->withAuthentication($merchant, $apiKey);
     *
     * // Send first request
     * $response1 = $httpClient->send($elavonRequest);
     *
     * // Reuse configuration for second request
     * $elavonRequest2 = $elavonRequest->withMessage($secondRequest);
     * $response2 = $httpClient->send($elavonRequest2);
     * ```
     *
     * @param RequestInterface $request The new request to decorate
     * @return static New instance with the new request and same configuration
     */
    public function withMessage(RequestInterface $request): static
    {
        // Extract the base URI from the current request
        $currentUri = $this->request->getUri();
        $baseUri = $currentUri->getScheme() . '://' . $currentUri->getHost();
        if ($currentUri->getPort() !== null) {
            $baseUri .= ':' . $currentUri->getPort();
        }

        // Create a new instance with the same configuration
        $new = new static(
            $request,
            $this->apiVersion,
            $this->username,
            $this->password
        );

        // Apply the base URI from the current request to the new request
        $newRequestUri = $request->getUri();
        $replacedUri = $this->replaceBaseUri($newRequestUri, $baseUri);
        $new->request = $new->request->withUri($replacedUri);

        return $new;
    }

    /**
     * Sets a custom API version.
     *
     * @param string $version API version
     * @return static New instance with updated version
     */
    public function withApiVersion(string $version): static
    {
        if ($version === $this->apiVersion) {
            return $this;
        }

        $new = clone $this;
        $new->apiVersion = $version;
        $new->request = $this->request->withHeader('Accept-Version', $version);
        return $new;
    }

    /**
     * Adds HTTP Basic Authentication credentials.
     *
     * Elavon provides two types of API keys:
     * - Public key (pk_...): For client-side hosted card operations only
     * - Secret key (sk_...): For all server-side operations (transactions, etc.)
     *
     * Use the appropriate key based on the operation being performed.
     *
     * @param string $username Username (merchant alias)
     * @param string $password Password (public or secret API key, depending on operation)
     * @return static New instance with authentication
     */
    public function withAuthentication(string $username, string $password): static
    {
        $new = clone $this;
        $new->username = $username;
        $new->password = $password;
        $new->request = $this->request->withHeader(
            'Authorization',
            'Basic ' . base64_encode("{$username}:{$password}")
        );
        return $new;
    }

    /**
     * Gets the username (merchant alias) used for authentication.
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Sets the base URI to the EU Sandbox/UAT environment.
     *
     * @return static New instance with EU UAT base URI
     */
    public function withSandbox(): static
    {
        return $this->withBaseUri(self::EU_SANDBOX);
    }

    /**
     * Sets the base URI to the EU Production/Live environment.
     *
     * @return static New instance with EU production base URI
     */
    public function withProduction(): static
    {
        return $this->withBaseUri(self::EU_PRODUCTION);
    }

    // EU environment shortcuts

    /**
     * Sets the base URI to EU Production.
     *
     * @return static New instance with EU production base URI
     */
    public function withEuProduction(): static
    {
        return $this->withBaseUri(self::EU_PRODUCTION);
    }

    /**
     * Sets the base URI to EU Sandbox/UAT.
     *
     * @return static New instance with EU UAT base URI
     */
    public function withEuSandbox(): static
    {
        return $this->withBaseUri(self::EU_SANDBOX);
    }

    // US environment shortcuts

    /**
     * Sets the base URI to US Production.
     *
     * @return static New instance with US production base URI
     */
    public function withUsProduction(): static
    {
        return $this->withBaseUri(self::US_PRODUCTION);
    }

    /**
     * Sets the base URI to US Sandbox/UAT.
     *
     * @return static New instance with US UAT base URI
     */
    public function withUsSandbox(): static
    {
        return $this->withBaseUri(self::US_SANDBOX);
    }

    /**
     * Sets the base URI using either a URL or an alias.
     *
     * Accepts either:
     * - A full URL: 'https://api.eu.convergepay.com'
     * - An alias: 'eu-production', 'eu-sandbox', 'us-production', 'us-sandbox', etc.
     *
     * Available aliases:
     * - eu-production, eu-live, eu-prod, eu → EU Production
     * - eu-sandbox, eu-uat, eu-test → EU UAT/Sandbox
     * - us-production, us-live, us-prod, us → US Production
     * - us-sandbox, us-uat, us-test → US UAT/Sandbox
     *
     * @param string $baseUriOrAlias URL or environment alias
     * @return static New instance with the specified base URI
     */
    public function withBaseUri(string $baseUriOrAlias): static
    {
        // Resolve alias to URL if applicable
        $baseUri = self::ENVIRONMENT_ALIASES[strtolower($baseUriOrAlias)] ?? $baseUriOrAlias;

        $new = clone $this;
        $currentUri = $this->request->getUri();
        $newUri = $this->replaceBaseUri($currentUri, $baseUri);
        $new->request = $this->request->withUri($newUri);
        return $new;
    }

    /**
     * Gets the current base URI (scheme + host + port).
     *
     * @return string The base URI without path or query
     */
    public function getBaseUri(): string
    {
        $uri = $this->request->getUri();
        $baseUri = $uri->getScheme() . '://' . $uri->getHost();

        if ($uri->getPort() !== null) {
            $baseUri .= ':' . $uri->getPort();
        }

        return $baseUri;
    }

    /**
     * Gets the API version used for this request.
     *
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * Replaces the base URI of a URI while preserving the path and query.
     *
     * Handles cases where the original URI might not have a proper scheme/host
     * (e.g., when built with an alias like 'eu-sandbox/transactions').
     *
     * @param UriInterface $currentUri
     * @param string $newBaseUri
     * @return UriInterface
     */
    private function replaceBaseUri(UriInterface $currentUri, string $newBaseUri): UriInterface
    {
        $newUri = new Uri($newBaseUri);

        // Get the path from the original URI
        $path = $currentUri->getPath();

        // If the original URI had no scheme/host, the "path" might include
        // non-path segments (e.g., "eu-sandbox/transactions" instead of "/transactions").
        // In this case, extract only the portion starting with a known API path prefix.
        if ($currentUri->getHost() === '' && !str_starts_with($path, '/')) {
            // Find the first slash which indicates the start of the actual path
            $slashPos = strpos($path, '/');
            if ($slashPos !== false) {
                $path = substr($path, $slashPos);
            } else {
                // No path found, use empty path
                $path = '';
            }
        }

        // Preserve the path and query from the original URI
        return $newUri
            ->withPath($path)
            ->withQuery($currentUri->getQuery());
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
