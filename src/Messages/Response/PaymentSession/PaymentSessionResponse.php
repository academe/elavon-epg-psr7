<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * PaymentSession Response.
 *
 * Parses a PSR-7 response from the EPG API containing either payment session data or error details.
 *
 * For successful responses (2xx), contains payment session data.
 * For error responses (4xx, 5xx), contains error details.
 */
class PaymentSessionResponse
{
    use HandlesErrors;

    private readonly ?PaymentSession $paymentSession;

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
            $this->paymentSession = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->paymentSession = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a PaymentSessionResponse from a PSR-7 response.
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
     * Gets the parsed PaymentSession object.
     *
     * Only available for successful responses (2xx status codes).
     *
     * @return PaymentSession|null Returns null if response was an error
     */
    public function getPaymentSession(): ?PaymentSession
    {
        return $this->paymentSession;
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
     * Parses a successful response into a PaymentSession object.
     *
     * @return PaymentSession
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): PaymentSession
    {
        $data = $this->parseJsonBody();
        return PaymentSession::fromData($data);
    }

}
