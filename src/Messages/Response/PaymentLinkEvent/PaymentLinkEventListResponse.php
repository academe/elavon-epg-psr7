<?php

declare(strict_types=1);

namespace Academe\Elavon\Epg\Psr7\Messages\Response\PaymentLinkEvent;

use Academe\Elavon\Epg\Psr7\Dtos\PaymentLinkEvent;
use Academe\Elavon\Epg\Psr7\Exceptions\InvalidArgumentException;
use Academe\Elavon\Epg\Psr7\Messages\Response\Concerns\ParsesPsr7Response;

class PaymentLinkEventListResponse
{
    use ParsesPsr7Response;

    /** @var array<PaymentLinkEvent>|null */
    public readonly ?array $paymentLinkEvents;
    public readonly ?string $nextPage;
    public readonly ?string $firstPage;

    public function __construct(array $data, int $statusCode)
    {
        $this->statusCode = $statusCode;

        if ($this->isSuccessful()) {
            $parsed = $this->parseSuccessData($data);
            $this->paymentLinkEvents = $parsed['items'];
            $this->nextPage = $parsed['next'];
            $this->firstPage = $parsed['first'];
            $this->error = null;
        } else {
            $this->paymentLinkEvents = null;
            $this->nextPage = null;
            $this->firstPage = null;
            $this->error = self::parseErrorData($data);
        }
    }

    public function hasMorePages(): bool
    {
        return $this->nextPage !== null;
    }
    private function parseSuccessData(array $data): array
    {
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
}
