<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentMethodSession;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentMethodSession;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

/**
 * PaymentMethodSession Response.
 *
 * Parses a PSR-7 response from the EPG API containing either payment method session data or error details.
 */
class PaymentMethodSessionResponse
{
    use HandlesErrors;

    private readonly ?PaymentMethodSession $paymentMethodSession;

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
            $this->paymentMethodSession = $this->parseSuccessResponse();
            $this->error = null;
        } else {
            $this->paymentMethodSession = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    /**
     * Creates a PaymentMethodSessionResponse from a PSR-7 response.
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
     * Gets the parsed PaymentMethodSession object.
     *
     * @return PaymentMethodSession|null Returns null if response was an error
     */
    public function getPaymentMethodSession(): ?PaymentMethodSession
    {
        return $this->paymentMethodSession;
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
     * Parses a successful response into a PaymentMethodSession object.
     *
     * @return PaymentMethodSession
     * @throws InvalidArgumentException When response cannot be parsed
     */
    private function parseSuccessResponse(): PaymentMethodSession
    {
        $data = $this->parseJsonBody();
        return PaymentMethodSession::fromData($data);
    }

}
