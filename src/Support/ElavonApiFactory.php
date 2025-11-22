<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Support;

use Psr\Http\Message\RequestInterface;

/**
 * Elavon API Request Factory.
 *
 * A simpler alternative to ElavonApiRequest that doesn't implement RequestInterface.
 * Configure once, apply to multiple requests via apply().
 *
 * Adds the following headers automatically:
 * - Accept: application/json;charset=UTF-8
 * - Content-Type: application/json (if body exists)
 * - Accept-Version: 1 (default, can be customized)
 * - Authorization: Basic {credentials} (if withAuthentication() is called)
 *
 * Basic usage:
 * ```php
 * $request = (new CreateTransactionRequest($transaction))->build();
 * $apiRequest = ElavonApiFactory::configure()
 *     ->withEuSandbox()
 *     ->withAuthentication($merchantAlias, $apiSecret)
 *     ->apply($request);
 * $client->sendRequest($apiRequest);
 * ```
 *
 * Reusing configuration for multiple requests:
 * ```php
 * // Configure once
 * $factory = ElavonApiFactory::configure()
 *     ->withEuSandbox()
 *     ->withAuthentication($merchantAlias, $apiSecret);
 *
 * // Apply to multiple requests - returns plain PSR-7 RequestInterface
 * $request1 = $factory->apply($firstRequest);
 * $request2 = $factory->apply($secondRequest);
 * $request3 = $factory->apply($thirdRequest);
 * ```
 *
 * Environment shortcuts:
 * ```php
 * // EU environments (default for withSandbox/withProduction)
 * $factory = ElavonApiFactory::configure()
 *     ->withEuSandbox()  // or withSandbox()
 *     ->withAuthentication($merchant, $apiKey);
 *
 * // US environments
 * $factory = ElavonApiFactory::configure()
 *     ->withUsSandbox()
 *     ->withAuthentication($merchant, $apiKey);
 *
 * // Using aliases with withBaseUri()
 * $factory = ElavonApiFactory::configure()
 *     ->withBaseUri('eu-sandbox')  // alias
 *     ->withAuthentication($merchant, $apiKey);
 *
 * // Custom base URI (full URL)
 * $factory = ElavonApiFactory::configure()
 *     ->withBaseUri('https://custom.api.example.com')
 *     ->withAuthentication($merchant, $apiKey);
 * ```
 */
class ElavonApiFactory
{
    // EU environments
    public const EU_PRODUCTION = 'https://api.eu.convergepay.com';
    public const EU_UAT = 'https://uat.api.converge.eu.elavonaws.com';
    public const EU_SANDBOX = self::EU_UAT;

    // US environments
    public const US_PRODUCTION = 'https://api.convergepay.com';
    public const US_UAT = 'https://uat.api.convergepay.com';
    public const US_SANDBOX = self::US_UAT;

    /**
     * Environment aliases for use with withBaseUri().
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
        'eu' => self::EU_PRODUCTION,
        'us' => self::US_PRODUCTION,
    ];

    private const DEFAULT_API_VERSION = '1';
    private const DEFAULT_ACCEPT = 'application/json;charset=UTF-8';

    private string $apiVersion;
    private ?string $baseUri = null;
    private ?string $username = null;
    private ?string $password = null;

    public function __construct(string $apiVersion = self::DEFAULT_API_VERSION)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * Static factory method to create a new configuration instance.
     */
    public static function configure(string $apiVersion = self::DEFAULT_API_VERSION): static
    {
        return new static($apiVersion);
    }

    /**
     * Applies Elavon API headers and configuration to a PSR-7 request.
     *
     * @param RequestInterface $request The request to configure
     * @return RequestInterface The configured request (plain PSR-7, not this class)
     */
    public function apply(RequestInterface $request): RequestInterface
    {
        // Add Accept header if not already present
        if (!$request->hasHeader('Accept')) {
            $request = $request->withHeader('Accept', self::DEFAULT_ACCEPT);
        }

        // Add Accept-Version header
        $request = $request->withHeader('Accept-Version', $this->apiVersion);

        // Add Content-Type for requests with body
        if (!$request->hasHeader('Content-Type') && $request->getBody()->getSize() > 0) {
            $request = $request->withHeader('Content-Type', 'application/json');
        }

        // Add authentication if configured
        if ($this->username !== null && $this->password !== null) {
            $request = $request->withHeader(
                'Authorization',
                'Basic ' . base64_encode("{$this->username}:{$this->password}")
            );
        }

        // Replace base URI if configured
        if ($this->baseUri !== null) {
            $currentUri = $request->getUri();
            $newUri = $this->replaceBaseUri($currentUri, $this->baseUri);
            $request = $request->withUri($newUri);
        }

        return $request;
    }

    public function withApiVersion(string $version): static
    {
        $new = clone $this;
        $new->apiVersion = $version;
        return $new;
    }

    /**
     * Adds HTTP Basic Authentication credentials.
     *
     * Elavon provides two types of API keys:
     * - Public key (pk_...): For client-side hosted card operations only
     * - Secret key (sk_...): For all server-side operations (transactions, etc.)
     */
    public function withAuthentication(string $username, string $password): static
    {
        $new = clone $this;
        $new->username = $username;
        $new->password = $password;
        return $new;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function withSandbox(): static
    {
        return $this->withBaseUri(self::EU_SANDBOX);
    }

    public function withProduction(): static
    {
        return $this->withBaseUri(self::EU_PRODUCTION);
    }

    public function withEuProduction(): static
    {
        return $this->withBaseUri(self::EU_PRODUCTION);
    }

    public function withEuSandbox(): static
    {
        return $this->withBaseUri(self::EU_SANDBOX);
    }

    public function withUsProduction(): static
    {
        return $this->withBaseUri(self::US_PRODUCTION);
    }

    public function withUsSandbox(): static
    {
        return $this->withBaseUri(self::US_SANDBOX);
    }

    /**
     * Sets the base URI using either a URL or an alias.
     *
     * Available aliases:
     * - eu-production, eu-live, eu-prod, eu → EU Production
     * - eu-sandbox, eu-uat, eu-test → EU UAT/Sandbox
     * - us-production, us-live, us-prod, us → US Production
     * - us-sandbox, us-uat, us-test → US UAT/Sandbox
     */
    public function withBaseUri(string $baseUriOrAlias): static
    {
        $baseUri = self::ENVIRONMENT_ALIASES[strtolower($baseUriOrAlias)] ?? $baseUriOrAlias;

        $new = clone $this;
        $new->baseUri = $baseUri;
        return $new;
    }

    public function getBaseUri(): ?string
    {
        return $this->baseUri;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * Replaces the base URI while preserving the path and query.
     */
    private function replaceBaseUri(\Psr\Http\Message\UriInterface $currentUri, string $newBaseUri): \Psr\Http\Message\UriInterface
    {
        $newUri = new Uri($newBaseUri);
        $path = $currentUri->getPath();

        // Handle alias-based original URIs (e.g., "eu-sandbox/transactions")
        if ($currentUri->getHost() === '' && !str_starts_with($path, '/')) {
            $slashPos = strpos($path, '/');
            $path = $slashPos !== false ? substr($path, $slashPos) : '';
        }

        return $newUri
            ->withPath($path)
            ->withQuery($currentUri->getQuery());
    }
}
