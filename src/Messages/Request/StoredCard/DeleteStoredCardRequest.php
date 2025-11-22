<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Request\StoredCard;

use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Support\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Delete Stored Card Request.
 *
 * Builds a PSR-7 request for deleting a stored card (DELETE /stored-cards/{id}).
 *
 * Example usage with ElavonApiRequest decorator:
 * ```php
 * use Academe\Elavon\Epg\Psr7\Messages\Request\DeleteStoredCardRequest;
 * use Academe\Elavon\Epg\Psr7\Support\ElavonApiRequest;
 *
 * // Build the base request
 * $request = (new DeleteStoredCardRequest('sc123'))->build();
 *
 * // Add Elavon API headers, environment, and authentication
 * $elavonRequest = ElavonApiRequest::create($request)
 *     ->withSandbox()
 *     ->withAuthentication($merchantAlias, $apiKey);
 *
 * // Send the request
 * $response = $httpClient->sendRequest($elavonRequest);
 * ```
 *
 * Note: This class builds the base request but does NOT add:
 * - Elavon API headers (Accept, Accept-Version)
 * - Environment configuration (sandbox, production, custom base URI)
 * - Authentication headers (Authorization)
 * Use the ElavonApiRequest decorator to add these via fluent interface.
 */
class DeleteStoredCardRequest
{
    /**
     * @param string $storedCardId Stored card ID to delete
     * @param RequestFactoryInterface|null $requestFactory PSR-17 request factory (uses built-in if null)
     *
     * @throws InvalidArgumentException When stored card ID is empty
     */
    public function __construct(
        private readonly string $storedCardId,
        private readonly ?RequestFactoryInterface $requestFactory = null,
    ) {
        if (empty($this->storedCardId)) {
            throw new InvalidArgumentException('Stored card ID cannot be empty');
        }
    }

    /**
     * Builds the PSR-7 HTTP request.
     *
     * @return RequestInterface The PSR-7 request ready to send
     */
    public function build(): RequestInterface
    {
        // Use built-in factory if none provided
        $requestFactory = $this->requestFactory ?? new Psr17Factory();

        // Build PSR-7 DELETE request
        return $requestFactory
            ->createRequest('DELETE', '/stored-cards/' . $this->storedCardId);
    }

    /**
     * Gets the stored card ID being deleted.
     *
     * @return string
     */
    public function getStoredCardId(): string
    {
        return $this->storedCardId;
    }
}
