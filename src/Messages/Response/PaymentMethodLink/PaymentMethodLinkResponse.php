<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentMethodLink;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodLink;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * PaymentMethodLink Response.
 *
 * Parses a PSR-7 response from the EPG API containing either payment method link data or error details.
 *
 * For successful responses (2xx), contains payment method link data.
 * For error responses (4xx, 5xx), contains error details.
 */
class PaymentMethodLinkResponse
{
    use HandlesErrors;

    private readonly ?PaymentMethodLink $paymentMethodLink;

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
            $this->paymentMethodLink = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->paymentMethodLink = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a PaymentMethodLinkResponse from a PSR-7 response.
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
     * Gets the parsed PaymentMethodLink object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return PaymentMethodLink|null Returns null if response was an error
     */
    public function getPaymentMethodLink(): ?PaymentMethodLink
    {
        return $this->paymentMethodLink;
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
     * Parses a successful response into a PaymentMethodLink object.
     *
     * @return PaymentMethodLink
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): PaymentMethodLink
    {
        $data = $this->parseJsonBody();
        return PaymentMethodLink::fromData($data);
    }

}
