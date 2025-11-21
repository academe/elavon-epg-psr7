<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLinkEvent;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\HandlesErrors;
use Psr\Http\Message\ResponseInterface;

class PaymentLinkEventListResponse
{
    use HandlesErrors;

    private readonly ?array $paymentLinkEvents;
    private readonly ?string $nextPage;
    private readonly ?string $firstPage;

    public function __construct(private readonly ResponseInterface $response)
    {
        if ($this->isSuccessful()) {
            $data = $this->parseSuccessResponse();
            $this->paymentLinkEvents = $data['items'];
            $this->nextPage = $data['next'];
            $this->firstPage = $data['first'];
            $this->error = null;
        } else {
            $this->paymentLinkEvents = null;
            $this->nextPage = null;
            $this->firstPage = null;
            $this->error = $this->parseErrorResponse();
        }
    }

    public static function fromPsr7Response(ResponseInterface $response): static
    {
        return new static($response);
    }

    public function getPaymentLinkEvents(): ?array
    {
        return $this->paymentLinkEvents;
    }

    public function getNext(): ?string
    {
        return $this->nextPage;
    }

    public function getFirst(): ?string
    {
        return $this->firstPage;
    }

    public function hasMorePages(): bool
    {
        return $this->nextPage !== null;
    }

    public function getPsr7Response(): ResponseInterface
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    private function parseSuccessResponse(): array
    {
        $data = $this->parseJsonBody();

        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new InvalidArgumentException('Response must contain an "items" array');
        }

        $events = [];
        foreach ($data['items'] as $index => $itemData) {
            if (!is_array($itemData)) {
                throw new InvalidArgumentException("Item at index {$index} is not an array");
            }
            $events[] = PaymentLinkEvent::fromData($itemData);
        }

        return [
            'items' => $events,
            'next' => isset($data['next']) ? (string) $data['next'] : null,
            'first' => isset($data['first']) ? (string) $data['first'] : null,
        ];
    }

    private function parseJsonBody(): array
    {
        $body = (string) $this->response->getBody();

        if ($body === '') {
            throw new InvalidArgumentException('Response body is empty');
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new InvalidArgumentException(
                'Failed to decode JSON response: ' . $e->getMessage(),
                previous: $e
            );
        }

        if (!is_array($data) || $data === [] || array_keys($data) === range(0, count($data) - 1)) {
            throw new InvalidArgumentException('Response body is not a JSON object');
        }

        return $data;
    }
}
