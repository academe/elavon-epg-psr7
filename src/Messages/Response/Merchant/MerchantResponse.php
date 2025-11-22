<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\Merchant;

use Academe\Elavon\Epg\Psr7\Dtos\Merchant;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * Merchant Response.
 *
 * Parses a PSR-7 response from the EPG API containing either merchant data or error details.
 *
 * For successful responses (2xx), contains merchant data.
 * For error responses (4xx, 5xx), contains error details.
 */
class MerchantResponse
{
    use HandlesErrors;

    private readonly ?Merchant $merchant;

    /**
     * @param ResponseInterface $response PSR-7 response from the API
     *
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public function __construct(
        private readonly ResponseInterface $response,
    ) {
        // Parse response based on status code
        if ($this->isSuccessful()) {
            $this->merchant = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->merchant = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a MerchantResponse from a PSR-7 response.
     *
     * @param ResponseInterface $response PSR-7 response
     *
     * @return self
     * @throws InvalidArgumentException When response cannot be parsed
     */
    public static function fromPsr7Response(ResponseInterface $response): self
    {
        return new self($response);
    }

    /**
     * Gets the parsed Merchant object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return Merchant|null Returns null if response was an error
     */
    public function getMerchant(): ?Merchant
    {
        return $this->merchant;
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Gets the original PSR-7 response.
     *
     * @return ResponseInterface
     */
    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Parses a successful response into a Merchant object.
     *
     * @return Merchant
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): Merchant
    {
        $data = $this->parseJsonBody();
        return Merchant::fromData($data);
    }

}
