<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Support;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Psr\Http\Message\RequestInterface;

/**
 * Elavon API Request Factory.
 *
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
 * $factory = ElavonApiFactory::configure()
 *     ->withRegion('eu')
 *     ->withEnvironment('sandbox')
 *     ->withAuthentication($merchantAlias, $apiSecret);
 *
 * $request = (new CreateTransactionRequest($transaction))->build();
 * $apiRequest = $factory->apply($request);
 * $client->sendRequest($apiRequest);
 * ```
 *
 * Custom base URI (overrides region/environment):
 * ```php
 * $factory = ElavonApiFactory::configure()
 *     ->withBaseUri('https://custom.api.example.com')
 *     ->withAuthentication($merchant, $apiKey);
 * ```
 */
class ElavonApiFactory
{
    // Region constants
    public const REGION_EU = 'eu';
    public const REGION_US = 'us';

    // Environment constants
    public const ENV_LIVE = 'live';
    public const ENV_TEST = 'test';

    /**
     * Base URLs indexed by region and environment.
     */
    private const BASE_URLS = [
        self::REGION_EU => [
            self::ENV_LIVE => 'https://api.eu.convergepay.com',
            self::ENV_TEST => 'https://uat.api.converge.eu.elavonaws.com',
        ],
        self::REGION_US => [
            self::ENV_LIVE => 'https://api.convergepay.com',
            self::ENV_TEST => 'https://uat.api.convergepay.com',
        ],
    ];

    /**
     * Environment aliases for normalization.
     */
    private const ENVIRONMENT_ALIASES = [
        'live' => self::ENV_LIVE,
        'prod' => self::ENV_LIVE,
        'production' => self::ENV_LIVE,
        'test' => self::ENV_TEST,
        'sandbox' => self::ENV_TEST,
        'uat' => self::ENV_TEST,
    ];

    private const DEFAULT_API_VERSION = '1';
    private const DEFAULT_ACCEPT = 'application/json;charset=UTF-8';

    private string $apiVersion;

    // URL region and environment.
    private ?string $region = null;
    private ?string $environment = null;

    // Custom base URI (overrides region/environment if set).
    private ?string $baseUri = null;

    // Complete custom URL (overrides region/environment and any path and query already set).
    private ?string $uri = null;

    // Authentication credentials.
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

        // Set the host in the URI if configured.
        // No base URIs have a path component at this time,
        // so that will be ignored. If it does in the future,
        // then the path would need to be prefixed to the existing path
        // in the request URI.
        $baseUri = $this->getBaseUri();
        $uri = $this->getUri();

        if ($uri !== null) {
            $newUri = new Uri($uri);
            $request = $request->withUri($newUri);

            return $request;
        }
        
        if ($baseUri !== null) {
            $newUri = $request->getUri()
                ->withHost(parse_url($baseUri, PHP_URL_HOST))
                ->withScheme(parse_url($baseUri, PHP_URL_SCHEME))
                ->withPort(parse_url($baseUri, PHP_URL_PORT));
            $request = $request->withUri($newUri);
        }

        return $request;
    }

    /**
     * Sets the API region (eu, us).
     *
     * @param string $region Region code: 'eu' or 'us'
     * @throws InvalidArgumentException When region is unknown
     */
    public function withRegion(?string $region): static
    {
        $region = strtolower($region);

        if (!isset(self::BASE_URLS[$region])) {
            $validRegions = implode(', ', array_keys(self::BASE_URLS));
            throw new InvalidArgumentException(
                "Unknown region '{$region}'. Valid regions: {$validRegions}"
            );
        }

        $new = clone $this;
        $new->region = $region;
        $new->baseUri = null; // region/environment takes precedence over raw URL
        return $new;
    }

    /**
     * Sets the API environment (live, test).
     *
     * Aliases:
     * - 'live' → production
     * - 'sandbox', 'test' → UAT
     *
     * @param string $environment Environment name
     * @throws InvalidArgumentException When environment is unknown
     */
    public function withEnvironment(?string $environment): static
    {
        $environment = strtolower($environment);
        $normalized = self::ENVIRONMENT_ALIASES[$environment] ?? null;

        if ($normalized === null) {
            $validEnvs = implode(', ', array_keys(self::ENVIRONMENT_ALIASES));
            throw new InvalidArgumentException(
                "Unknown environment '{$environment}'. Valid environments: {$validEnvs}"
            );
        }

        $new = clone $this;
        $new->environment = $normalized;
        $new->baseUri = null; // region/environment takes precedence over raw URL
        return $new;
    }

    /**
     * Sets a custom base URI, overriding region/environment.
     *
     * @param string $baseUri Full base URL
     */
    public function withBaseUri(?string $baseUri): static
    {
        $new = clone $this;
        $new->baseUri = $baseUri;
        return $new;
    }

    public function withUri(?string $uri): static
    {
        $new = clone $this;
        $new->uri = $uri;
        return $new;
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

    /**
     * Gets the calculated base URI.
     *
     * Priority:
     * 1. Raw baseUri (if set via withBaseUri)
     * 2. Calculated from region + environment
     * 3. null if neither is configured
     */
    public function getBaseUri(): ?string
    {
        // Raw URL takes precedence
        if ($this->baseUri !== null) {
            return $this->baseUri;
        }

        // Calculate from region + environment
        if ($this->region !== null && $this->environment !== null) {
            return self::BASE_URLS[$this->region][$this->environment];
        }

        return null;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
